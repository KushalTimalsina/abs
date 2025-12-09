<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $invoiceService;

    public function __construct(PaymentService $paymentService, InvoiceService $invoiceService)
    {
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Show payment page
     */
    public function show(Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        if ($booking->payment_status === 'paid') {
            return redirect()
                ->route('bookings.show', [$organization, $booking])
                ->with('info', 'This booking has already been paid');
        }

        $availableGateways = $organization->paymentGateways()
            ->where('is_active', true)
            ->get();

        return view('payments.show', compact('organization', 'booking', 'availableGateways'));
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request, Organization $organization, Booking $booking)
    {
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'gateway' => ['required', 'in:esewa,khalti,stripe'],
        ]);

        try {
            $paymentData = $this->paymentService->initiatePayment($booking, $validated['gateway']);

            if ($paymentData['type'] === 'redirect') {
                return view('payments.redirect', $paymentData);
            }

            if ($paymentData['type'] === 'stripe_checkout') {
                return view('payments.stripe', $paymentData);
            }

            return redirect()->back()->with('error', 'Unsupported payment type');
            
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * eSewa success callback
     */
    public function esewaSuccess(Request $request, Payment $payment)
    {
        try {
            $verified = $this->paymentService->verifyEsewaPayment($payment, $request->all());

            if ($verified) {
                // Auto-generate invoice
                if (!$this->invoiceService->hasBookingInvoice($payment->booking)) {
                    $this->invoiceService->generateBookingInvoice($payment->booking, $payment);
                }

                return redirect()
                    ->route('bookings.show', [$payment->booking->organization, $payment->booking])
                    ->with('success', 'Payment successful! Your booking is confirmed.');
            }

            return redirect()
                ->route('bookings.show', [$payment->booking->organization, $payment->booking])
                ->with('error', 'Payment verification failed. Please contact support.');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('bookings.show', [$payment->booking->organization, $payment->booking])
                ->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }

    /**
     * eSewa failure callback
     */
    public function esewaFailure(Payment $payment)
    {
        $payment->update(['status' => 'failed']);

        return redirect()
            ->route('payments.show', [$payment->booking->organization, $payment->booking])
            ->with('error', 'Payment was cancelled or failed. Please try again.');
    }

    /**
     * Khalti callback
     */
    public function khaltiCallback(Request $request, Payment $payment)
    {
        try {
            $pidx = $request->input('pidx');
            
            if (!$pidx) {
                throw new \Exception('Invalid payment response');
            }

            $verified = $this->paymentService->verifyKhaltiPayment($payment, $pidx);

            if ($verified) {
                // Auto-generate invoice
                if (!$this->invoiceService->hasBookingInvoice($payment->booking)) {
                    $this->invoiceService->generateBookingInvoice($payment->booking, $payment);
                }

                return redirect()
                    ->route('bookings.show', [$payment->booking->organization, $payment->booking])
                    ->with('success', 'Payment successful! Your booking is confirmed.');
            }

            return redirect()
                ->route('bookings.show', [$payment->booking->organization, $payment->booking])
                ->with('error', 'Payment verification failed. Please contact support.');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('bookings.show', [$payment->booking->organization, $payment->booking])
                ->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }

    /**
     * Stripe webhook
     */
    public function stripeWebhook(Request $request)
    {
        // Stripe webhook handling
        // This would require Stripe SDK and webhook signature verification
        // Simplified for now
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Process cash payment
     */
    public function processCash(Organization $organization, Booking $booking)
    {
        $this->authorize('view', $organization);

        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        if ($booking->payment_status === 'paid') {
            return redirect()
                ->back()
                ->with('info', 'This booking has already been paid');
        }

        try {
            $payment = $this->paymentService->processCashPayment($booking);

            // Auto-generate invoice for cash payment (will be marked as unpaid)
            if (!$this->invoiceService->hasBookingInvoice($booking)) {
                $this->invoiceService->generateBookingInvoice($booking, $payment);
            }

            return redirect()
                ->route('bookings.show', [$organization, $booking])
                ->with('success', 'Cash payment recorded successfully');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * View payment history
     */
    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);

        $query = $organization->payments()->with(['booking', 'invoice']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payments = $query->latest()->paginate(20);

        return view('payments.index', compact('organization', 'payments'));
    }
}
