<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'settings',
        'status',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the active subscription for this organization
     */
    public function subscription()
    {
        return $this->hasOne(OrganizationSubscription::class)->where('is_active', true)->latest();
    }

    /**
     * Get all subscriptions (including expired)
     */
    public function subscriptions()
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    /**
     * Get users (team members) of this organization
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot('role', 'permissions', 'status', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get services offered by this organization
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get shifts configured for this organization
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Get team invitations for this organization
     */
    public function teamInvitations()
    {
        return $this->hasMany(\App\Models\TeamInvitation::class);
    }

    /**
     * Get slots for this organization
     */
    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    /**
     * Get bookings for this organization
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get payment gateways configured for this organization
     */
    public function paymentGateways()
    {
        return $this->hasMany(PaymentGateway::class);
    }

    /**
     * Get subscription payments for this organization
     */
    public function subscriptionPayments()
    {
        return $this->hasMany(\App\Models\SubscriptionPayment::class);
    }

    /**
     * Get booking payments for this organization
     */
    public function payments()
    {
        return $this->hasManyThrough(
            \App\Models\Payment::class,
            \App\Models\Booking::class,
            'organization_id', // Foreign key on bookings table
            'booking_id',      // Foreign key on payments table
            'id',              // Local key on organizations table
            'id'               // Local key on bookings table
        );
    }

    /**
     * Get widget settings for this organization
     */
    public function widgetSettings()
    {
        return $this->hasOne(WidgetSettings::class);
    }

    /**
     * Check if organization has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription()->exists();
    }

    /**
     * Get the current subscription plan
     */
    public function getCurrentPlan()
    {
        return $this->subscription?->plan;
    }

    /**
     * Check if can add more team members
     */
    public function canAddTeamMember(): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) return false;
        
        $currentCount = $this->users()->wherePivot('status', 'active')->count();
        return $currentCount < $plan->max_team_members;
    }

    /**
     * Check if can schedule slot for given date
     */
    public function canScheduleSlot(\DateTime $date): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) return false;
        
        $daysAhead = (new \DateTime())->diff($date)->days;
        return $daysAhead <= $plan->slot_scheduling_days;
    }

    /**
     * Get available payment methods based on subscription
     */
    public function getAvailablePaymentMethods(): array
    {
        $plan = $this->getCurrentPlan();
        
        if (!$plan) {
            return ['cash'];
        }
        
        // Ensure payment_methods is an array
        $methods = $plan->payment_methods;
        
        if (is_null($methods)) {
            return ['cash'];
        }
        
        if (is_string($methods)) {
            return json_decode($methods, true) ?? ['cash'];
        }
        
        return is_array($methods) ? $methods : ['cash'];
    }

    /**
     * Get active payment gateways
     */
    public function getActivePaymentGateways()
    {
        return $this->paymentGateways()->where('is_active', true)->get();
    }

    /**
     * Check if organization can accept online payments
     */
    public function canAcceptOnlinePayments(): bool
    {
        $plan = $this->getCurrentPlan();
        
        if (!$plan) {
            return false;
        }
        
        return $plan->online_payment_enabled ?? false;
    }
}
