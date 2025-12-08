<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine if the user can view the organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Determine if the user can update the organization.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Determine if the user can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('role', 'admin')
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Determine if the user can manage team members.
     */
    public function manageTeam(User $user, Organization $organization): bool
    {
        $membership = $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$membership) {
            return false;
        }

        $role = $membership->pivot->role;
        $permissions = $membership->pivot->permissions ?? [];

        return $role === 'admin' || in_array('manage_team', $permissions);
    }

    /**
     * Determine if the user can manage services.
     */
    public function manageServices(User $user, Organization $organization): bool
    {
        $membership = $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$membership) {
            return false;
        }

        $role = $membership->pivot->role;
        $permissions = $membership->pivot->permissions ?? [];

        return $role === 'admin' || in_array('manage_services', $permissions);
    }
}
