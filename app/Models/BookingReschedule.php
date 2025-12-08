<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingReschedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'requested_by',
        'old_slot_id',
        'new_slot_id',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who requested reschedule
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved/rejected
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the old slot
     */
    public function oldSlot()
    {
        return $this->belongsTo(Slot::class, 'old_slot_id');
    }

    /**
     * Get the new slot
     */
    public function newSlot()
    {
        return $this->belongsTo(Slot::class, 'new_slot_id');
    }

    /**
     * Approve the reschedule request
     */
    public function approve(int $approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the reschedule request
     */
    public function reject(int $approvedBy, string $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
