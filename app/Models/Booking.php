<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'organization_id',
        'service_id',
        'slot_id',
        'customer_id',
        'staff_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'payment_status',
        'notes',
        'customer_notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the slot
     */
    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the assigned staff
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get payment for this booking
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get invoice for this booking
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get reschedule requests for this booking
     */
    public function reschedules()
    {
        return $this->hasMany(BookingReschedule::class);
    }

    /**
     * Check if booking can be rescheduled
     */
    public function canReschedule(): bool
    {
        if (!in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }
        
        // Check if booking is at least 1 day in the future
        $bookingDateTime = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        $oneDayFromNow = Carbon::now()->addDay();
        
        return $bookingDateTime->gt($oneDayFromNow);
    }

    /**
     * Check if booking is upcoming
     */
    public function isUpcoming(): bool
    {
        $bookingDateTime = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        return $bookingDateTime->isFuture() && in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if booking is past
     */
    public function isPast(): bool
    {
        $bookingDateTime = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        return $bookingDateTime->isPast();
    }

    /**
     * Scope to get upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', Carbon::today())
                     ->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Scope to get bookings for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('booking_date', $date);
    }

    /**
     * Scope to get bookings by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
