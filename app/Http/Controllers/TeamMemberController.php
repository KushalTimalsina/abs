<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamMemberController extends Controller
{
    /**
     * Display team members
     */
    public function index(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        $teamMembers = $organization->users()
            ->withPivot('role', 'permissions', 'status', 'joined_at')
            ->paginate(15);
            
        $pendingInvitations = $organization->teamInvitations()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();
        
        return view('team.index', compact('organization', 'teamMembers', 'pendingInvitations'));
    }

    /**
     * Show form to create new team member
     */
    public function create(Organization $organization)
    {
        $this->authorize('manageTeam', $organization);
        
        return view('team.create', compact('organization'));
    }

    /**
     * Store new team member
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('manageTeam', $organization);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,team_member,frontdesk'],
            'permissions' => ['nullable', 'array'],
        ]);

        // Check team member limit
        if (!$organization->canAddTeamMember()) {
            return redirect()
                ->back()
                ->with('error', 'Team member limit reached for your subscription plan');
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'user_type' => 'team_member',
        ]);

        // Attach to organization
        $organization->users()->attach($user->id, [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return redirect()
            ->route('organization.team.index', $organization)
            ->with('success', 'Team member added successfully');
    }

    /**
     * Show form to edit team member
     */
    public function edit(Organization $organization, User $user)
    {
        $this->authorize('manageTeam', $organization);
        
        // Load user with pivot data from organization relationship
        $user = $organization->users()
            ->where('users.id', $user->id)
            ->withPivot('role', 'permissions', 'status', 'joined_at')
            ->firstOrFail();
        
        return view('team.edit', compact('organization', 'user'));
    }

    /**
     * Update team member role and permissions
     */
    public function update(Request $request, Organization $organization, User $user)
    {
        $this->authorize('manageTeam', $organization);
        
        $validated = $request->validate([
            'role' => ['required', 'in:admin,team_member,frontdesk'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Check if trying to modify the last admin
        if ($request->role !== 'admin') {
            $adminCount = $organization->users()
                ->wherePivot('role', 'admin')
                ->wherePivot('status', 'active')
                ->count();
                
            $currentRole = $organization->users()
                ->wherePivot('user_id', $user->id)
                ->first()->pivot->role;
                
            if ($currentRole === 'admin' && $adminCount <= 1) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot change role of the last admin');
            }
        }

        $organization->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
        ]);

        return redirect()
            ->route('team.index', $organization)
            ->with('success', 'Team member updated successfully');
    }

    /**
     * Remove team member
     */
    public function destroy(Organization $organization, User $user)
    {
        $this->authorize('manageTeam', $organization);
        
        // Check if trying to remove the last admin
        $adminCount = $organization->users()
            ->wherePivot('role', 'admin')
            ->wherePivot('status', 'active')
            ->count();
            
        $currentRole = $organization->users()
            ->wherePivot('user_id', $user->id)
            ->first()->pivot->role;
            
        if ($currentRole === 'admin' && $adminCount <= 1) {
            return redirect()
                ->back()
                ->with('error', 'Cannot remove the last admin');
        }

        // Soft delete by updating status
        $organization->users()->updateExistingPivot($user->id, [
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('team.index', $organization)
            ->with('success', 'Team member removed successfully');
    }

    /**
     * Reactivate team member
     */
    public function reactivate(Organization $organization, User $user)
    {
        $this->authorize('manageTeam', $organization);
        
        // Check team member limit
        if (!$organization->canAddTeamMember()) {
            return redirect()
                ->back()
                ->with('error', 'Team member limit reached for your subscription plan');
        }

        $organization->users()->updateExistingPivot($user->id, [
            'status' => 'active',
        ]);

        return redirect()
            ->route('team.index', $organization)
            ->with('success', 'Team member reactivated successfully');
    }
}
