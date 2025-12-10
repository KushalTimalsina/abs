# Superadmin Notification Access - Quick Guide

## How Superadmin Sends Notifications

### Step 1: Access Notifications

-   Login as superadmin
-   Click "Notifications" in sidebar (bottom of menu)
-   This should take you to `/notifications`

### Step 2: Send Notification

-   Click "Send Notification" button (top right)
-   Fill the form with:
    -   **Title**: e.g., "System Maintenance"
    -   **Message**: e.g., "We will be performing maintenance..."
    -   **Type**: Info/Success/Warning/Error
    -   **Send to**:
        -   All Organizations
        -   Specific Organizations (check boxes)
        -   By Subscription Plan (select plans)

### Step 3: Submit

-   Click "Send Notification"
-   Organization admins will receive it!

## Current Setup

### Routes (Both work for superadmin):

```php
// In superadmin middleware group
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/create', [NotificationController::class, 'create']);
Route::post('/notifications', [NotificationController::class, 'store']);

// Also in auth middleware group (for regular users)
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/create', [NotificationController::class, 'create']);
Route::post('/notifications', [NotificationController::class, 'store']);
```

### Controller Logic:

The `NotificationController` automatically detects:

-   If `Auth::guard('superadmin')->user()` exists → Show organization/plan form
-   If `Auth::user()` exists → Show team member form

## Testing

1. **Login as superadmin** at `/superadmin/login`
2. **Click "Notifications"** in sidebar
3. **Should see**: Your notifications page
4. **Click "Send Notification"**
5. **Should see**: Form with organization/plan options
6. **Fill and submit**
7. **Should redirect to**: `/notifications` with success message

## If Still Redirecting to Normal Dashboard

The issue is likely that the superadmin middleware is not recognizing the session. Try:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Then logout and login again as superadmin.
