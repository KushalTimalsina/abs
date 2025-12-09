<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_type',
        'booking_id',
        'payment_id',
        'subscription_payment_id',
        'organization_id',
        'invoice_number',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'paid_by',
        'paid_at',
        'issued_at',
        'due_at',
        'status',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
            if (empty($invoice->issued_at)) {
                $invoice->issued_at = now();
            }
        });
    }

    /**
     * Get the booking this invoice belongs to
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the payment for this invoice
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the subscription payment for this invoice
     */
    public function subscriptionPayment()
    {
        return $this->belongsTo(SubscriptionPayment::class);
    }

    /**
     * Get the organization for this invoice
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if this is a subscription invoice
     */
    public function isSubscriptionInvoice(): bool
    {
        return $this->invoice_type === 'subscription';
    }

    /**
     * Check if this is a booking invoice
     */
    public function isBookingInvoice(): bool
    {
        return $this->invoice_type === 'booking';
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' && $this->paid_at !== null;
    }

    /**
     * Get total in rupees (from paisa)
     */
    public function getTotalInRupeesAttribute(): float
    {
        return $this->total / 100;
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid($paidBy = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => $paidBy ?? $this->payment_method,
        ]);
    }

    /**
     * Mark invoice as unpaid
     */
    public function markAsUnpaid()
    {
        $this->update([
            'status' => 'unpaid',
            'paid_at' => null,
        ]);
    }

    /**
     * Calculate total from subtotal, tax, and discount
     */
    public function calculateTotal()
    {
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            'esewa' => 'eSewa',
            'khalti' => 'Khalti',
            'stripe' => 'Stripe',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            default => ucfirst($this->payment_method ?? 'N/A'),
        };
    }
}

