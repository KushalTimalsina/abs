<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Shift;
use App\Models\Slot;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotGenerationService
{
    /**
     * Generate slots for an organization based on shifts
     */
    public function generateSlots(
        Organization $organization,
        Service $service,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        // Check subscription limits
        $plan = $organization->getCurrentPlan();
        if (!$plan) {
            throw new \Exception('Organization does not have an active subscription plan');
        }

        // Validate date range against subscription limits
        $daysAhead = now()->diffInDays($endDate);
        if ($daysAhead > $plan->slot_scheduling_days) {
            throw new \Exception("Cannot schedule slots beyond {$plan->slot_scheduling_days} days (subscription limit)");
        }

        $slots = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $daySlots = $this->generateSlotsForDate($organization, $service, $currentDate);
            $slots = $slots->merge($daySlots);
            $currentDate->addDay();
        }

        return $slots;
    }

    /**
     * Generate slots for a specific date
     */
    public function generateSlotsForDate(
        Organization $organization,
        Service $service,
        Carbon $date
    ): Collection {
        $slots = collect();
        $dayOfWeek = $date->dayOfWeek;

        // Get shifts for this day (recurring or specific date)
        $shifts = Shift::where('organization_id', $organization->id)
            ->where(function ($query) use ($dayOfWeek, $date) {
                $query->where(function ($q) use ($dayOfWeek) {
                    $q->where('is_recurring', true)
                      ->where('day_of_week', $dayOfWeek);
                })
                ->orWhere(function ($q) use ($date) {
                    $q->where('is_recurring', false)
                      ->whereDate('specific_date', $date->toDateString());
                });
            })
            ->get();

        foreach ($shifts as $shift) {
            $shiftSlots = $this->generateSlotsForShift($organization, $service, $shift, $date);
            $slots = $slots->merge($shiftSlots);
        }

        return $slots;
    }

    /**
     * Generate slots for a specific shift
     */
    protected function generateSlotsForShift(
        Organization $organization,
        Service $service,
        Shift $shift,
        Carbon $date
    ): Collection {
        $slots = collect();
        
        $shiftStart = Carbon::parse($date->toDateString() . ' ' . $shift->start_time);
        $shiftEnd = Carbon::parse($date->toDateString() . ' ' . $shift->end_time);
        
        $currentTime = $shiftStart->copy();
        $slotDuration = $service->duration; // in minutes

        while ($currentTime->copy()->addMinutes($slotDuration)->lte($shiftEnd)) {
            $slotStart = $currentTime->copy();
            $slotEnd = $currentTime->copy()->addMinutes($slotDuration);

            // Check if slot already exists
            $existingSlot = Slot::where('organization_id', $organization->id)
                ->where('service_id', $service->id)
                ->where('assigned_staff_id', $shift->user_id)
                ->where('start_time', $slotStart)
                ->where('end_time', $slotEnd)
                ->first();

            if (!$existingSlot) {
                // Create new slot
                $slot = Slot::create([
                    'organization_id' => $organization->id,
                    'service_id' => $service->id,
                    'assigned_staff_id' => $shift->user_id,
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'status' => 'available',
                    'max_bookings' => 1,
                    'current_bookings' => 0,
                ]);

                $slots->push($slot);
            } else {
                $slots->push($existingSlot);
            }

            $currentTime->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * Check if a slot has conflicts
     */
    public function hasConflict(Slot $slot, ?int $excludeBookingId = null): bool
    {
        // Check if slot is already fully booked
        if ($slot->current_bookings >= $slot->max_bookings) {
            return true;
        }

        // Check for overlapping bookings for the same staff
        $conflictingBookings = \App\Models\Booking::where('organization_id', $slot->organization_id)
            ->where('staff_id', $slot->assigned_staff_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($slot) {
                $query->whereBetween('start_time', [$slot->start_time, $slot->end_time])
                    ->orWhereBetween('end_time', [$slot->start_time, $slot->end_time])
                    ->orWhere(function ($q) use ($slot) {
                        $q->where('start_time', '<=', $slot->start_time)
                          ->where('end_time', '>=', $slot->end_time);
                    });
            });

        if ($excludeBookingId) {
            $conflictingBookings->where('id', '!=', $excludeBookingId);
        }

        return $conflictingBookings->exists();
    }

    /**
     * Get available slots for a service within a date range
     */
    public function getAvailableSlots(
        Organization $organization,
        Service $service,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return Slot::where('organization_id', $organization->id)
            ->where('service_id', $service->id)
            ->where('status', 'available')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->where('current_bookings', '<', \DB::raw('max_bookings'))
            ->with('assignedStaff')
            ->orderBy('start_time')
            ->get()
            ->filter(function ($slot) {
                return !$this->hasConflict($slot);
            });
    }

    /**
     * Block a slot
     */
    public function blockSlot(Slot $slot, string $reason = null): void
    {
        $slot->update([
            'status' => 'blocked',
            'settings' => array_merge($slot->settings ?? [], [
                'blocked_reason' => $reason,
                'blocked_at' => now(),
            ]),
        ]);
    }

    /**
     * Unblock a slot
     */
    public function unblockSlot(Slot $slot): void
    {
        $slot->update(['status' => 'available']);
    }
}
