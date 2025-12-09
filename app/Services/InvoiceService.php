<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\SubscriptionPayment;

class InvoiceService
{
    /**
     * Generate invoice for a booking
     */
    public function generateBookingInvoice(Booking $booking, Payment $payment): Invoice
    {
        // Determine payment status based on payment method
        $status = $this->determinePaymentStatus($payment->gateway_type);
        $paidBy = $this->getPaidByText($payment->gateway_type);
        
        $invoice = Invoice::create([
            'invoice_type' => 'booking',
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'organization_id' => $booking->organization_id,
            'subtotal' => $payment->amount,
            'tax' => 0, // Can be calculated if needed
            'discount' => 0,
            'total' => $payment->amount,
            'payment_method' => $payment->gateway_type,
            'paid_by' => $paidBy,
            'paid_at' => $status === 'paid' ? now() : null,
            'status' => $status,
            'issued_at' => now(),
        ]);

        return $invoice;
    }

    /**
     * Generate invoice for a subscription payment
     */
    public function generateSubscriptionInvoice(SubscriptionPayment $subscriptionPayment): Invoice
    {
        // Determine payment status based on payment method
        $status = $this->determinePaymentStatus($subscriptionPayment->payment_method);
        $paidBy = $this->getPaidByText($subscriptionPayment->payment_method);
        
        $invoice = Invoice::create([
            'invoice_type' => 'subscription',
            'subscription_payment_id' => $subscriptionPayment->id,
            'organization_id' => $subscriptionPayment->organization_id,
            'subtotal' => $subscriptionPayment->amount,
            'tax' => 0,
            'discount' => 0,
            'total' => $subscriptionPayment->amount,
            'payment_method' => $subscriptionPayment->payment_method,
            'paid_by' => $paidBy,
            'paid_at' => $status === 'paid' ? now() : null,
            'status' => $status,
            'issued_at' => now(),
        ]);

        return $invoice;
    }

    /**
     * Determine payment status based on payment method
     * 
     * Online payments (esewa, khalti, stripe) and verified bank transfers are marked as paid
     * Cash payments are marked as unpaid
     */
    private function determinePaymentStatus(string $paymentMethod): string
    {
        return match($paymentMethod) {
            'esewa', 'khalti', 'stripe', 'bank_transfer' => 'paid',
            'cash' => 'unpaid',
            default => 'unpaid',
        };
    }

    /**
     * Get "Paid By" text for invoice
     */
    private function getPaidByText(string $paymentMethod): string
    {
        return match($paymentMethod) {
            'esewa' => 'eSewa',
            'khalti' => 'Khalti',
            'stripe' => 'Stripe',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            default => ucfirst($paymentMethod),
        };
    }

    /**
     * Check if invoice already exists for booking
     */
    public function hasBookingInvoice(Booking $booking): bool
    {
        return Invoice::where('booking_id', $booking->id)
            ->where('invoice_type', 'booking')
            ->exists();
    }

    /**
     * Check if invoice already exists for subscription payment
     */
    public function hasSubscriptionInvoice(SubscriptionPayment $subscriptionPayment): bool
    {
        return Invoice::where('subscription_payment_id', $subscriptionPayment->id)
            ->where('invoice_type', 'subscription')
            ->exists();
    }
}
