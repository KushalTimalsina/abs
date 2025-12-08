<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Get organization from route parameter or session
        $organizationId = $request->route('organization') ?? session('current_organization_id');

        if (!$organizationId) {
            // If no organization context, allow request (will be handled by controller)
            return $next($request);
        }

        $organization = Organization::find($organizationId);

        if (!$organization) {
            abort(404, 'Organization not found');
        }

        // Check if user is member of this organization
        $isMember = $organization->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isMember && $user->user_type !== 'customer') {
            abort(403, 'You are not a member of this organization');
        }

        // Check if organization has active subscription
        if (!$organization->hasActiveSubscription()) {
            // Check grace period (7 days after expiry)
            $lastSubscription = $organization->subscriptions()
                ->where('status', 'expired')
                ->latest('ends_at')
                ->first();

            if ($lastSubscription) {
                $gracePeriodEnd = $lastSubscription->ends_at->addDays(7);
                
                if (now()->greaterThan($gracePeriodEnd)) {
                    // Grace period expired, suspend organization
                    $organization->update(['status' => 'suspended']);
                    
                    return redirect()
                        ->route('organization.subscription.renew', $organization)
                        ->with('error', 'Your subscription has expired. Please renew to continue.');
                }
                
                // Still in grace period
                session()->flash('warning', 'Your subscription has expired. You have until ' . $gracePeriodEnd->format('M d, Y') . ' to renew.');
            } else {
                // No subscription at all
                return redirect()
                    ->route('organization.subscription.select', $organization)
                    ->with('error', 'Please select a subscription plan to continue.');
            }
        }

        // Store organization in request for easy access in controllers
        $request->merge(['current_organization' => $organization]);

        return $next($request);
    }
}
