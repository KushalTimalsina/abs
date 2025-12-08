# Email Notification System - Usage Guide

## Overview
The email notification system has been successfully implemented with the following features:
- **6 Notification Types**: Booking confirmed, cancelled, rescheduled, reminder, payment received, and staff assigned
- **Dual Channels**: Email (via mail driver) and Database (for in-app notifications)
- **Log Driver**: Configured for local testing - emails written to `storage/logs/laravel.log`
- **Responsive Templates**: Modern, mobile-friendly email designs using Laravel's markdown components

## Notification Classes

All notifications are located in `app/Notifications/`:
- `BookingConfirmed.php` - Sent when a booking is confirmed
- `BookingCancelled.php` - Sent when a booking is cancelled
- `BookingRescheduled.php` - Sent when a booking is rescheduled
- `BookingReminder.php` - Sent 24 hours before appointment
- `PaymentReceived.php` - Sent when payment is processed
- `StaffAssigned.php` - Sent when staff is assigned to a booking

## Usage Examples

### 1. Send Booking Confirmation
```php
use App\Notifications\BookingConfirmed;

// Send to customer
$booking->customer->notify(new BookingConfirmed($booking, 'customer'));

// Send to staff
if ($booking->staff) {
    $booking->staff->notify(new BookingConfirmed($booking, 'staff'));
}
```

### 2. Send Cancellation Notice
```php
use App\Notifications\BookingCancelled;

$reason = "Customer requested cancellation";

// Notify customer
$booking->customer->notify(new BookingCancelled($booking, $reason, 'customer'));

// Notify staff
if ($booking->staff) {
    $booking->staff->notify(new BookingCancelled($booking, $reason, 'staff'));
}
```

### 3. Send Reschedule Notification
```php
use App\Notifications\BookingRescheduled;

$oldDate = $booking->booking_date->format('Y-m-d');
$oldTime = $booking->start_time;

// Update booking details...
$booking->booking_date = $newDate;
$booking->start_time = $newTime;
$booking->save();

// Notify customer
$booking->customer->notify(new BookingRescheduled($booking, $oldDate, $oldTime, 'customer'));

// Notify staff
if ($booking->staff) {
    $booking->staff->notify(new BookingRescheduled($booking, $oldDate, $oldTime, 'staff'));
}
```

### 4. Send Payment Confirmation
```php
use App\Notifications\PaymentReceived;

// After payment is processed
$payment->booking->customer->notify(new PaymentReceived($payment));
```

### 5. Notify Staff of Assignment
```php
use App\Notifications\StaffAssigned;

// When assigning staff to a booking
$booking->staff_id = $staffMember->id;
$booking->save();

$staffMember->notify(new StaffAssigned($booking));
```

## Automated Reminders

A console command has been created to send booking reminders:

```bash
# Manually run the reminder command
php artisan bookings:send-reminders
```

The command is scheduled to run **daily at 9:00 AM** in `app/Console/Kernel.php`.

To enable scheduled tasks, run the Laravel scheduler:
```bash
# For local development (keeps running)
php artisan schedule:work

# For production (add to cron)
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Database Notifications (In-App)

All notifications are also stored in the `notifications` table for in-app display.

### Retrieve Unread Notifications
```php
// Get unread notifications for a user
$notifications = auth()->user()->unreadNotifications;

// In a controller
public function getNotifications()
{
    return auth()->user()->unreadNotifications()
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
}
```

### Mark as Read
```php
// Mark single notification as read
$notification = auth()->user()->notifications()->find($id);
$notification->markAsRead();

// Mark all as read
auth()->user()->unreadNotifications->markAsRead();
```

### Display in Blade
```blade
@foreach(auth()->user()->unreadNotifications as $notification)
    <div class="notification">
        <p>{{ $notification->data['message'] }}</p>
        <small>{{ $notification->created_at->diffForHumans() }}</small>
    </div>
@endforeach
```

## Testing Notifications

### View Email in Logs
When using the log driver, emails are written to `storage/logs/laravel.log`. Look for entries like:

```
[2024-12-08 14:15:00] local.DEBUG: Booking Confirmed
Subject: Booking Confirmed - BK-ABC123
To: customer@example.com
```

### Test in Tinker
```bash
php artisan tinker
```

```php
// Create a test booking
$booking = \App\Models\Booking::with(['customer', 'service', 'organization'])->first();

// Send notification
$booking->customer->notify(new \App\Notifications\BookingConfirmed($booking));

// Check database notifications
\App\Models\User::first()->notifications;
```

## Switching to SMTP

When ready to send real emails:

1. **Update `.env` file:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Your App Name"
```

2. **Clear config cache:**
```bash
php artisan config:clear
```

3. **Test:**
```bash
php artisan tinker
# Send a test notification
```

## Email Service Providers

### Gmail
- Enable 2FA
- Generate App Password: https://myaccount.google.com/apppasswords
- Use app password in `MAIL_PASSWORD`

### SendGrid
- Sign up at sendgrid.com
- Create API key
- Use `apikey` as `MAIL_USERNAME`
- Use API key as `MAIL_PASSWORD`

### Mailgun
- Sign up at mailgun.com
- Get domain and secret from dashboard
- Update `.env` with Mailgun credentials

## Queue Configuration (Recommended for Production)

Notifications implement `ShouldQueue`, so they can be queued for better performance:

1. **Configure queue driver in `.env`:**
```env
QUEUE_CONNECTION=database
```

2. **Create jobs table:**
```bash
php artisan queue:table
php artisan migrate
```

3. **Run queue worker:**
```bash
php artisan queue:work
```

## Integration Points

Add notification triggers at these points in your application:

1. **After booking creation** → `BookingConfirmed`
2. **After booking cancellation** → `BookingCancelled`
3. **After booking reschedule** → `BookingRescheduled`
4. **After payment success** → `PaymentReceived`
5. **After staff assignment** → `StaffAssigned`
6. **Daily at 9 AM** → `BookingReminder` (automated)

## Customization

### Modify Email Templates
Email templates are in `resources/views/emails/`:
- `booking-confirmed.blade.php`
- `booking-cancelled.blade.php`
- `booking-rescheduled.blade.php`
- `booking-reminder.blade.php`
- `payment-received.blade.php`
- `staff-assigned.blade.php`

### Customize Notification Data
Edit the `toArray()` method in notification classes to change what's stored in the database.

### Change Notification Channels
Edit the `via()` method to add/remove channels (e.g., SMS, Slack, etc.).
