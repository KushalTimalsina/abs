<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_id',
        'invoice_number',
        'subtotal',
        'tax',
        'discount',
        'total',
        'issued_at',
        'due_at',
        'status',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
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
     * Get total in rupees (from paisa)
     */
    public function getTotalInRupeesAttribute(): float
    {
        return $this->total / 100;
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid()
    {
        $this->update(['status' => 'paid']);
    }

    /**
     * Calculate total from subtotal, tax, and discount
     */
    public function calculateTotal()
    {
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }
}
