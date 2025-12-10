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
        
        // Date range filter (default: current week)
        $startDate = $request->input('start_date', now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfWeek()->format('Y-m-d'));
        
        $query = $organization->slots()
            ->with(['shift', 'booking', 'staff'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        // Sorting
        $sortField = $request->input('sort', 'date');
        $sortDirection = $request->input('direction', 'desc');
        
        $allowedSortFields = ['id', 'date', 'start_time', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Secondary sort by start_time if sorting by date
        if ($sortField === 'date') {
            $query->orderBy('start_time', $sortDirection);
        }
        
        $perPage = $request->input('per_page', 20);
        $slots = $query->paginate($perPage)->withQueryString();
        
        return view('slots.index', compact('organization', 'slots', 'startDate', 'endDate'));
    }

    /**
     * Generate slots from shifts
     */
    public function generate(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            // Use SlotGenerationService to generate slots
            $slots = $this->slotService->generateSlots(
                $organization,
                $service,
                $startDate,
                $endDate
            );

            return redirect()
                ->route('organization.slots.index', $organization)
                ->with('success', "Generated {$slots->count()} slots successfully");
                
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
