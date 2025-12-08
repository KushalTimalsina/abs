# Email System Documentation

## Overview

Your application now has a comprehensive email system with:
- ✅ Email verification on registration
- ✅ Password reset emails (built-in Laravel feature)
- ✅ Welcome emails for new users
- ✅ Subscription confirmation emails

## Email Configuration

### Current Setup (Local Development)

Emails are configured to use the **log driver**, which means all emails are written to:
```
storage/logs/laravel.log
```

### Viewing Emails

To see the emails that are sent:

1. **Windows**: Open `a:\Projects\abs\storage\logs\laravel.log` in a text editor
2. **Search for**: Look for email content in the log file
3. **Format**: Emails are logged with full HTML content

### Production Configuration

For production, update your `.env` file with a real email service:

#### Option 1: Gmail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note**: For Gmail, you need to create an "App Password" in your Google Account settings.

#### Option 2: SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Option 3: Mailgun
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-secret
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Email Features

### 1. Email Verification

**When it's sent**: Automatically when a user registers

**What it does**:
- Sends a verification link to the user's email
- User must click the link to verify their email
- Unverified users are redirected to verification notice page

**Testing**:
1. Register a new account
2. Check `storage/logs/laravel.log` for the verification email
3. Copy the verification URL from the log
4. Paste it in your browser to verify the email

**Routes**:
- Verification notice: `/email/verify`
- Verification handler: `/email/verify/{id}/{hash}`
- Resend verification: `/email/verification-notification`

### 2. Welcome Email

**When it's sent**: After successful registration

**Content**:
- Personalized greeting
- Different content for customers vs. business owners
- Quick start guide
- Link to dashboard

**For Customers**:
- Browse services
- Book appointments
- Manage bookings

**For Business Owners**:
- Add services
- Manage team
- Configure availability
- Accept appointments

### 3. Subscription Confirmation Email

**When it's sent**: When a business owner registers and selects a plan

**Content**:
- Subscription plan details
- Price and duration
- Start and end dates
- Plan features
- Link to dashboard

**Details Included**:
- Plan name
- Price (NPR)
- Duration (days)
- Start date
- End date
- Max team members
- Max services
- Scheduling days

### 4. Password Reset Email

**When it's sent**: When user clicks "Forgot Password"

**What it does**:
- Sends a password reset link
- Link expires after 60 minutes
- User can set a new password

**Testing**:
1. Go to login page
2. Click "Forgot Password"
3. Enter email address
4. Check `storage/logs/laravel.log` for reset email
5. Copy the reset URL
6. Set new password

## Email Templates

All email templates are located in:
```
resources/views/emails/
```

### Available Templates

1. **welcome.blade.php** - Welcome email for new users
2. **subscription-confirmation.blade.php** - Subscription confirmation

### Customizing Email Templates

You can customize the email templates by editing the blade files:

```blade
<!-- Example: resources/views/emails/welcome.blade.php -->
<h1>Welcome to {{ config('app.name') }}!</h1>
<p>Hello {{ $user->name }}!</p>
```

**Variables Available**:
- `$user` - User model (in welcome email)
- `$organization` - Organization model (in subscription email)
- `$subscription` - Subscription model (in subscription email)

## Mail Classes

Mail classes are located in:
```
app/Mail/
```

### Available Mail Classes

1. **WelcomeEmail.php** - Welcome email mailable
2. **SubscriptionConfirmation.php** - Subscription confirmation mailable

### Sending Emails Manually

You can send emails from anywhere in your code:

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

// Send welcome email
Mail::to($user->email)->send(new WelcomeEmail($user));

// Send subscription confirmation
Mail::to($organization->email)->send(
    new SubscriptionConfirmation($organization, $subscription)
);
```

## Testing Emails

### 1. Register a New User

```bash
# Check the log file
tail -f storage/logs/laravel.log
```

Then register a new account and watch the log for emails.

### 2. View Email Content

The log file will contain the full HTML email. Look for sections like:

```
Content-Type: text/html; charset=utf-8

<!DOCTYPE html>
<html>
...email content...
</html>
```

### 3. Test Email Verification

1. Register a new account
2. Find the verification URL in the log
3. Copy and paste it in your browser
4. You should be redirected to the dashboard

## Queue Configuration (Optional)

For better performance in production, you can queue emails:

### 1. Update `.env`

```env
QUEUE_CONNECTION=database
```

### 2. Create Queue Table

```bash
php artisan queue:table
php artisan migrate
```

### 3. Update Mail Classes

Add `implements ShouldQueue` to mail classes:

```php
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    // ...
}
```

### 4. Run Queue Worker

```bash
php artisan queue:work
```

## Troubleshooting

### Emails Not Appearing in Log

1. Check `.env` file:
   ```env
   MAIL_MAILER=log
   ```

2. Clear config cache:
   ```bash
   php artisan config:clear
   ```

3. Check file permissions on `storage/logs/`

### Email Verification Not Working

1. Make sure `User` model implements `MustVerifyEmail`
2. Check that `email_verified_at` is in the `users` table
3. Verify routes are registered in `routes/web.php`

### Production Email Issues

1. Check SMTP credentials
2. Verify firewall allows outbound SMTP
3. Check email service logs
4. Test with a simple email first

## Future Enhancements

Consider adding:
- Subscription expiring reminder emails (7 days before)
- Booking confirmation emails
- Booking reminder emails (24 hours before)
- Payment receipt emails
- Team invitation emails
- Monthly summary emails

## Support

For email-related issues:
1. Check `storage/logs/laravel.log`
2. Verify `.env` configuration
3. Test with `php artisan tinker`:
   ```php
   Mail::raw('Test email', function($msg) {
       $msg->to('test@example.com')->subject('Test');
   });
   ```
