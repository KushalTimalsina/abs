<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'duration',
        'price',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the organization that owns this service
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get bookings for this service
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get price in rupees (from paisa)
     */
    public function getPriceInRupeesAttribute(): float
    {
        return $this->price / 100;
    }

    /**
     * Scope to get active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve route binding scoped to organization
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Get the organization from the route
        $organization = request()->route('organization');
        
        // If organization is present, scope the service to it
        if ($organization) {
            return $this->where('organization_id', $organization->id)
                        ->where($field ?? 'id', $value)
                        ->firstOrFail();
        }
        
        // Fallback to default behavior
        return parent::resolveRouteBinding($value, $field);
    }
}
