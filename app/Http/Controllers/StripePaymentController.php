<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Models\OrganizationSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Stripe checkout session
     */
    public function createCheckout(Request $request)
    {
        $paymentId = session('pending_payment_id');
        
        if (!$paymentId) {
            return redirect()->route('dashboard')
                ->with('error', 'No pending payment found.');
        }

        $payment = SubscriptionPayment::with(['subscriptionPlan', 'organization'])
            ->findOrFail($paymentId);

        // Check if user owns this organization
        if (!Auth::user()->organizations->contains($payment->organization_id)) {
            abort(403);
        }

        try {
            // Create Stripe checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $payment->subscriptionPlan->name,
                            'description' => $payment->subscriptionPlan->description,
                        ],
                        'unit_amount' => $payment->subscriptionPlan->stripe_price, // Amount in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.payment.show'),
                'client_reference_id' => $payment->id,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'organization_id' => $payment->organization_id,
                ],
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create payment session: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful payment
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('dashboard')
                ->with('error', 'Invalid payment session.');
        }

        try {
            $session = Session::retrieve($sessionId);
            
            if ($session->payment_status === 'paid') {
                $paymentId = $session->metadata->payment_id;
                $payment = SubscriptionPayment::findOrFail($paymentId);

                // Update payment record
                $payment->update([
                    'payment_method' => 'stripe',
                    'transaction_id' => $session->payment_intent,
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);

                // Create subscription
                $plan = $payment->subscriptionPlan;
                $subscription = OrganizationSubscription::create([
                    'organization_id' => $payment->organization_id,
                    'subscription_plan_id' => $plan->id,
                    'start_date' => now(),
                    'end_date' => now()->addDays($plan->duration_days),
                    'is_active' => true,
                ]);

                // Activate organization
                $payment->organization->update(['status' => 'active']);

                // Send confirmation email (queued)
                \Mail::to($payment->organization->email ?? $payment->organization->users->first()->email)
                    ->queue(new \App\Mail\SubscriptionConfirmation($payment->organization, $subscription));

                // Clear session
                session()->forget('pending_payment_id');

                return redirect()->route('dashboard')
                    ->with('success', 'Payment successful! Your subscription is now active.');
            }

            return redirect()->route('subscription.payment.show')
                ->with('error', 'Payment was not completed.');
                
        } catch (\Exception $e) {
            return redirect()->route('subscription.payment.show')
                ->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    // Payment is successful and the subscription is created
                    // You can provision the subscription here
                    break;
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    // Payment succeeded
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    // Payment failed
                    break;
                default:
                    // Unexpected event type
                    return response()->json(['error' => 'Unexpected event type'], 400);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
