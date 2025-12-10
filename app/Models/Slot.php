<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'organization_id',
        'service_id',
        'date',
        'start_time',
        'end_time',
        'assigned_staff_id',
        'status',
        'max_bookings',
        'current_bookings',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    /**
     * Get the shift this slot belongs to
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the service this slot is for
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the assigned staff member
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Get the booking for this slot (if any)
     */
    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    /**
     * Get bookings for this slot
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if slot is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && 
               $this->bookings()->whereIn('status', ['pending', 'confirmed'])->count() === 0;
    }

    /**
     * Check if there's a conflict with existing bookings
     */
    public function hasConflict(): bool
    {
        if (!$this->assigned_staff_id) return false;
        
        return Slot::where('assigned_staff_id', $this->assigned_staff_id)
            ->where('date', $this->date)
            ->where('id', '!=', $this->id)
            ->where(function($query) {
                $query->whereBetween('start_time', [$this->start_time, $this->end_time])
                      ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                      ->orWhere(function($q) {
                          $q->where('start_time', '<=', $this->start_time)
                            ->where('end_time', '>=', $this->end_time);
                      });
            })
            ->whereHas('bookings', function($query) {
                $query->whereIn('status', ['pending', 'confirmed']);
            })
            ->exists();
    }

    /**
     * Scope to get available slots
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get slots for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope to get slots for a specific staff member
     */
    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('assigned_staff_id', $staffId);
    }
}
