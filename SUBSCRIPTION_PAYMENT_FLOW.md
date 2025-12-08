# Subscription Payment Flow Documentation

## Overview

The subscription payment system has been updated to require payment verification before activating business accounts. This ensures proper payment collection and subscription management.

## Registration Flow

### For Customers
1. Register as customer
2. Account activated immediately
3. Can browse and book services

### For Business Owners
1. Register as business owner
2. Select subscription plan
3. **Organization created as INACTIVE**
4. **Pending payment record created**
5. Redirected to payment submission page
6. Submit payment proof
7. Wait for admin verification
8. Account activated after verification

## Payment Submission Process

### Step 1: Payment Page

After registration, business owners are redirected to: `/subscription/payment`

**What they see:**
- Subscription details (plan, amount, duration)
- Payment form with options:
  - Payment method (Cash, eSewa, Khalti, Bank Transfer)
  - Transaction ID (optional)
  - Payment proof upload (screenshot/receipt)
  - Additional notes

### Step 2: Submit Payment

**Payment Methods:**
- **Cash**: Visit office to make payment
- **eSewa**: Send payment to merchant ID, upload screenshot
- **Khalti**: Send payment to merchant ID, upload screenshot
- **Bank Transfer**: Transfer to bank account, upload receipt

**What happens:**
- Payment record updated with details
- Status remains "pending"
- User redirected to dashboard
- Success message shown

### Step 3: Admin Verification

**Superadmin Dashboard:**
1. Go to Subscription Payments
2. View pending payments
3. Review payment proof
4. Verify or reject payment

**When Verified:**
- ✅ Payment status → "verified"
- ✅ Organization status → "active"
- ✅ Subscription created/activated
- ✅ Confirmation email sent
- ✅ Business can start using the system

## Database Changes

### Organizations Table
- **Field**: `status` (enum: 'active', 'inactive', 'suspended')
- **New registrations**: Set to 'inactive'
- **After payment**: Set to 'active'

### Subscription Payments Table
Fields used:
- `organization_id` - Which organization
- `subscription_plan_id` - Which plan
- `amount` - Payment amount (NPR)
- `payment_method` - How they paid
- `transaction_id` - Transaction reference
- `payment_proof` - Uploaded file path
- `status` - pending/verified/rejected
- `start_date` - Subscription start
- `end_date` - Subscription end
- `verified_at` - When verified
- `verified_by` - Superadmin who verified
- `admin_notes` - Admin comments

### Organization Subscriptions Table
- Created when payment is verified
- `is_active` - Boolean (true when active)
- `start_date` - When subscription starts
- `end_date` - When subscription expires

## Routes

### User Routes (Authenticated)
```php
GET  /subscription/payment       - Show payment page
POST /subscription/payment       - Submit payment
GET  /subscription/payment/skip  - Skip payment (testing)
```

### Superadmin Routes
```php
GET  /superadmin/subscriptions/payments           - List all payments
GET  /superadmin/subscriptions/payments/{id}      - View payment details
POST /superadmin/subscriptions/payments/{id}/verify - Verify payment
POST /superadmin/subscriptions/payments/{id}/reject - Reject payment
```

## Controllers

### SubscriptionPaymentSubmissionController
**Purpose**: Handle payment submission by users

**Methods:**
- `show()` - Display payment form
- `submit()` - Process payment submission
- `skip()` - Skip payment (for testing)

### SubscriptionPaymentController (Superadmin)
**Purpose**: Handle payment verification by superadmin

**Methods:**
- `index()` - List all payments
- `show()` - View payment details
- `verify()` - Verify and activate subscription
- `reject()` - Reject payment

## Email Notifications

### Welcome Email
- **When**: After registration
- **To**: User email
- **Content**: Welcome message, next steps

### Subscription Confirmation Email
- **When**: After payment verification
- **To**: Organization email
- **Content**: 
  - Subscription details
  - Plan features
  - Start/end dates
  - Dashboard link

## Testing the Flow

### 1. Register New Business

```bash
# Go to registration page
http://localhost:8000/register

# Fill in details:
- Name: Test Business
- Email: test@business.com
- Password: password
- User Type: Business Owner
- Organization: Test Salon
- Plan: Select any plan
```

### 2. Payment Submission

After registration, you'll be redirected to:
```
http://localhost:8000/subscription/payment
```

**Submit payment:**
- Payment Method: Cash (or any)
- Transaction ID: TEST123 (optional)
- Upload screenshot (optional)
- Notes: Test payment
- Click "Submit Payment"

### 3. Admin Verification

**Login as Superadmin:**
```
http://localhost:8000/superadmin/login
Email: superadmin@abs.local
Password: password
```

**Verify Payment:**
1. Go to Subscription Payments
2. Find the pending payment
3. Click "View"
4. Click "Verify Payment"
5. Set start date and duration
6. Add notes (optional)
7. Click "Verify"

### 4. Check Results

**Organization:**
- Status should be "active"
- Can access dashboard
- Can add services

**Subscription:**
- Created in database
- is_active = true
- Has start and end dates

**Email:**
- Check `storage/logs/laravel.log`
- Look for subscription confirmation email

## Skip Payment (Testing Only)

For testing purposes, you can skip payment:

```
http://localhost:8000/subscription/payment/skip
```

This will:
- Clear pending payment from session
- Redirect to dashboard
- Organization remains inactive
- No subscription created

## File Uploads

Payment proof files are stored in:
```
storage/app/public/payment-proofs/
```

**To access uploaded files:**
1. Create symbolic link (if not exists):
   ```bash
   php artisan storage:link
   ```

2. Access via URL:
   ```
   http://localhost:8000/storage/payment-proofs/filename.jpg
   ```

## Troubleshooting

### Payment Page Not Showing

**Check:**
1. Is there a pending_payment_id in session?
2. Does the payment record exist?
3. Is the user authenticated?

**Fix:**
```php
// In tinker
session(['pending_payment_id' => 1]);
```

### Organization Still Inactive

**Check:**
1. Has payment been verified?
2. Check organization status in database

**Fix:**
```php
// In tinker
$org = Organization::find(1);
$org->update(['status' => 'active']);
```

### Email Not Sent

**Check:**
1. MAIL_MAILER=log in .env
2. Check storage/logs/laravel.log
3. Verify email addresses exist

**Fix:**
```bash
php artisan config:clear
php artisan cache:clear
```

## Security Considerations

### Authorization
- Only organization owner can submit payment
- Only superadmin can verify payments
- Payment proof files are private

### Validation
- Payment amount must match plan price
- Transaction ID format validation
- File upload size limit (2MB)
- Image files only for proof

### Data Integrity
- Organizations inactive until paid
- Subscriptions only created after verification
- Payment records immutable after verification

## Future Enhancements

Consider adding:
- [ ] Automated payment gateway integration (eSewa, Khalti)
- [ ] Payment reminder emails
- [ ] Subscription renewal notifications
- [ ] Invoice generation
- [ ] Payment history for organizations
- [ ] Refund management
- [ ] Promo codes/discounts
- [ ] Free trial period
- [ ] Subscription upgrades/downgrades
