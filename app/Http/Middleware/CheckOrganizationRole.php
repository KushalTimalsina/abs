<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class CheckOrganizationRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $organization = $request->route('organization');
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$organization instanceof Organization) {
             $orgId = $request->route('organization_id') ?? $request->route('organization'); // Handle string ID if not bound
             if ($orgId && is_string($orgId) || is_int($orgId)) {
                 $organization = Organization::find($orgId);
             }
        }

        if (!$organization) {
             return response()->json(['message' => 'Organization not found context.'], 404);
        }

        // Check each allowed role
        $hasRole = false;
        if (empty($roles)) {
            // If no specific role requested, just check membership
             $hasRole = $user->organizations()->where('organization_id', $organization->id)->exists();
        } else {
            foreach ($roles as $role) {
                if ($user->hasRole($organization->id, $role)) {
                    $hasRole = true;
                    break;
                }
            }
        }

        if (!$hasRole) {
            return response()->json(['message' => 'You do not have the required role to access this resource.'], 403);
        }

        return $next($request);
    }
}
