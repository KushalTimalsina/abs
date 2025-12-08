<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class OrganizationPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $organizationId = $request->route('organization') ?? session('current_organization_id');

        if (!$organizationId) {
            abort(403, 'No organization context');
        }

        $organization = Organization::find($organizationId);

        if (!$organization) {
            abort(404, 'Organization not found');
        }

        // Get user's role and permissions in this organization
        $membership = $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->first();

        if (!$membership) {
            abort(403, 'You are not a member of this organization');
        }

        $userRole = $membership->pivot->role;
        $userPermissions = $membership->pivot->permissions ?? [];

        // Admin role has all permissions
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if user has any of the required permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'You do not have permission to perform this action. Required: ' . implode(' or ', $permissions));
        }

        return $next($request);
    }
}
