<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        // Try to get organization from route parameter or user context
        $organization = $request->route('organization');
        
        if (!$organization && $request->user()) {
             // Logic to determine organization from user if not in route (e.g., current active org session)
             // For now, relies on route injection or passed model
        }

        if (!$organization instanceof Organization) {
            // Need a way to resolve organization context. 
            // If the route has an ID, we can resolve it.
             $orgId = $request->route('organization_id') ?? $request->input('organization_id');
             if ($orgId) {
                 $organization = Organization::find($orgId);
             }
        }

        if (!$organization) {
             // If we can't contextually find an organization, we might skip or fail depending on route strictness.
             // For routes strictly under /organization/{organization}, user binding should handle it if route model binding is on.
             return $next($request);
        }

        if (!$organization->hasActiveSubscription()) {
            return response()->json(['message' => 'Organization does not have an active subscription.'], 403);
        }

        if ($feature) {
             $plan = $organization->getCurrentPlan();
             if (!$plan || !$plan->hasFeature($feature)) {
                 return response()->json(['message' => "This feature ({$feature}) is not available on your current plan."], 403);
             }
        }

        return $next($request);
    }
}
