<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Booking;
use App\Models\BookingReschedule;
use App\Models\Slot;
use App\Services\SlotGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RescheduleController extends Controller
{
    protected $slotService;

    public function __construct(SlotGenerationService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Show reschedule request form
     */
    public function create(Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        // Only allow rescheduling of pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()
                ->back()
                ->with('error', 'Cannot reschedule this booking');
        }

        // Check 1-day prior restriction (customers only)
        $user = Auth::user();
        if ($user && $user->user_type === 'customer') {
            $bookingDate = Carbon::parse($booking->booking_date);
            if (now()->diffInDays($bookingDate, false) < 1) {
                return redirect()
                    ->back()
                    ->with('error', 'Reschedule requests must be made at least 1 day before the appointment');
            }
        }

        // Get available slots for the same service
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(30)->endOfDay();
        
        $availableSlots = $this->slotService->getAvailableSlots(
            $organization,
            $booking->service,
            $startDate,
            $endDate
        )->groupBy(function ($slot) {
            return $slot->start_time->format('Y-m-d');
        });

        return view('bookings.reschedule', compact('organization', 'booking', 'availableSlots'));
    }

    /**
     * Store reschedule request
     */
    public function store(Request $request, Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'new_slot_id' => ['required', 'exists:slots,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Check 1-day prior restriction for customers
        $user = Auth::user();
        $requiresApproval = false;
        
        if ($user && $user->user_type === 'customer') {
            $bookingDate = Carbon::parse($booking->booking_date);
            if (now()->diffInDays($bookingDate, false) < 1) {
                return redirect()
                    ->back()
                    ->with('error', 'Reschedule requests must be made at least 1 day before the appointment');
            }
            $requiresApproval = true;
        }

        DB::beginTransaction();
        
        try {
            $newSlot = Slot::findOrFail($validated['new_slot_id']);

            // Verify new slot belongs to organization and same service
            if ($newSlot->organization_id !== $organization->id || 
                $newSlot->service_id !== $booking->service_id) {
                throw new \Exception('Invalid slot selected');
            }

            // Check for conflicts on new slot
            if ($this->slotService->hasConflict($newSlot, $booking->id)) {
                return redirect()
                    ->back()
                    ->with('error', 'Selected slot is no longer available');
            }

            // Create reschedule request
            $reschedule = BookingReschedule::create([
                'booking_id' => $booking->id,
                'old_slot_id' => $booking->slot_id,
                'new_slot_id' => $newSlot->id,
                'requested_by' => $user ? $user->id : null,
                'reason' => $validated['reason'],
                'status' => $requiresApproval ? 'pending' : 'approved',
                'requested_at' => now(),
            ]);

            // If admin/frontdesk, approve immediately
            if (!$requiresApproval) {
                $this->approveReschedule($reschedule);
            }

            DB::commit();

            if ($requiresApproval) {
                return redirect()
                    ->route('bookings.show', [$organization, $booking])
                    ->with('success', 'Reschedule request submitted. Waiting for approval.');
            }

            return redirect()
                ->route('bookings.show', [$organization, $booking])
                ->with('success', 'Booking rescheduled successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to reschedule: ' . $e->getMessage());
        }
    }

    /**
     * Approve reschedule request
     */
    public function approve(Organization $organization, BookingReschedule $reschedule)
    {
        $this->authorize('view', $organization);
        
        $booking = $reschedule->booking;
        
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        if ($reschedule->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'This reschedule request has already been processed');
        }

        DB::beginTransaction();
        
        try {
            $this->approveReschedule($reschedule);
            
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Reschedule request approved');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to approve reschedule: ' . $e->getMessage());
        }
    }

    /**
     * Reject reschedule request
     */
    public function reject(Organization $organization, BookingReschedule $reschedule)
    {
        $this->authorize('view', $organization);
        
        $booking = $reschedule->booking;
        
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        if ($reschedule->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'This reschedule request has already been processed');
        }

        $reschedule->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Reschedule request rejected');
    }

    /**
     * Helper method to approve reschedule
     */
    protected function approveReschedule(BookingReschedule $reschedule)
    {
        $booking = $reschedule->booking;
        $oldSlot = $reschedule->oldSlot;
        $newSlot = $reschedule->newSlot;

        // Check conflict again
        if ($this->slotService->hasConflict($newSlot, $booking->id)) {
            throw new \Exception('New slot is no longer available');
        }

        // Free up old slot
        if ($oldSlot) {
            $oldSlot->decrement('current_bookings');
            if ($oldSlot->status === 'booked' && $oldSlot->current_bookings < $oldSlot->max_bookings) {
                $oldSlot->update(['status' => 'available']);
            }
        }

        // Book new slot
        $newSlot->increment('current_bookings');
        if ($newSlot->current_bookings >= $newSlot->max_bookings) {
            $newSlot->update(['status' => 'booked']);
        }

        // Update booking
        $booking->update([
            'slot_id' => $newSlot->id,
            'staff_id' => $newSlot->assigned_staff_id,
            'booking_date' => $newSlot->start_time->toDateString(),
            'start_time' => $newSlot->start_time,
            'end_time' => $newSlot->end_time,
            'status' => 'rescheduled',
        ]);

        // Update reschedule request
        $reschedule->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);
    }
}
