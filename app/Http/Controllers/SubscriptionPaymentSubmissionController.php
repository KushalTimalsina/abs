<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionConfirmation;

class SubscriptionPaymentSubmissionController extends Controller
{
    /**
     * Show the payment submission page
     */
    public function show()
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

        // Get active payment gateways
        $paymentGateways = \App\Models\PaymentSetting::activeGateways();

        return view('subscription.payment', compact('payment', 'paymentGateways'));
    }

    /**
     * Submit payment proof
     */
    public function submit(Request $request)
    {
        $paymentId = session('pending_payment_id');
        
        if (!$paymentId) {
            return redirect()->route('dashboard')
                ->with('error', 'No pending payment found.');
        }

        $payment = SubscriptionPayment::findOrFail($paymentId);

        // Check if user owns this organization
        if (!Auth::user()->organizations->contains($payment->organization_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,esewa,khalti,bank_transfer',
            'transaction_id' => 'nullable|string|max:255',
            'payment_proof' => 'nullable|image|max:2048', // 2MB max
            'admin_notes' => 'nullable|string|max:500',
        ]);

        // Handle file upload
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');
            $validated['payment_proof'] = $path;
        }

        // Update payment
        $payment->update([
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'payment_proof' => $validated['payment_proof'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'status' => 'pending', // Waiting for admin verification
        ]);

        // Clear pending payment from session
        session()->forget('pending_payment_id');

        return redirect()->route('dashboard')
            ->with('success', 'Payment submitted successfully! Your subscription will be activated once verified by our team.');
    }

    /**
     * Skip payment (for testing or free trials)
     */
    public function skip()
    {
        $paymentId = session('pending_payment_id');
        
        if (!$paymentId) {
            return redirect()->route('dashboard');
        }

        $payment = SubscriptionPayment::findOrFail($paymentId);

        // Check if user owns this organization
        if (!Auth::user()->organizations->contains($payment->organization_id)) {
            abort(403);
        }

        // For now, just mark as pending
        // In production, you might want to delete this or mark differently
        session()->forget('pending_payment_id');

        return redirect()->route('dashboard')
            ->with('info', 'Payment skipped. Please complete payment to activate your subscription.');
    }
}
