<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Models\OrganizationSubscription;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionPaymentController extends Controller
{
    /**
     * Display all subscription payments
     */
    public function index(Request $request)
    {
        $query = SubscriptionPayment::with(['organization', 'subscriptionPlan']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest()->paginate(20);

        return view('superadmin.subscriptions.payments', compact('payments'));
    }

    /**
     * Show payment details
     */
    public function show(SubscriptionPayment $payment)
    {
        $payment->load(['organization', 'subscriptionPlan', 'verifiedBy']);

        return view('superadmin.subscriptions.show', compact('payment'));
    }

    /**
     * Verify payment
     */
    public function verify(Request $request, SubscriptionPayment $payment)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'duration_months' => 'required|integer|min:1|max:12',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addMonths($request->duration_months);

        // Update payment
        $payment->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'admin_notes' => $request->admin_notes,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Update or create organization subscription
        $subscription = OrganizationSubscription::updateOrCreate(
            ['organization_id' => $payment->organization_id],
            [
                'subscription_plan_id' => $payment->subscription_plan_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => true,
            ]
        );

        // Activate the organization
        $payment->organization->update(['status' => 'active']);

        // Send subscription confirmation email (queued) - with error handling
        try {
            // Get organization owner email
            $recipientEmail = $payment->organization->email;
            
            // If organization doesn't have email, get from first admin user
            if (!$recipientEmail) {
                $adminUser = $payment->organization->users()
                    ->wherePivot('role', 'admin')
                    ->first();
                    
                if ($adminUser) {
                    $recipientEmail = $adminUser->email;
                }
            }
            
            if ($recipientEmail) {
                \Mail::to($recipientEmail)
                    ->queue(new \App\Mail\SubscriptionConfirmation($payment->organization, $subscription));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send subscription confirmation email: ' . $e->getMessage());
            // Continue anyway - email failure shouldn't block verification
        }

        // Auto-generate invoice
        try {
            $invoiceService = app(InvoiceService::class);
            if (!$invoiceService->hasSubscriptionInvoice($payment)) {
                $invoiceService->generateSubscriptionInvoice($payment);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice: ' . $e->getMessage());
            // Continue anyway
        }

        return redirect()->back()
            ->with('success', 'Payment verified and subscription activated successfully!');
    }

    /**
     * Reject payment
     */
    public function reject(Request $request, SubscriptionPayment $payment)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $payment->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()
            ->with('success', 'Payment rejected');
    }
}
