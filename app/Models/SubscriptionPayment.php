<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'subscription_plan_id',
        'payment_method',
        'amount',
        'transaction_id',
        'payment_proof',
        'status',
        'admin_notes',
        'verified_at',
        'verified_by',
        'duration_months',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the subscription plan
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the admin who verified
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the invoice for this subscription payment
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
