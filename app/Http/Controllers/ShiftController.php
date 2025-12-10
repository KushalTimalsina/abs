<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * Display shifts
     */
    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);
        
        $query = $organization->shifts()->with('user');
        
        // Sorting
        $sortField = $request->input('sort', 'day_of_week');
        $sortDirection = $request->input('direction', 'asc');
        
        $allowedSortFields = ['id', 'day_of_week', 'start_time', 'end_time', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'day_of_week';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        $query->orderBy($sortField, $sortDirection);
        
        // Secondary sort by start_time if sorting by day
        if ($sortField === 'day_of_week') {
            $query->orderBy('start_time', 'asc');
        }
        
        // Get all shifts (not paginated) since we're grouping by user
        $shifts = $query->get()->groupBy('user_id');
            
        $teamMembers = $organization->users()
            ->wherePivot('status', 'active')
            ->whereIn('user_type', ['team_member', 'frontdesk'])
            ->get();
        
        return view('shifts.index', compact('organization', 'shifts', 'teamMembers'));
    }

    /**
     * Store a new shift
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('manageServices', $organization);
        
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required_without:specific_date', 'nullable', 'integer', 'between:0,6'],
            'specific_date' => ['required_without:day_of_week', 'nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_recurring' => ['boolean'],
        ]);

        // Verify user is member of organization
        $isMember = $organization->users()
            ->wherePivot('user_id', $validated['user_id'])
            ->wherePivot('status', 'active')
            ->exists();
            
        if (!$isMember) {
            return redirect()
                ->back()
                ->with('error', 'User is not a member of this organization');
        }

        $validated['organization_id'] = $organization->id;
        $validated['is_recurring'] = $request->has('is_recurring');

        Shift::create($validated);

        return redirect()
            ->route('organization.shifts.index', $organization)
            ->with('success', 'Shift created successfully');
    }

    /**
     * Update shift
     */
    public function update(Request $request, Organization $organization, Shift $shift)
    {
        $this->authorize('manageServices', $organization);
        
        if ($shift->organization_id !== $organization->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'day_of_week' => ['required_without:specific_date', 'nullable', 'integer', 'between:0,6'],
            'specific_date' => ['required_without:day_of_week', 'nullable', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_recurring' => ['boolean'],
        ]);

        $validated['is_recurring'] = $request->has('is_recurring');

        $shift->update($validated);

        return redirect()
            ->route('organization.shifts.index', $organization)
            ->with('success', 'Shift updated successfully');
    }

    /**
     * Delete shift
     */
    public function destroy(Organization $organization, Shift $shift)
    {
        $this->authorize('manageServices', $organization);
        
        if ($shift->organization_id !== $organization->id) {
            abort(404);
        }
        
        $shift->delete();

        return redirect()
            ->route('organization.shifts.index', $organization)
            ->with('success', 'Shift deleted successfully');
    }

    /**
     * Bulk create shifts for a team member
     */
    public function bulkStore(Request $request, Organization $organization)
    {
        $this->authorize('manageServices', $organization);
        
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'shifts' => ['required', 'array', 'min:1'],
            'shifts.*.start_time' => ['required', 'date_format:H:i'],
            'shifts.*.end_time' => ['required', 'date_format:H:i'],
        ]);

        // Verify user is member of organization
        $isMember = $organization->users()
            ->wherePivot('user_id', $validated['user_id'])
            ->wherePivot('status', 'active')
            ->exists();
            
        if (!$isMember) {
            return redirect()
                ->back()
                ->with('error', 'User is not a member of this organization');
        }

        $createdCount = 0;
        foreach ($validated['shifts'] as $shiftData) {
            Shift::create([
                'organization_id' => $organization->id,
                'user_id' => $validated['user_id'],
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $shiftData['start_time'],
                'end_time' => $shiftData['end_time'],
                'is_recurring' => true,
            ]);
            $createdCount++;
        }

        return redirect()
            ->route('organization.shifts.index', $organization)
            ->with('success', "Created {$createdCount} shifts successfully");
    }
}
