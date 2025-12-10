<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'organization_id',
        'amount',
        'payment_method',
        'gateway_type',
        'transaction_id',
        'payment_proof',
        'payment_data',
        'status',
        'paid_at',
        'payment_date',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'payment_date' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the booking this payment belongs to
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the organization this payment belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the invoice for this payment
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get amount in rupees (from paisa)
     */
    public function getAmountInRupeesAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(string $transactionId, array $paymentData = [])
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_data' => $paymentData,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(array $paymentData = [])
    {
        $this->update([
            'status' => 'failed',
            'payment_data' => $paymentData,
        ]);
    }

    /**
     * Scope to get completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
