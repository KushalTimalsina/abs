<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'day_of_week',
        'specific_date',
        'start_time',
        'end_time',
        'slot_duration',
        'max_concurrent_bookings',
        'is_recurring',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
        'specific_date' => 'date',
    ];

    /**
     * Get the organization that owns this shift
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user (team member) assigned to this shift
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get slots generated from this shift
     */
    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    /**
     * Generate slots for a specific date
     */
    public function generateSlots(\DateTime $date): array
    {
        $slots = [];
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);
        
        $currentTime = $startTime->copy();
        
        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($this->slot_duration);
            
            if ($slotEnd->lte($endTime)) {
                $slots[] = [
                    'shift_id' => $this->id,
                    'organization_id' => $this->organization_id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $currentTime->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'status' => 'available',
                ];
            }
            
            $currentTime->addMinutes($this->slot_duration);
        }
        
        return $slots;
    }

    /**
     * Scope to get active shifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get shifts for a specific day
     */
    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }
}
