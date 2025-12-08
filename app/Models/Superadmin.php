<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Superadmin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Get all organizations
     */
    public function getAllOrganizations()
    {
        return Organization::with(['subscription.plan', 'users'])
            ->withCount('services', 'bookings')
            ->latest()
            ->get();
    }

    /**
     * Get organization statistics
     */
    public function getOrganizationStats()
    {
        return [
            'total' => Organization::count(),
            'active' => Organization::where('is_active', true)->count(),
            'suspended' => Organization::where('is_active', false)->count(),
            'with_active_subscription' => Organization::whereHas('subscription', function($q) {
                $q->where('is_active', true);
            })->count(),
        ];
    }

    /**
     * Get subscription plan statistics
     */
    public function getSubscriptionStats()
    {
        return [
            'total_plans' => SubscriptionPlan::count(),
            'active_plans' => SubscriptionPlan::where('is_active', true)->count(),
            'total_subscriptions' => OrganizationSubscription::count(),
            'active_subscriptions' => OrganizationSubscription::where('is_active', true)->count(),
        ];
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        $payments = SubscriptionPayment::all();
        
        return [
            'total_payments' => $payments->count(),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'verified_payments' => $payments->where('status', 'verified')->count(),
            'total_revenue' => $payments->where('status', 'verified')->sum('amount'),
        ];
    }

    /**
     * Get system overview
     */
    public function getSystemOverview()
    {
        return [
            'organizations' => $this->getOrganizationStats(),
            'subscriptions' => $this->getSubscriptionStats(),
            'payments' => $this->getPaymentStats(),
            'total_users' => User::count(),
            'total_bookings' => Booking::count(),
            'total_services' => Service::count(),
        ];
    }

    /**
     * Suspend an organization
     */
    public function suspendOrganization(Organization $organization, string $reason = null)
    {
        $organization->update([
            'is_active' => false,
            'suspension_reason' => $reason,
        ]);

        // Optionally notify organization admin
        // $organization->owner->notify(new OrganizationSuspended($reason));
    }

    /**
     * Activate an organization
     */
    public function activateOrganization(Organization $organization)
    {
        $organization->update([
            'is_active' => true,
            'suspension_reason' => null,
        ]);

        // Optionally notify organization admin
        // $organization->owner->notify(new OrganizationActivated());
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
