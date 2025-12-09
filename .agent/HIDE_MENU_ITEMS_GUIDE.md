# Quick Fix: Hide Menu Items Based on Permissions

## Immediate Action Required

Add this helper function to `app/helpers.php` (create if doesn't exist):

```php
<?php

if (!function_exists('userCan')) {
    function userCan($permission) {
        $user = auth()->user();
        $currentOrgId = session('current_organization_id');

        if (!$user || !$currentOrgId) {
            return false;
        }

        return $user->hasPermissionInOrganization($currentOrgId, $permission);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        $user = auth()->user();
        $currentOrgId = session('current_organization_id');

        if (!$user || !$currentOrgId) {
            return false;
        }

        $org = $user->organizations()
            ->wherePivot('organization_id', $currentOrgId)
            ->first();

        return $org && $org->pivot->role === 'admin';
    }
}
```

## Then update sidebar.blade.php:

### Services (Line 93-101):

```blade
@if(isAdmin() || userCan('view_services'))
    <!-- Services -->
    <li>
        ...existing services menu...
    </li>
@endif
```

### Slots (Line 88):

```blade
@if(isAdmin() || userCan('view_all_slots') || userCan('view_own_slots'))
    <li>
        <a href="{{ route('organization.slots.index', $currentOrg) }}">Manage Slots</a>
    </li>
@endif
```

### Team (Line 127-146):

```blade
@if(isAdmin() || userCan('view_team'))
    <!-- Team -->
    <li>
        ...existing team menu...
    </li>
@endif
```

### Payments (Line 148-157):

```blade
@if(isAdmin() || userCan('view_payments'))
    <!-- Payments -->
    <li>
        ...existing payments menu...
    </li>
@endif
```

### Settings (Line 159-187):

```blade
@if(isAdmin())
    <!-- Settings - Admin Only -->
    <li>
        ...existing settings menu...
    </li>
@endif
```

## Auto-load helpers

Add to `composer.json`:

```json
"autoload": {
    "files": [
        "app/helpers.php"
    ]
}
```

Then run: `composer dump-autoload`
