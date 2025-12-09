# Team Member Permission System - Implementation Guide

## Current Issue

Team members currently have full admin access to all features, which is a security and UX concern.

## Required Implementation

### 1. **Role-Based Menu Visibility** (Quick Fix - UI Only)

Hide menu items for team members in `resources/views/layouts/sidebar.blade.php`:

**Team Members Should NOT See:**

-   Services menu
-   Slots menu (except "My Slots")
-   Team Members menu
-   Payment Gateways menu
-   Organization Settings menu
-   Widget Settings menu
-   Invoices menu
-   Payments menu (full access)

**Team Members SHOULD See:**

-   Dashboard (filtered to their data)
-   My Bookings (only their assigned bookings)
-   My Slots (only their assigned slots)
-   Profile/Settings

### 2. **Controller Authorization** (Security - CRITICAL)

Add authorization checks in controllers:

```php
// In BookingController
public function index(Request $request, Organization $organization)
{
    $this->authorize('view', $organization);

    $query = $organization->bookings()->with(['customer', 'service', 'slot']);

    // Filter for team members - only show their bookings
    if (Auth::user()->isTeamMember()) {
        $query->where('staff_id', Auth::id());
    }

    // ... rest of code
}
```

### 3. **Policy Classes to Create**

Create policies for:

-   `ServicePolicy` - prevent team members from create/edit/delete
-   `SlotPolicy` - allow view only for assigned slots
-   `BookingPolicy` - allow view/update only for assigned bookings
-   `PaymentGatewayPolicy` - deny all access for team members
-   `OrganizationPolicy` - deny settings access for team members

### 4. **Middleware**

Create `EnsureUserHasPermission` middleware to check permissions before allowing access to routes.

### 5. **View Directives**

Use `@can` and `@cannot` directives in Blade templates:

```blade
@can('create', App\Models\Service::class)
    <a href="{{ route('organization.services.create', $organization) }}">
        Create Service
    </a>
@endcan
```

### 6. **Database Changes**

Consider adding a `permissions` JSON column to `organization_users` pivot table to store granular permissions.

## Implementation Steps

1. **Phase 1: Quick UI Fix** (30 mins)
    - Hide restricted menu items in sidebar
    - Add role badges to show user type
2. **Phase 2: Controller Filters** (1 hour)
    - Add query filters for team members
    - Restrict data access in all controllers
3. **Phase 3: Authorization Policies** (2 hours)
    - Create policy classes
    - Add authorization checks
    - Test all endpoints
4. **Phase 4: Route Protection** (1 hour)
    - Add middleware to routes
    - Test unauthorized access attempts
5. **Phase 5: View Updates** (1 hour)
    - Add @can directives
    - Hide action buttons for restricted features
    - Update forms to disable restricted fields

## Testing Checklist

-   [ ] Team member cannot access services page
-   [ ] Team member cannot create/edit slots
-   [ ] Team member only sees their assigned bookings
-   [ ] Team member cannot access payment settings
-   [ ] Team member cannot access organization settings
-   [ ] Team member can update their profile
-   [ ] Team member can view their schedule
-   [ ] Team member can update booking status (confirm/complete)
-   [ ] Direct URL access to restricted pages returns 403
-   [ ] API endpoints are protected

## Priority: HIGH

This is a security issue that should be addressed in the next session.
