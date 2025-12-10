# Form Validation, Email Queue & Queue Management Implementation Plan

## Task 1: Form Validation Enhancement

### Forms to Validate:

1. **Authentication Forms**
    - Login (email, password)
    - Registration (name, email, password, phone)
    - Organization Registration (business details)
2. **Organization Forms**
    - Service creation/edit
    - Team member creation/edit
    - Shift creation/edit
    - Slot generation
3. **Booking Forms**
    - Widget booking (name, email, phone)
    - Customer registration
4. **Subscription Forms**
    - Payment submission
    - Payment verification

### Validation Rules to Implement:

-   **Email:** Valid format, unique where needed
-   **Password:** Min 8 chars, uppercase, lowercase, number, special char
-   **Phone:** Valid format (international or local)
-   **Required fields:** Not empty
-   **Dates:** Valid format, logical ranges
-   **Files:** Size limits, allowed types

### Implementation:

1. Create custom validation rules
2. Add FormRequest classes for complex forms
3. Add client-side validation (JavaScript)
4. Display validation errors properly

---

## Task 2: Email Queue Implementation

### Current Email Sending:

-   Subscription confirmation
-   Booking confirmation
-   Password reset
-   Email verification

### Changes Needed:

1. **Configure Queue Driver**

    - Use database queue driver
    - Create jobs table

2. **Convert Emails to Jobs**

    - Create mail jobs for each email type
    - Use `queue()` instead of `send()`

3. **Queue Worker**
    - Set up queue worker
    - Handle failed jobs

### Email Types to Queue:

-   ✅ Subscription confirmation
-   ✅ Booking confirmation
-   ✅ Payment verification
-   ✅ Password reset
-   ✅ Email verification
-   ✅ Booking reminders

---

## Task 3: Queue Management Dashboard (Superadmin)

### Features:

1. **Queue Statistics**

    - Total jobs
    - Pending jobs
    - Processing jobs
    - Failed jobs
    - Completed jobs

2. **Job List View**

    - Job type
    - Status
    - Created at
    - Attempts
    - Payload preview

3. **Actions**

    - Retry failed jobs
    - Delete failed jobs
    - Clear all jobs
    - Pause/Resume queue

4. **Real-time Updates**
    - Auto-refresh stats
    - Job progress

### Implementation:

1. Create queue management controller
2. Create views for queue dashboard
3. Add routes for queue actions
4. Add real-time updates (optional)

---

## Files to Create/Modify:

### New Files:

1. `app/Http/Requests/` - FormRequest classes
2. `app/Jobs/` - Email job classes
3. `app/Http/Controllers/Superadmin/QueueController.php`
4. `resources/views/superadmin/queue/` - Queue dashboard views
5. `database/migrations/*_create_jobs_table.php`
6. `database/migrations/*_create_failed_jobs_table.php`

### Files to Modify:

1. All controller files with email sending
2. Form views (add validation display)
3. `config/queue.php` - Queue configuration
4. `.env` - Queue driver setting
5. Superadmin routes

---

## Execution Order:

### Phase 1: Queue Setup (30 min)

1. Create jobs table migration
2. Configure queue driver
3. Create base email job classes

### Phase 2: Email Queue Migration (45 min)

1. Convert all emails to queued jobs
2. Test email sending
3. Handle failed jobs

### Phase 3: Form Validation (60 min)

1. Create FormRequest classes
2. Add validation to controllers
3. Update views with error display
4. Add client-side validation

### Phase 4: Queue Dashboard (45 min)

1. Create queue controller
2. Create dashboard views
3. Add statistics and job listing
4. Add action buttons

---

## Testing Checklist:

-   [ ] All forms validate correctly
-   [ ] Validation errors display properly
-   [ ] Emails queue successfully
-   [ ] Queue worker processes jobs
-   [ ] Failed jobs are logged
-   [ ] Queue dashboard shows accurate data
-   [ ] Retry failed jobs works
-   [ ] Clear queue works

---

## Total Estimated Time: 3 hours

Ready to proceed? I'll start with Phase 1.
