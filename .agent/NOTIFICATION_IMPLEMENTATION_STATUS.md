# Notification Module - Implementation Status

## ✅ Completed

### Database & Models

-   ✅ Migration created: `custom_notifications` table
-   ✅ Model created: `CustomNotification`
-   ✅ Notification class: `App\Notifications\CustomNotification` (generated)

## 🔄 Next Steps to Complete

### 1. Update Notification Class

**File**: `app/Notifications/CustomNotification.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;
    public $message;
    public $type;
    public $actionUrl;
    public $actionText;

    public function __construct($title, $message, $type = 'info', $actionUrl = null, $actionText = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

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
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
    }
}
```

### 2. Create Superadmin Controller

**Command**: `php artisan make:controller Superadmin/NotificationController`

**Methods needed**:

-   `index()` - List sent notifications
-   `create()` - Show create form
-   `store()` - Send notification
-   `show($id)` - View notification details

### 3. Create Organization Controller

**Command**: `php artisan make:controller OrganizationNotificationController`

**Methods needed**:

-   `create()` - Show create form
-   `store()` - Send notification
-   `sent()` - List sent notifications

### 4. Create Job for Bulk Sending

**Command**: `php artisan make:job SendBulkNotifications`

```php
class SendBulkNotifications implements ShouldQueue
{
    protected $customNotification;

    public function handle()
    {
        // Get recipients based on criteria
        $recipients = $this->getRecipients();

        // Send to each recipient
        foreach ($recipients as $recipient) {
            $recipient->notify(new CustomNotification(
                $this->customNotification->title,
                $this->customNotification->message,
                $this->customNotification->type,
                $this->customNotification->action_url,
                $this->customNotification->action_text
            ));
        }

        // Update sent status
        $this->customNotification->update([
            'sent_at' => now(),
            'recipients_count' => count($recipients),
        ]);
    }
}
```

### 5. Add Routes

**Superadmin Routes** (`routes/web.php`):

```php
Route::prefix('superadmin')->middleware(['auth:superadmin'])->group(function () {
    Route::get('/notifications', [SuperadminNotificationController::class, 'index'])
        ->name('superadmin.notifications.index');
    Route::get('/notifications/create', [SuperadminNotificationController::class, 'create'])
        ->name('superadmin.notifications.create');
    Route::post('/notifications', [SuperadminNotificationController::class, 'store'])
        ->name('superadmin.notifications.store');
});
```

**Organization Routes**:

```php
Route::prefix('organization/{organization}')->middleware(['auth'])->group(function () {
    Route::get('/notifications/create', [OrganizationNotificationController::class, 'create'])
        ->name('organization.notifications.create');
    Route::post('/notifications', [OrganizationNotificationController::class, 'store'])
        ->name('organization.notifications.store');
    Route::get('/notifications/sent', [OrganizationNotificationController::class, 'sent'])
        ->name('organization.notifications.sent');
});
```

### 6. Create Views

**Superadmin Create Form**: `resources/views/superadmin/notifications/create.blade.php`

-   Title input
-   Message textarea
-   Type select (info/warning/success/error)
-   Recipient type radio:
    -   All Organizations
    -   Specific Organizations (multi-select)
    -   By Subscription Plan
-   Action URL & Text (optional)
-   Schedule datetime (optional)
-   Submit button

**Organization Create Form**: `resources/views/organization/notifications/create.blade.php`

-   Title input
-   Message textarea
-   Type select
-   Recipient type radio:
    -   All Team Members
    -   Specific Members (multi-select)
    -   By Role
-   Action URL & Text (optional)
-   Submit button

### 7. Add to Sidebar Menu

**Superadmin Sidebar**:

```blade
<li>
    <a href="{{ route('superadmin.notifications.create') }}">
        Send Notification
    </a>
</li>
```

**Organization Sidebar**:

```blade
@if(isAdmin())
<li>
    <a href="{{ route('organization.notifications.create', $currentOrg) }}">
        Send Notification
    </a>
</li>
@endif
```

## Quick Start Commands

```bash
# Run migration
php artisan migrate

# Create controllers
php artisan make:controller Superadmin/NotificationController
php artisan make:controller OrganizationNotificationController

# Create job
php artisan make:job SendBulkNotifications

# Create views (manually)
# - superadmin/notifications/create.blade.php
# - superadmin/notifications/index.blade.php
# - organization/notifications/create.blade.php
# - organization/notifications/sent.blade.php
```

## Testing Steps

1. Login as superadmin
2. Go to "Send Notification"
3. Fill form and select "All Organizations"
4. Send notification
5. Login as organization admin
6. Check notifications dropdown
7. Test organization sending to team members

## Estimated Remaining Time: 2-3 hours

This will complete the full notification system with bulk sending capabilities.
