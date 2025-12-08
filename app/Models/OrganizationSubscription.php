<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrganizationSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the organization for this subscription
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the subscription plan
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               (!$this->end_date || $this->end_date->isFuture());
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return !$this->is_active || 
               ($this->end_date && $this->end_date->isPast());
    }

    /**
     * Get days remaining in subscription
     */
    public function daysRemaining(): int
    {
        if (!$this->end_date) return PHP_INT_MAX;
        
        return max(0, Carbon::now()->diffInDays($this->end_date, false));
    }

    /**
     * Renew subscription
     */
    public function renew(int $days = 30)
    {
        $this->end_date = Carbon::now()->addDays($days);
        $this->is_active = true;
        $this->save();
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $this->is_active = false;
        $this->save();
    }
}
