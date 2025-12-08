<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\OrganizationSubscription;

// Create organization
$org = Organization::firstOrCreate(
    ['email' => 'testorg@abs.com'],
    [
        'name' => 'Test Organization',
        'slug' => 'test-org',
        'is_active' => true,
    ]
);

// Create subscription
$plan = SubscriptionPlan::first();
if ($plan && !$org->subscription) {
    OrganizationSubscription::create([
        'organization_id' => $org->id,
        'subscription_plan_id' => $plan->id,
        'start_date' => now(),
        'end_date' => now()->addMonths(1),
        'is_active' => true,
    ]);
}

// Create Admin
$admin = User::firstOrCreate(
    ['email' => 'admin@abs.com'],
    [
        'name' => 'Admin User',
        'password' => bcrypt('password'),
        'user_type' => 'admin',
        'email_verified_at' => now(),
    ]
);
if (!$admin->organizations()->where('organization_id', $org->id)->exists()) {
    $admin->organizations()->attach($org->id, ['role' => 'admin', 'status' => 'active']);
}

// Create Staff
$staff = User::firstOrCreate(
    ['email' => 'staff@abs.com'],
    [
        'name' => 'Staff Member',
        'password' => bcrypt('password'),
        'user_type' => 'team_member',
        'email_verified_at' => now(),
    ]
);
if (!$staff->organizations()->where('organization_id', $org->id)->exists()) {
    $staff->organizations()->attach($org->id, ['role' => 'team_member', 'status' => 'active']);
}

// Create Customer
$customer = User::firstOrCreate(
    ['email' => 'customer@abs.com'],
    [
        'name' => 'Customer User',
        'password' => bcrypt('password'),
        'user_type' => 'customer',
        'email_verified_at' => now(),
    ]
);

echo "✅ Test users created successfully!\n\n";
echo "=== TEST LOGIN CREDENTIALS ===\n\n";
echo "SUPERADMIN:\n";
echo "  URL: http://localhost:8000/superadmin/login\n";
echo "  Email: superadmin@abs.com\n";
echo "  Password: password\n\n";
echo "ADMIN:\n";
echo "  URL: http://localhost:8000/login\n";
echo "  Email: admin@abs.com\n";
echo "  Password: password\n\n";
echo "STAFF:\n";
echo "  URL: http://localhost:8000/login\n";
echo "  Email: staff@abs.com\n";
echo "  Password: password\n\n";
echo "CUSTOMER:\n";
echo "  URL: http://localhost:8000/login\n";
echo "  Email: customer@abs.com\n";
echo "  Password: password\n\n";
