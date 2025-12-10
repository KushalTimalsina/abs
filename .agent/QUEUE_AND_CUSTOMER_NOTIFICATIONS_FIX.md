# Email Invoice Queue & Customer Notifications - Fix Guide

## Issue 1: Email Invoice Taking Time (Not Using Queue)

### Problem

Queue is set to `sync` which runs immediately instead of in background.

### Solution

**Step 1: Update .env file**
Change line 21 in `.env`:

```
FROM: QUEUE_CONNECTION=sync
TO:   QUEUE_CONNECTION=database
```

**Step 2: Create jobs table**
Run this command:

```bash
php artisan queue:table
php artisan migrate
```

**Step 3: Start queue worker**
Run this command (keep it running):

```bash
php artisan queue:work
```

**Alternative: Use queue:listen for development**

```bash
php artisan queue:listen
```

Now emails will be sent in the background! ✅

---

## Issue 2: Organizations Can Send Notifications to Customers

### Current Situation

-   Organizations can only send to team members
-   Need to add customer notification feature

### Implementation Plan

1. **Update NotificationController create() method**

    - Add "customers" option to recipient types
    - Fetch organization's customers (from bookings)

2. **Update create.blade.php form**

    - Add "All Customers" option
    - Add "Specific Customers" option with customer list

3. **Update store() method**
    - Handle customer recipients
    - Send notifications to selected customers

### Files to Modify

1. `app/Http/Controllers/NotificationController.php`
2. `resources/views/notifications/create.blade.php`

Would you like me to implement the customer notifications feature now?
