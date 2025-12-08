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
        return in_array($feature, (array)($this->features ?? []));
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
        return in_array($method, (array)($this->payment_methods ?? []));
    }

    /**
     * Get price in USD for Stripe (converted from NPR)
     * Using approximate exchange rate: 1 USD = 133 NPR
     */
    public function getPriceInUsdAttribute(): float
    {
        $exchangeRate = 133; // 1 USD = 133 NPR (approximate)
        return round($this->price / $exchangeRate, 2);
    }

    /**
     * Get price in cents for Stripe (USD * 100)
     */
    public function getStripePriceAttribute(): int
    {
        return (int)($this->price_in_usd * 100);
    }

    /**
     * Get formatted price for display
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'NPR ' . number_format($this->price, 2);
    }

    /**
     * Get formatted USD price for display
     */
    public function getFormattedUsdPriceAttribute(): string
    {
        return '$' . number_format($this->price_in_usd, 2);
    }
}
