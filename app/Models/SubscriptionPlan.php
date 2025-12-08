<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'max_team_members',
        'max_services',
        'max_bookings_per_month',
        'slot_scheduling_days',
        'payment_methods',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get organizations subscribed to this plan
     */
    public function organizations()
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    /**
     * Check if a feature is enabled for this plan
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get all subscriptions using this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    /**
     * Get all payments for this plan
     */
    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Check if payment method is allowed
     */
    public function allowsPaymentMethod(string $method): bool
    {
        return in_array($method, $this->payment_methods ?? []);
    }

    /**
     * Get price in rupees (from paisa)
     */
    public function getPriceInRupeesAttribute(): float
    {
        return $this->price / 100;
    }
}
