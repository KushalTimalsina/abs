# Slot Generation Debugging Guide

## Issue: Shift 5 PM - 8 PM not generating slots

### Quick Debug Steps

1. **Check your shift data:**

```php
// In tinker or a test route
$shift = Shift::find(YOUR_SHIFT_ID);
dd([
    'start_time' => $shift->start_time,
    'end_time' => $shift->end_time,
    'start_type' => gettype($shift->start_time),
    'end_type' => gettype($shift->end_time),
]);
```

2. **Check your service:**

```php
$service = Service::find(YOUR_SERVICE_ID);
dd([
    'name' => $service->name,
    'duration' => $service->duration,
]);
```

3. **Test slot generation manually:**

```php
use App\Services\SlotGenerationService;
use Carbon\Carbon;

$slotService = app(SlotGenerationService::class);
$organization = Organization::find(YOUR_ORG_ID);
$service = Service::find(YOUR_SERVICE_ID);
$date = Carbon::today();

$slots = $slotService->generateSlotsForDate($organization, $service, $date);

dd([
    'slots_count' => $slots->count(),
    'slots' => $slots->toArray(),
]);
```

### Common Issues & Fixes

#### Issue 1: Time Format

**Problem:** Shift times stored as "17:00:00" vs "5:00 PM"

**Check:**

```sql
SELECT id, start_time, end_time FROM shifts WHERE id = YOUR_SHIFT_ID;
```

**Should be:** `17:00:00` and `20:00:00` (24-hour format)

#### Issue 2: Day of Week Mismatch

**Problem:** Shift is for Monday (1) but you're generating for Tuesday (2)

**Check:**

```php
$shift = Shift::find(YOUR_SHIFT_ID);
echo "Shift day: " . $shift->day_of_week;
echo "Today: " . Carbon::today()->dayOfWeek;
```

**Fix:** Make sure day_of_week matches (0=Sunday, 1=Monday, etc.)

#### Issue 3: Shift Not Active

**Problem:** `is_active` = 0

**Check:**

```sql
SELECT id, is_active FROM shifts WHERE id = YOUR_SHIFT_ID;
```

**Fix:**

```sql
UPDATE shifts SET is_active = 1 WHERE id = YOUR_SHIFT_ID;
```

#### Issue 4: No Team Member Assigned

**Problem:** `user_id` is NULL

**Check:**

```sql
SELECT id, user_id FROM shifts WHERE id = YOUR_SHIFT_ID;
```

**Fix:** Assign a team member to the shift

### Manual Test

Create a test route in `routes/web.php`:

```php
Route::get('/test-slots/{organization}', function($organizationSlug) {
    $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
    $service = $organization->services()->first();

    if (!$service) {
        return "No services found. Create a service first.";
    }

    $slotService = app(\App\Services\SlotGenerationService::class);
    $today = \Carbon\Carbon::today();

    // Get shifts for today
    $dayOfWeek = $today->dayOfWeek;
    $shifts = $organization->shifts()
        ->where('is_active', true)
        ->where('is_recurring', true)
        ->where('day_of_week', $dayOfWeek)
        ->get();

    if ($shifts->isEmpty()) {
        return "No shifts found for day {$dayOfWeek}. Create a shift first.";
    }

    $allSlots = collect();
    foreach ($shifts as $shift) {
        $slots = $slotService->generateSlotsForShift($organization, $service, $shift, $today);
        $allSlots = $allSlots->merge($slots);
    }

    return [
        'date' => $today->toDateString(),
        'day_of_week' => $dayOfWeek,
        'service' => [
            'name' => $service->name,
            'duration' => $service->duration,
        ],
        'shifts_found' => $shifts->count(),
        'shifts' => $shifts->map(fn($s) => [
            'id' => $s->id,
            'start' => $s->start_time,
            'end' => $s->end_time,
            'user_id' => $s->user_id,
        ]),
        'slots_generated' => $allSlots->count(),
        'slots' => $allSlots->map(fn($s) => [
            'start' => $s->start_time,
            'end' => $s->end_time,
        ]),
    ];
});
```

Visit: `http://localhost:8000/test-slots/your-organization-slug`

### Expected Output for 5 PM - 8 PM (30-min service)

```json
{
    "slots_generated": 6,
    "slots": [
        { "start": "2025-12-10 17:00:00", "end": "2025-12-10 17:30:00" },
        { "start": "2025-12-10 17:30:00", "end": "2025-12-10 18:00:00" },
        { "start": "2025-12-10 18:00:00", "end": "2025-12-10 18:30:00" },
        { "start": "2025-12-10 18:30:00", "end": "2025-12-10 19:00:00" },
        { "start": "2025-12-10 19:00:00", "end": "2025-12-10 19:30:00" },
        { "start": "2025-12-10 19:30:00", "end": "2025-12-10 20:00:00" }
    ]
}
```

### Checklist

-   [ ] Shift exists and is active
-   [ ] Shift has correct day_of_week
-   [ ] Shift has user_id (team member assigned)
-   [ ] Shift times are in 24-hour format (17:00:00, not 5:00 PM)
-   [ ] Service exists and has duration set
-   [ ] You're generating for the correct date/day
-   [ ] Organization has active subscription

### Still Not Working?

Run this in tinker:

```php
php artisan tinker

$org = App\Models\Organization::first();
$service = $org->services()->first();
$shift = $org->shifts()->first();

echo "Shift: {$shift->start_time} - {$shift->end_time}\n";
echo "Service: {$service->name} ({$service->duration} min)\n";
echo "Expected slots: " . ((\Carbon\Carbon::parse($shift->end_time)->diffInMinutes(\Carbon\Carbon::parse($shift->start_time))) / $service->duration) . "\n";

$slotService = app(\App\Services\SlotGenerationService::class);
$slots = $slotService->generateSlotsForShift($org, $service, $shift, \Carbon\Carbon::today());
echo "Generated: " . $slots->count() . " slots\n";
```

This will show you exactly what's happening!
