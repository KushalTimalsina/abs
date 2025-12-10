# Algorithms Used in Appointment Booking System

## Total Algorithms: 15+

---

## 1. **Slot Generation Algorithm**

**Location:** `app/Services/SlotGenerationService.php`

**Type:** Time-based Scheduling Algorithm

**Purpose:** Automatically generates available time slots based on shifts

**How it works:**

1. Takes shift start/end time and slot duration
2. Divides the time range into equal intervals
3. Creates slot records with status tracking
4. Handles conflicts and overlaps

**Complexity:** O(n) where n = number of slots to generate

**Example:**

```
Shift: 9:00 AM - 5:00 PM
Duration: 30 minutes
Output: 16 slots (9:00, 9:30, 10:00, ..., 4:30)
```

---

## 2. **Conflict Detection Algorithm**

**Location:** Booking validation logic

**Type:** Interval Overlap Detection

**Purpose:** Prevents double-booking of slots

**How it works:**

1. Checks if requested time slot is already booked
2. Validates staff availability
3. Ensures no overlapping appointments

**Complexity:** O(1) with database indexing

**Logic:**

```
If (slot.status == 'booked' OR slot.booking_id != null)
    → Conflict detected
```

---

## 3. **Subscription Validation Algorithm**

**Location:** `app/Models/Organization.php` → `hasActiveSubscription()`

**Type:** Date Range Validation

**Purpose:** Checks if organization's subscription is active

**How it works:**

1. Retrieves subscription record
2. Checks `is_active` flag
3. Validates `start_date` and `end_date`
4. Compares with current date

**Complexity:** O(1)

**Logic:**

```
Active = (is_active == true) AND
         (current_date >= start_date) AND
         (current_date <= end_date)
```

---

## 4. **Role-Based Access Control (RBAC) Algorithm**

**Location:** Policies, Middleware, Controllers

**Type:** Permission Checking Algorithm

**Purpose:** Determines user access rights

**How it works:**

1. Checks user's role (superadmin, admin, staff, customer)
2. Validates organization membership
3. Verifies specific permissions
4. Grants or denies access

**Complexity:** O(1) with proper indexing

**Hierarchy:**

```
Superadmin → Full access
Admin → Organization-level access
Staff → Limited organization access
Customer → Own data only
```

---

## 5. **Payment Gateway Routing Algorithm**

**Location:** Payment processing logic

**Type:** Strategy Pattern / Router Algorithm

**Purpose:** Routes payment to correct gateway

**How it works:**

1. Identifies payment method (eSewa, Khalti, Stripe, etc.)
2. Loads gateway configuration
3. Routes to appropriate payment processor
4. Handles callbacks

**Complexity:** O(1)

**Flow:**

```
Payment Method → Gateway Config → API Call → Callback → Verification
```

---

## 6. **Invoice Number Generation Algorithm**

**Location:** `app/Services/InvoiceService.php`

**Type:** Sequential ID Generation

**Purpose:** Creates unique invoice numbers

**How it works:**

1. Prefix: "INV-"
2. Year: Current year
3. Sequential number: Auto-increment
4. Format: INV-2025-00001

**Complexity:** O(1)

**Pattern:**

```
INV-{YEAR}-{SEQUENCE_NUMBER}
```

---

## 7. **Notification Routing Algorithm**

**Location:** `app/Http/Controllers/NotificationController.php`

**Type:** Multi-recipient Broadcasting

**Purpose:** Sends notifications to multiple users

**How it works:**

1. Determines recipient type (all/specific)
2. Fetches recipient list
3. Queues notification jobs
4. Dispatches to each recipient

**Complexity:** O(n) where n = number of recipients

**Types:**

-   All team members
-   Specific team members
-   All customers
-   Specific customers

---

## 8. **Booking Status State Machine**

**Location:** Booking lifecycle management

**Type:** Finite State Machine (FSM)

**Purpose:** Manages booking status transitions

**States:**

```
pending → confirmed → completed
   ↓         ↓
cancelled  cancelled
```

**Transitions:**

-   `pending` → `confirmed` (admin confirms)
-   `confirmed` → `completed` (service done)
-   `pending/confirmed` → `cancelled` (cancellation)

**Complexity:** O(1) per transition

---

## 9. **Search & Filter Algorithm**

**Location:** Index pages (bookings, payments, etc.)

**Type:** Query Building & Filtering

**Purpose:** Filters records based on criteria

**How it works:**

1. Accepts filter parameters (status, date range, etc.)
2. Builds dynamic SQL query
3. Applies WHERE clauses
4. Returns filtered results

**Complexity:** O(log n) with database indexes

**Filters:**

-   Status filtering
-   Date range filtering
-   Organization filtering
-   Service filtering

---

## 10. **Pagination Algorithm**

**Location:** All index pages

**Type:** Offset-based Pagination

**Purpose:** Divides large datasets into pages

**How it works:**

1. Calculates total records
2. Divides by per_page limit
3. Calculates offset
4. Returns current page

**Complexity:** O(1) for calculation, O(log n) for query

**Formula:**

```
offset = (page - 1) × per_page
```

---

## 11. **OAuth Authentication Flow**

**Location:** `app/Http/Controllers/WidgetAuthController.php`

**Type:** OAuth 2.0 Protocol

**Purpose:** Google login integration

**How it works:**

1. Redirect to Google OAuth
2. User authorizes
3. Receive callback with code
4. Exchange code for token
5. Fetch user info
6. Create/login user

**Complexity:** O(1) per request

**Flow:**

```
App → Google → User Auth → Callback → Token → User Data → Login
```

---

## 12. **Session Management Algorithm**

**Location:** Laravel session handling

**Type:** Token-based Session Management

**Purpose:** Maintains user login state

**How it works:**

1. Generates session ID
2. Stores in cookie
3. Maps to server-side data
4. Validates on each request

**Complexity:** O(1) with proper caching

---

## 13. **Queue Job Scheduling**

**Location:** Email sending, notifications

**Type:** Job Queue (FIFO)

**Purpose:** Processes background tasks

**How it works:**

1. Job pushed to queue
2. Worker picks from queue (FIFO)
3. Executes job
4. Marks as complete/failed

**Complexity:** O(1) for enqueue, O(n) for processing

**Jobs:**

-   Email sending
-   Invoice generation
-   Notification dispatch

---

## 14. **Price Calculation Algorithm**

**Location:** Booking creation

**Type:** Arithmetic Calculation

**Purpose:** Calculates total booking amount

**How it works:**

1. Gets service base price
2. Applies discounts (if any)
3. Adds taxes (if any)
4. Calculates final amount

**Complexity:** O(1)

**Formula:**

```
total = (base_price - discount) + tax
```

---

## 15. **Widget Customization Algorithm**

**Location:** `app/Http/Controllers/WidgetController.php`

**Type:** Template Generation

**Purpose:** Generates embeddable widget code

**How it works:**

1. Fetches widget settings
2. Applies customization (colors, fonts)
3. Generates iframe/JS code
4. Returns embed snippet

**Complexity:** O(1)

---

## 16. **Reschedule Validation Algorithm**

**Location:** Booking reschedule logic

**Type:** Constraint Validation

**Purpose:** Validates reschedule requests

**How it works:**

1. Checks new slot availability
2. Validates booking status
3. Ensures no conflicts
4. Updates booking

**Complexity:** O(1)

---

## Summary by Category

### **Time & Scheduling (3)**

1. Slot Generation
2. Conflict Detection
3. Reschedule Validation

### **Authentication & Authorization (3)**

4. RBAC
5. OAuth Flow
6. Session Management

### **Data Processing (4)**

7. Search & Filter
8. Pagination
9. Notification Routing
10. Queue Scheduling

### **Business Logic (4)**

11. Subscription Validation
12. Payment Routing
13. State Machine (Booking Status)
14. Price Calculation

### **Code Generation (2)**

15. Invoice Number Generation
16. Widget Template Generation

---

## Algorithm Complexity Summary

| Algorithm               | Time Complexity | Space Complexity |
| ----------------------- | --------------- | ---------------- |
| Slot Generation         | O(n)            | O(n)             |
| Conflict Detection      | O(1)            | O(1)             |
| Subscription Validation | O(1)            | O(1)             |
| RBAC                    | O(1)            | O(1)             |
| Payment Routing         | O(1)            | O(1)             |
| Invoice Generation      | O(1)            | O(1)             |
| Notification Routing    | O(n)            | O(n)             |
| State Machine           | O(1)            | O(1)             |
| Search & Filter         | O(log n)        | O(1)             |
| Pagination              | O(log n)        | O(1)             |
| OAuth Flow              | O(1)            | O(1)             |
| Session Management      | O(1)            | O(1)             |
| Queue Scheduling        | O(1) enqueue    | O(n)             |
| Price Calculation       | O(1)            | O(1)             |
| Widget Generation       | O(1)            | O(1)             |
| Reschedule Validation   | O(1)            | O(1)             |

**Overall System Complexity:** Mostly O(1) and O(log n) operations, making it highly efficient!

---

## Design Patterns Used

1. **Strategy Pattern** - Payment gateway routing
2. **State Pattern** - Booking status management
3. **Observer Pattern** - Event listeners for notifications
4. **Factory Pattern** - Service creation
5. **Repository Pattern** - Data access layer
6. **Middleware Pattern** - Request filtering
7. **Queue Pattern** - Background job processing

---

**Total: 16 Core Algorithms + 7 Design Patterns**

This system is built with efficiency and scalability in mind! 🚀
