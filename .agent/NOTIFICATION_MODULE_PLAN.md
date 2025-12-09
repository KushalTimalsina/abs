# Notification Module Implementation Plan

## Overview

A comprehensive notification system allowing:

-   **Superadmin**: Send notifications to organizations (bulk/individual/subscription-based)
-   **Organizations**: Send notifications to team members (bulk/individual)

## Database Schema

### 1. Notifications Table (Already exists - extend it)

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type'); // App\Notifications\CustomNotification
    $table->morphs('notifiable'); // user_id, user_type
    $table->text('data'); // JSON data
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
```

### 2. Custom Notifications Table (New)

```php
Schema::create('custom_notifications', function (Blueprint $table) {
    $table->id();
    $table->string('sender_type'); // superadmin, organization
    $table->unsignedBigInteger('sender_id')->nullable();
    $table->string('recipient_type'); // organization, user, subscription_plan
    $table->json('recipient_ids')->nullable(); // For bulk/individual
    $table->string('title');
    $table->text('message');
    $table->string('type')->default('info'); // info, warning, success, error
    $table->string('action_url')->nullable();
    $table->string('action_text')->nullable();
    $table->timestamp('scheduled_at')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->unsignedInteger('recipients_count')->default(0);
    $table->unsignedInteger('read_count')->default(0);
    $table->timestamps();
});
```

## Features to Implement

### Superadmin Features

1. **Send to All Organizations**
    - Notification sent to all organization admins
2. **Send to Specific Organizations**
    - Select multiple organizations
    - Preview recipient count
3. **Send by Subscription Plan**
    - Filter by plan (Free, Basic, Pro, etc.)
    - Send to all organizations on selected plan(s)
4. **Schedule Notifications**
    - Set future date/time for sending
    - Queue system for processing

### Organization Features

1. **Send to All Team Members**
    - Notification to all active team members
2. **Send to Specific Members**
    - Select individual team members
    - Multi-select interface
3. **Send by Role**
    - Filter by role (admin, team_member, frontdesk)
4. **Notification Templates**
    - Pre-defined templates for common messages
    - Custom message option

## UI Components

### Superadmin Notification Center

**Route**: `/superadmin/notifications/create`

**Form Fields**:

-   Title (required)
-   Message (required, rich text)
-   Type (info/warning/success/error)
-   Recipient Type (radio):
    -   All Organizations
    -   Specific Organizations (multi-select)
    -   By Subscription Plan (multi-select)
-   Action Button (optional):
    -   Button Text
    -   Button URL
-   Schedule (optional):
    -   Send Now / Schedule for Later
    -   Date & Time picker

### Organization Notification Center

**Route**: `/organization/{org}/notifications/create`

**Form Fields**:

-   Title (required)
-   Message (required, rich text)
-   Type (info/warning/success/error)
-   Recipient Type (radio):
    -   All Team Members
    -   Specific Members (multi-select)
    -   By Role (multi-select)
-   Action Button (optional)
-   Schedule (optional)

## Implementation Steps

### Phase 1: Database & Models (30 mins)

1. Create migration for `custom_notifications` table
2. Create `CustomNotification` model
3. Create notification class `App\Notifications\CustomNotification`

### Phase 2: Superadmin Notification (1 hour)

1. Create `SuperadminNotificationController`
2. Create views:
    - `superadmin/notifications/create.blade.php`
    - `superadmin/notifications/index.blade.php`
3. Add routes
4. Implement sending logic with queues

### Phase 3: Organization Notification (1 hour)

1. Create `OrganizationNotificationController`
2. Create views:
    - `organization/notifications/create.blade.php`
    - `organization/notifications/index.blade.php`
3. Add routes
4. Implement sending logic

### Phase 4: Notification Display (45 mins)

1. Update notification dropdown in navbar
2. Create notification list page
3. Mark as read functionality
4. Real-time updates (optional - using Pusher/Echo)

### Phase 5: Jobs & Queues (30 mins)

1. Create `SendBulkNotifications` job
2. Create `SendScheduledNotifications` command
3. Add to scheduler

## Routes

### Superadmin Routes

```php
Route::prefix('superadmin')->middleware(['auth:superadmin'])->group(function () {
    Route::get('/notifications', [SuperadminNotificationController::class, 'index'])->name('superadmin.notifications.index');
    Route::get('/notifications/create', [SuperadminNotificationController::class, 'create'])->name('superadmin.notifications.create');
    Route::post('/notifications', [SuperadminNotificationController::class, 'store'])->name('superadmin.notifications.store');
    Route::get('/notifications/{notification}', [SuperadminNotificationController::class, 'show'])->name('superadmin.notifications.show');
});
```

### Organization Routes

```php
Route::prefix('organization/{organization}')->middleware(['auth'])->group(function () {
    Route::get('/notifications/create', [OrganizationNotificationController::class, 'create'])->name('organization.notifications.create');
    Route::post('/notifications', [OrganizationNotificationController::class, 'store'])->name('organization.notifications.store');
    Route::get('/notifications/sent', [OrganizationNotificationController::class, 'sent'])->name('organization.notifications.sent');
});
```

## Jobs

### SendBulkNotifications Job

```php
class SendBulkNotifications implements ShouldQueue
{
    public function handle()
    {
        // Get recipients based on criteria
        // Send notification to each recipient
        // Update sent_at and recipients_count
    }
}
```

### SendScheduledNotifications Command

```php
class SendScheduledNotifications extends Command
{
    // Run every minute
    // Check for notifications where scheduled_at <= now() and sent_at is null
    // Dispatch SendBulkNotifications job
}
```

## Notification Types

### CustomNotification Class

```php
class CustomNotification extends Notification implements ShouldQueue
{
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
        ];
    }
}
```

## UI Design

### Notification Card

```blade
<div class="bg-{{ $type }}-50 border-l-4 border-{{ $type }}-400 p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <!-- Icon based on type -->
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-{{ $type }}-800">
                {{ $title }}
            </p>
            <p class="mt-1 text-sm text-{{ $type }}-700">
                {{ $message }}
            </p>
            @if($action_url)
            <div class="mt-2">
                <a href="{{ $action_url }}" class="btn btn-{{ $type }}">
                    {{ $action_text }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
```

## Testing Checklist

-   [ ] Superadmin can send to all organizations
-   [ ] Superadmin can send to specific organizations
-   [ ] Superadmin can send by subscription plan
-   [ ] Organization can send to all team members
-   [ ] Organization can send to specific members
-   [ ] Organization can send by role
-   [ ] Scheduled notifications are sent at correct time
-   [ ] Notification count updates correctly
-   [ ] Mark as read works
-   [ ] Bulk operations are queued
-   [ ] Email notifications work (optional)

## Priority: MEDIUM-HIGH

This is a valuable feature for user engagement and communication.

## Estimated Time: 4-5 hours
