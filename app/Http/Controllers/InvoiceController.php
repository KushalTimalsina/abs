<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Booking;
use App\Models\SubscriptionPayment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        // Authorization: Check if user can view this invoice
        $user = auth()->user();
        
        if ($invoice->isBookingInvoice()) {
            $invoice->load(['booking.service', 'booking.customer', 'booking.organization', 'payment']);
            
            // Customers can only view their own booking invoices
            if ($user->user_type === 'customer' && $invoice->booking->customer_id !== $user->id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        } else {
            $invoice->load(['subscriptionPayment.subscriptionPlan', 'subscriptionPayment.organization']);
            
            // Customers cannot view subscription invoices
            if ($user->user_type === 'customer') {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Download invoice as PDF
     */
    public function download(Invoice $invoice)
    {
        // Authorization: Check if user can download this invoice
        $user = auth()->user();
        
        if ($invoice->isBookingInvoice()) {
            $invoice->load(['booking.service', 'booking.customer', 'booking.organization', 'payment']);
            
            // Customers can only download their own booking invoices
            if ($user->user_type === 'customer' && $invoice->booking->customer_id !== $user->id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        } else {
            $invoice->load(['subscriptionPayment.subscriptionPlan', 'subscriptionPayment.organization']);
            
            // Customers cannot download subscription invoices
            if ($user->user_type === 'customer') {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    /**
     * Generate invoice for a booking
     */
    public function generateForBooking(Booking $booking)
    {
        // Check if invoice already exists
        if ($this->invoiceService->hasBookingInvoice($booking)) {
            return redirect()->back()->with('error', 'Invoice already exists for this booking');
        }

        // Check if booking has payment
        if (!$booking->payment) {
            return redirect()->back()->with('error', 'No payment found for this booking');
        }

        $invoice = $this->invoiceService->generateBookingInvoice($booking, $booking->payment);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice generated successfully');
    }

    /**
     * Generate invoice for a subscription payment
     */
    public function generateForSubscription(SubscriptionPayment $subscriptionPayment)
    {
        // Check if invoice already exists
        if ($this->invoiceService->hasSubscriptionInvoice($subscriptionPayment)) {
            return redirect()->back()->with('error', 'Invoice already exists for this subscription payment');
        }

        $invoice = $this->invoiceService->generateSubscriptionInvoice($subscriptionPayment);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice generated successfully');
    }

    /**
     * List all invoices (for admin/organization)
     */
    public function index(Request $request)
    {
        $query = Invoice::query();

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['booking', 'subscription'])) {
            $query->where('invoice_type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['paid', 'unpaid'])) {
            $query->where('status', $request->status);
        }

        // Filter by organization (if not superadmin)
        if (auth()->user()->user_type !== 'superadmin') {
            $organizationIds = auth()->user()->organizations()->pluck('organizations.id');
            $query->whereIn('organization_id', $organizationIds);
        }

        $perPage = $request->input('per_page', 20);
        $invoices = $query->with(['booking', 'subscriptionPayment', 'organization'])
            ->latest()
            ->paginate($perPage)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Regenerate invoice with current payment information
     */
    public function regenerate(Invoice $invoice)
    {
        // Authorization: Only admins and organization staff can regenerate invoices
        $user = auth()->user();
        if ($user->user_type === 'customer') {
            abort(403, 'Customers are not authorized to regenerate invoices.');
        }
        
        // Get the payment for this invoice
        $payment = $invoice->payment;
        
        if (!$payment) {
            return redirect()->back()->with('error', 'No payment found for this invoice');
        }

        // Update invoice with current payment information
        $invoice->update([
            'payment_method' => $payment->payment_method,
            'paid_by' => match($payment->payment_method) {
                'esewa' => 'eSewa',
                'khalti' => 'Khalti',
                'stripe' => 'Stripe',
                'bank_transfer' => 'Bank Transfer',
                'cash' => 'Cash',
                default => ucfirst($payment->payment_method),
            },
            'status' => $payment->status === 'completed' ? 'paid' : 'unpaid',
            'paid_at' => $payment->status === 'completed' ? ($invoice->paid_at ?? now()) : null,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice regenerated successfully with current payment information');
    }

    /**
     * Email invoice to customer
     */
    public function email(Invoice $invoice)
    {
        // Authorization: Only admins and organization staff can email invoices
        $user = auth()->user();
        if ($user->user_type === 'customer') {
            abort(403, 'Customers are not authorized to email invoices.');
        }
        
        try {
            // Get customer email based on invoice type
            if ($invoice->isBookingInvoice()) {
                $customerEmail = $invoice->booking->customer_email;
                $customerName = $invoice->booking->customer_name;
            } else {
                $customerEmail = $invoice->subscriptionPayment->organization->email;
                $customerName = $invoice->subscriptionPayment->organization->name;
            }

            if (!$customerEmail) {
                return redirect()->back()->with('error', 'No email address found for this invoice');
            }

            // Dispatch job to send email in background
            \App\Jobs\SendInvoiceEmail::dispatch($invoice, $customerEmail, $customerName);

            return redirect()->back()
                ->with('success', 'Invoice is being sent to ' . $customerEmail . '. You will be notified once it\'s delivered.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to queue invoice email: ' . $e->getMessage());
        }
    }
}
