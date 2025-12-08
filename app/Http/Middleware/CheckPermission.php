<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $organization = $request->route('organization');
         if (!$organization instanceof Organization) {
             $orgId = $request->route('organization_id') ?? $request->route('organization');
             if ($orgId && (is_string($orgId) || is_int($orgId))) {
                 $organization = Organization::find($orgId);
             }
        }

        if (!$organization) {
            return response()->json(['message' => 'Organization context required for permission check.'], 400);
        }

        // Admins within organization usually have all permissions, or explicitly check 'all'.
        // For simplicity, let's assume 'admin' role bypasses granular permissions or check explicit permission.
        
        if ($user->hasRole($organization->id, 'admin')) {
             return $next($request);
        }

        if (!$user->hasPermission($organization->id, $permission)) {
            return response()->json(['message' => "You do not have permission: $permission"], 403);
        }

        return $next($request);
    }
}
