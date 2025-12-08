<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionUpgradeController extends Controller
{
    /**
     * Show subscription management page
     */
    public function index()
    {
        return view('subscription.index');
    }

    /**
     * Handle subscription upgrade/downgrade request
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = Auth::user();
        $organization = $user->organizations()->first();

        if (!$organization) {
            return redirect()->back()
                ->with('error', 'No organization found for your account.');
        }

        $newPlan = SubscriptionPlan::findOrFail($request->plan_id);
        $currentSubscription = $organization->subscription;

        // Check if trying to select the same plan
        if ($currentSubscription && $currentSubscription->plan_id == $newPlan->id) {
            return redirect()->back()
                ->with('info', 'You are already subscribed to this plan.');
        }

        // Create a new subscription payment for the upgrade
        $subscriptionPayment = SubscriptionPayment::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $newPlan->id,
            'amount' => $newPlan->price,
            'payment_method' => 'pending',
            'status' => 'pending',
            'duration_months' => ceil($newPlan->duration_days / 30),
            'start_date' => now(),
            'end_date' => now()->addDays($newPlan->duration_days),
        ]);

        // Store payment ID in session
        session(['pending_payment_id' => $subscriptionPayment->id]);

        // Redirect to payment page
        return redirect()->route('subscription.payment.show')
            ->with('success', 'Please complete payment to activate your new subscription plan.');
    }
}
