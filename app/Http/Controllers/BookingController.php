<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use App\Services\SlotGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $slotService;

    public function __construct(SlotGenerationService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Display bookings
     */
    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);
        
        $query = $organization->bookings()->with(['customer', 'service', 'staff', 'slot']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }

        // Filter by staff (for team members viewing their own bookings)
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        $bookings = $query->latest('booking_date')->paginate(20);
        
        return view('bookings.index', compact('organization', 'bookings'));
    }

    /**
     * Show create booking form
     */
    public function create(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        $services = $organization->services()->where('is_active', true)->get();
        
        return view('bookings.create', compact('organization', 'services'));
    }

    /**
     * Get available slots for a service
     */
    public function getAvailableSlots(Organization $organization, Service $service, Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $date = Carbon::parse($validated['date']);
        
        // Generate slots if they don't exist
        $this->slotService->generateSlotsForDate($organization, $service, $date);
        
        // Get available slots
        $slots = $this->slotService->getAvailableSlots(
            $organization,
            $service,
            $date->startOfDay(),
            $date->endOfDay()
        );

        return response()->json([
            'slots' => $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'staff_name' => $slot->assignedStaff->name,
                    'available' => $slot->current_bookings < $slot->max_bookings,
                ];
            }),
        ]);
    }

    /**
     * Store a new booking
     */
    public function store(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'slot_id' => ['required', 'exists:slots,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cash,online'],
        ]);

        DB::beginTransaction();
        
        try {
            $slot = Slot::findOrFail($validated['slot_id']);
            $service = Service::findOrFail($validated['service_id']);

            // Verify slot belongs to organization
            if ($slot->organization_id !== $organization->id) {
                throw new \Exception('Invalid slot');
            }

            // Check for conflicts
            if ($this->slotService->hasConflict($slot)) {
                return redirect()
                    ->back()
                    ->with('error', 'This slot is no longer available. Please select another time.');
            }

            // Find or create customer
            $customer = User::firstOrCreate(
                ['email' => $validated['customer_email']],
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'user_type' => 'customer',
                    'password' => bcrypt(str()->random(16)), // Random password
                ]
            );

            // Create booking
            $booking = Booking::create([
                'organization_id' => $organization->id,
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'slot_id' => $slot->id,
                'staff_id' => $slot->assigned_staff_id,
                'booking_date' => $slot->start_time->toDateString(),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => $validated['payment_method'] === 'cash' ? 'confirmed' : 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $validated['payment_method'],
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'notes' => $validated['notes'],
            ]);

            // Update slot booking count
            $slot->increment('current_bookings');
            if ($slot->current_bookings >= $slot->max_bookings) {
                $slot->update(['status' => 'booked']);
            }

            DB::commit();

            // Send notifications (will be implemented in Phase 7)
            // event(new BookingCreated($booking));

            if ($validated['payment_method'] === 'online') {
                return redirect()
                    ->route('bookings.payment', [$organization, $booking])
                    ->with('success', 'Booking created. Please complete payment.');
            }

            return redirect()
                ->route('bookings.show', [$organization, $booking])
                ->with('success', 'Booking confirmed successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * Display booking details
     */
    public function show(Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $booking->load(['customer', 'service', 'staff', 'slot', 'payment']);
        
        return view('bookings.show', compact('organization', 'booking'));
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, Organization $organization, Booking $booking)
    {
        $this->authorize('view', $organization);
        
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,completed,cancelled'],
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);

        // If cancelling, free up the slot
        if ($validated['status'] === 'cancelled' && in_array($oldStatus, ['pending', 'confirmed'])) {
            $slot = $booking->slot;
            if ($slot) {
                $slot->decrement('current_bookings');
                if ($slot->status === 'booked' && $slot->current_bookings < $slot->max_bookings) {
                    $slot->update(['status' => 'available']);
                }
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Booking status updated successfully');
    }

    /**
     * Cancel booking
     */
    public function cancel(Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        // Only allow cancellation of pending or confirmed bookings
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()
                ->back()
                ->with('error', 'Cannot cancel this booking');
        }

        DB::beginTransaction();
        
        try {
            $booking->update(['status' => 'cancelled']);

            // Free up the slot
            $slot = $booking->slot;
            if ($slot) {
                $slot->decrement('current_bookings');
                if ($slot->status === 'booked' && $slot->current_bookings < $slot->max_bookings) {
                    $slot->update(['status' => 'available']);
                }
            }

            DB::commit();

            return redirect()
                ->route('bookings.index', $organization)
                ->with('success', 'Booking cancelled successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }
}
