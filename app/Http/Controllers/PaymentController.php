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
                ->route('organization.bookings.show', [$organization, $booking])
                ->with('info', 'This booking has already been paid');
        }

        // Eager load relationships to avoid null errors
        $booking->load(['customer', 'service', 'staff', 'slot']);

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
            'gateway' => ['required', 'in:esewa,khalti,stripe,bank_transfer'],
            'payment_proof' => ['required_if:gateway,esewa,bank_transfer', 'image', 'max:2048'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            // Handle manual payment methods (eSewa and Bank Transfer)
            if (in_array($validated['gateway'], ['esewa', 'bank_transfer'])) {
                return $this->handleManualPayment($request, $organization, $booking, $validated);
            }

            // Handle online payment gateways (Khalti and Stripe)
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
     * Handle manual payment (eSewa and Bank Transfer)
     */
    protected function handleManualPayment(Request $request, Organization $organization, Booking $booking, array $validated)
    {
        DB::beginTransaction();
        try {
            // Upload payment proof
            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
            }

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'organization_id' => $organization->id,
                'amount' => $booking->service->price ?? 0,
                'payment_method' => $validated['gateway'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'payment_proof' => $proofPath,
                'status' => 'pending', // Pending verification
                'payment_date' => now(),
            ]);

            // Update booking payment status
            $booking->update([
                'payment_status' => 'pending',
            ]);

            DB::commit();

            return redirect()
                ->route('organization.bookings.show', [$organization, $booking])
                ->with('success', 'Payment proof uploaded successfully. Awaiting verification.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
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
        if ($booking->organization_id !== $organization->id) {
            abort(404);
        }

        if ($booking->payment_status === 'paid') {
            return redirect()
                ->route('organization.bookings.show', [$organization, $booking])
                ->with('info', 'This booking has already been paid');
        }

        try {
            // Create payment record
            $payment = Payment::create([
                'organization_id' => $organization->id,
                'booking_id' => $booking->id,
                'amount' => $booking->service?->price ?? 0,
                'payment_method' => 'cash',
                'status' => 'completed',
                'currency' => 'NPR',
                'transaction_id' => 'CASH-' . time(),
            ]);

            // Update booking payment status
            $booking->update([
                'payment_status' => 'paid',
            ]);

            return redirect()
                ->route('organization.bookings.show', [$organization, $booking])
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
    /**
     * Show payment details
     */
    public function showDetail(Organization $organization, $paymentId)
    {
        $this->authorize('view', $organization);

        // Find payment within this organization
        $payment = $organization->payments()->findOrFail($paymentId);

        $payment->load(['booking.customer', 'booking.service', 'booking.staff']);

        return view('payments.detail', compact('organization', 'payment'));
    }

    /**
     * Verify manual payment
     */
    public function verifyPayment(Request $request, Organization $organization, $paymentId)
    {
        $this->authorize('view', $organization);

        // Find payment within this organization
        $payment = $organization->payments()->findOrFail($paymentId);

        $validated = $request->validate([
            'status' => ['required', 'in:completed,failed'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            // Update payment status
            $payment->update([
                'status' => $validated['status'],
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Update booking payment status
            if ($payment->booking) {
                $payment->booking->update([
                    'payment_status' => $validated['status'] === 'completed' ? 'paid' : 'unpaid',
                ]);
            }

            DB::commit();

            $message = $validated['status'] === 'completed' 
                ? 'Payment verified and marked as completed'
                : 'Payment marked as failed';

            return redirect()
                ->route('organization.payments.index', $organization)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * List all payments
     */
    public function index(Organization $organization, Request $request)
    {
        $this->authorize('view', $organization);

        $query = $organization->payments()->with(['booking', 'invoice']);

        // Filter by status
        if ($request->filled('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('method') && !empty($request->method)) {
            $query->where('payment_method', $request->method);
        }

        // Filter by date range
        if ($request->filled('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        
        $allowedSortFields = ['id', 'amount', 'payment_method', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 20);
        $payments = $query->paginate($perPage)->withQueryString();

        return view('payments.index', compact('organization', 'payments'));
    }
}
