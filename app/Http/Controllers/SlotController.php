<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Service;
use App\Models\Slot;
use App\Services\SlotGenerationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SlotController extends Controller
{
    protected $slotService;

    public function __construct(SlotGenerationService $slotService)
    {
        $this->slotService = $slotService;
    }

    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);
        
        // Load slots for the current week
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        
        $slots = $organization->slots()
            ->with(['shift', 'booking', 'staff'])
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        return view('slots.index', compact('organization', 'slots'));
    }

    /**
     * Generate slots from shifts
     */
    public function generate(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            // Get all active shifts for the organization
            $shifts = $organization->shifts()->where('is_active', true)->get();
            
            if ($shifts->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'No active shifts found. Please create shifts first.');
            }
            
            $slotsCreated = 0;
            
            // Generate slots for each day in the range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dayOfWeek = $date->dayOfWeek;
                
                // Get shifts for this day of week
                $dayShifts = $shifts->where('day_of_week', $dayOfWeek);
                
                foreach ($dayShifts as $shift) {
                    // Create slot for this shift
                    Slot::create([
                        'shift_id' => $shift->id,
                        'organization_id' => $organization->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $shift->start_time,
                        'end_time' => $shift->end_time,
                        'assigned_staff_id' => $shift->user_id,
                        'status' => 'available',
                    ]);
                    $slotsCreated++;
                }
            }

            return redirect()
                ->route('slots.index', $organization)
                ->with('success', "Generated {$slotsCreated} slots successfully");
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to generate slots: ' . $e->getMessage());
        }
    }

    /**
     * Block a slot
     */
    public function block(Request $request, Organization $organization, Slot $slot)
    {
        $this->authorize('manageServices', $organization);
        
        if ($slot->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->slotService->blockSlot($slot, $validated['reason'] ?? null);

        return redirect()
            ->back()
            ->with('success', 'Slot blocked successfully');
    }

    /**
     * Toggle slot availability (block/unblock)
     */
    public function toggle(Organization $organization, Slot $slot)
    {
        $this->authorize('update', $organization);
        
        if ($slot->organization_id !== $organization->id) {
            abort(404);
        }

        // Can't toggle if slot is booked
        if ($slot->status === 'booked') {
            return redirect()
                ->back()
                ->with('error', 'Cannot modify a booked slot');
        }

        // Toggle between available and unavailable
        $newStatus = $slot->status === 'available' ? 'unavailable' : 'available';
        $slot->update(['status' => $newStatus]);

        return redirect()
            ->back()
            ->with('success', $newStatus === 'available' ? 'Slot marked as available' : 'Slot marked as unavailable');
    }

    /**
     * Update slot status manually
     */
    public function updateStatus(Request $request, Organization $organization, Slot $slot)
    {
        $this->authorize('update', $organization);
        
        if ($slot->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:available,unavailable,rescheduled'],
        ]);

        $slot->update(['status' => $validated['status']]);

        return redirect()
            ->back()
            ->with('success', 'Slot status updated successfully');
    }

    /**
     * Unblock a slot
     */
    public function unblock(Organization $organization, Slot $slot)
    {
        $this->authorize('manageServices', $organization);
        
        if ($slot->organization_id !== $organization->id) {
            abort(404);
        }

        $this->slotService->unblockSlot($slot);

        return redirect()
            ->back()
            ->with('success', 'Slot unblocked successfully');
    }

    /**
     * Delete a slot
     */
    public function destroy(Organization $organization, Slot $slot)
    {
        $this->authorize('manageServices', $organization);
        
        if ($slot->organization_id !== $organization->id) {
            abort(404);
        }

        // Check if slot has bookings
        if ($slot->current_bookings > 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete slot with active bookings');
        }

        $slot->delete();

        return redirect()
            ->back()
            ->with('success', 'Slot deleted successfully');
    }
}
