<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
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
        'google_id',
        'email_verified_at',
        'user_type',
        'phone',
        'avatar',
        'settings',
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
        'password' => 'hashed',
        'settings' => 'array',
    ];

    /**
     * Get organizations this user belongs to
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->withPivot('role', 'permissions', 'status', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get bookings made by this user (as customer)
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    /**
     * Get bookings assigned to this user (as staff)
     */
    public function assignedBookings()
    {
        return $this->hasMany(Booking::class, 'staff_id');
    }

    /**
     * Get slots assigned to this user
     */
    public function assignedSlots()
    {
        return $this->hasMany(Slot::class, 'assigned_staff_id');
    }

    /**
     * Check if user has a specific role in an organization
     */
    public function hasRole(int $organizationId, string $role): bool
    {
        return $this->organizations()
            ->wherePivot('organization_id', $organizationId)
            ->wherePivot('role', $role)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Check if user has permission in an organization
     */
    public function hasPermission(int $organizationId, string $permission): bool
    {
        $org = $this->organizations()
            ->wherePivot('organization_id', $organizationId)
            ->wherePivot('status', 'active')
            ->first();
        
        if (!$org) return false;
        
        $permissions = $org->pivot->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get all organizations for this user
     */
    public function getOrganizations()
    {
        return $this->organizations()->wherePivot('status', 'active')->get();
    }

    /**
     * Check if user is admin type
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if user is team member type
     */
    public function isTeamMember(): bool
    {
        return $this->user_type === 'team_member';
    }

    /**
     * Check if user is frontdesk type
     */
    public function isFrontdesk(): bool
    {
        return $this->user_type === 'frontdesk';
    }

    /**
     * Check if user is customer type
     */
    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    /**
     * Scope to filter admin users
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    /**
     * Scope to filter team members
     */
    public function scopeTeamMembers($query)
    {
        return $query->where('user_type', 'team_member');
    }

    /**
     * Scope to filter frontdesk users
     */
    public function scopeFrontdesk($query)
    {
        return $query->where('user_type', 'frontdesk');
    }

    /**
     * Scope to filter customers
     */
    public function scopeCustomers($query)
    {
        return $query->where('user_type', 'customer');
    }
}

