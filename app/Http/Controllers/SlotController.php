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

    /**
     * Display slots
     */
    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);
        
        $services = $organization->services()->where('is_active', true)->get();
        
        $query = $organization->slots()->with(['service', 'assignedStaff']);

        // Filter by service
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('start_time', $date);
        } else {
            // Default to today
            $query->whereDate('start_time', today());
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $slots = $query->orderBy('start_time')->get();
        
        return view('slots.index', compact('organization', 'slots', 'services'));
    }

    /**
     * Generate slots for a service
     */
    public function generate(Request $request, Organization $organization)
    {
        $this->authorize('manageServices', $organization);
        
        $validated = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            
            if ($service->organization_id !== $organization->id) {
                abort(404);
            }

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $slots = $this->slotService->generateSlots(
                $organization,
                $service,
                $startDate,
                $endDate
            );

            return redirect()
                ->route('slots.index', $organization)
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
