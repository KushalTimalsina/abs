<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Initiate payment for a booking
     */
    public function initiatePayment(Booking $booking, string $gatewayName): array
    {
        $organization = $booking->organization;
        $gateway = $organization->paymentGateways()
            ->where('gateway_name', $gatewayName)
            ->where('is_active', true)
            ->firstOrFail();

        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'organization_id' => $organization->id,
            'amount' => $booking->service->price,
            'currency' => 'NPR', // Default to Nepali Rupees
            'payment_method' => $gatewayName,
            'status' => 'pending',
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
        ]);

        // Generate invoice
        $invoice = $this->generateInvoice($booking, $payment);

        // Initiate payment based on gateway
        switch ($gatewayName) {
            case 'esewa':
                return $this->initiateEsewaPayment($payment, $gateway);
            case 'khalti':
                return $this->initiateKhaltiPayment($payment, $gateway);
            case 'stripe':
                return $this->initiateStripePayment($payment, $gateway);
            default:
                throw new \Exception('Unsupported payment gateway');
        }
    }

    /**
     * Initiate eSewa payment
     */
    protected function initiateEsewaPayment(Payment $payment, PaymentGateway $gateway): array
    {
        $credentials = json_decode(Crypt::decryptString($gateway->credentials), true);
        
        $params = [
            'amt' => $payment->amount,
            'psc' => 0,
            'pdc' => 0,
            'txAmt' => 0,
            'tAmt' => $payment->amount,
            'pid' => $payment->transaction_id,
            'scd' => $credentials['merchant_id'],
            'su' => route('payment.esewa.success', $payment->id),
            'fu' => route('payment.esewa.failure', $payment->id),
        ];

        $esewaUrl = $gateway->is_test_mode 
            ? 'https://uat.esewa.com.np/epay/main'
            : 'https://esewa.com.np/epay/main';

        return [
            'type' => 'redirect',
            'url' => $esewaUrl,
            'method' => 'POST',
            'params' => $params,
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Initiate Khalti payment
     */
    protected function initiateKhaltiPayment(Payment $payment, PaymentGateway $gateway): array
    {
        $credentials = json_decode(Crypt::decryptString($gateway->credentials), true);
        
        $khaltiUrl = $gateway->is_test_mode
            ? 'https://a.khalti.com/api/v2/epayment/initiate/'
            : 'https://khalti.com/api/v2/epayment/initiate/';

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $credentials['secret_key'],
        ])->post($khaltiUrl, [
            'return_url' => route('payment.khalti.callback', $payment->id),
            'website_url' => url('/'),
            'amount' => $payment->amount * 100, // Khalti expects amount in paisa
            'purchase_order_id' => $payment->transaction_id,
            'purchase_order_name' => 'Booking Payment',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            $payment->update([
                'gateway_response' => $data,
            ]);

            return [
                'type' => 'redirect',
                'url' => $data['payment_url'],
                'payment_id' => $payment->id,
            ];
        }

        throw new \Exception('Failed to initiate Khalti payment: ' . $response->body());
    }

    /**
     * Initiate Stripe payment
     */
    protected function initiateStripePayment(Payment $payment, PaymentGateway $gateway): array
    {
        $credentials = json_decode(Crypt::decryptString($gateway->credentials), true);
        
        // Note: This is a simplified version. In production, use Stripe PHP SDK
        return [
            'type' => 'stripe_checkout',
            'publishable_key' => $credentials['publishable_key'],
            'amount' => $payment->amount * 100, // Stripe expects amount in cents
            'currency' => 'usd',
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Verify eSewa payment
     */
    public function verifyEsewaPayment(Payment $payment, array $responseData): bool
    {
        $gateway = $payment->organization->paymentGateways()
            ->where('gateway_name', 'esewa')
            ->firstOrFail();

        $credentials = json_decode(Crypt::decryptString($gateway->credentials), true);

        $verifyUrl = $gateway->is_test_mode
            ? 'https://uat.esewa.com.np/epay/transrec'
            : 'https://esewa.com.np/epay/transrec';

        $response = Http::asForm()->get($verifyUrl, [
            'amt' => $responseData['amt'] ?? $payment->amount,
            'rid' => $responseData['refId'] ?? '',
            'pid' => $payment->transaction_id,
            'scd' => $credentials['merchant_id'],
        ]);

        if ($response->successful() && str_contains($response->body(), 'Success')) {
            $payment->update([
                'status' => 'completed',
                'gateway_response' => $responseData,
                'paid_at' => now(),
            ]);

            $this->completeBookingPayment($payment);
            return true;
        }

        $payment->update(['status' => 'failed']);
        return false;
    }

    /**
     * Verify Khalti payment
     */
    public function verifyKhaltiPayment(Payment $payment, string $pidx): bool
    {
        $gateway = $payment->organization->paymentGateways()
            ->where('gateway_name', 'khalti')
            ->firstOrFail();

        $credentials = json_decode(Crypt::decryptString($gateway->credentials), true);

        $verifyUrl = $gateway->is_test_mode
            ? 'https://a.khalti.com/api/v2/epayment/lookup/'
            : 'https://khalti.com/api/v2/epayment/lookup/';

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $credentials['secret_key'],
        ])->post($verifyUrl, [
            'pidx' => $pidx,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            if ($data['status'] === 'Completed') {
                $payment->update([
                    'status' => 'completed',
                    'gateway_response' => $data,
                    'paid_at' => now(),
                ]);

                $this->completeBookingPayment($payment);
                return true;
            }
        }

        $payment->update(['status' => 'failed']);
        return false;
    }

    /**
     * Complete booking payment
     */
    protected function completeBookingPayment(Payment $payment): void
    {
        $booking = $payment->booking;
        
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        // Update invoice
        $invoice = $payment->invoice;
        if ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }

    /**
     * Generate invoice for booking
     */
    public function generateInvoice(Booking $booking, Payment $payment): Invoice
    {
        $organization = $booking->organization;
        
        // Generate invoice number
        $lastInvoice = Invoice::where('organization_id', $organization->id)
            ->latest('id')
            ->first();
        
        $invoiceNumber = 'INV-' . $organization->id . '-' . str_pad(
            ($lastInvoice ? $lastInvoice->id : 0) + 1,
            6,
            '0',
            STR_PAD_LEFT
        );

        return Invoice::create([
            'organization_id' => $organization->id,
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'invoice_number' => $invoiceNumber,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'customer_phone' => $booking->customer_phone,
            'items' => json_encode([
                [
                    'description' => $booking->service->name,
                    'quantity' => 1,
                    'unit_price' => $booking->service->price,
                    'total' => $booking->service->price,
                ]
            ]),
            'subtotal' => $booking->service->price,
            'tax' => 0,
            'total' => $booking->service->price,
            'status' => 'pending',
            'issued_at' => now(),
        ]);
    }

    /**
     * Process cash payment
     */
    public function processCashPayment(Booking $booking): Payment
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'organization_id' => $booking->organization_id,
            'amount' => $booking->service->price,
            'currency' => 'NPR',
            'payment_method' => 'cash',
            'status' => 'completed',
            'transaction_id' => 'CASH-' . strtoupper(Str::random(12)),
            'paid_at' => now(),
        ]);

        $invoice = $this->generateInvoice($booking, $payment);
        
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $payment;
    }
}
