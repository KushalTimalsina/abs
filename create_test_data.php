<?php

use Illuminate\Support\Facades\DB;

// Create subscription plan
DB::table('subscription_plans')->insert([
    'name' => 'Premium',
    'price' => 99.99,
    'billing_cycle' => 'monthly',
    'features' => json_encode([
        'max_team_members' => 10,
        'max_services' => 50,
        'online_payments' => true,
        'custom_branding' => true,
        'analytics' => true,
        'api_access' => true
    ]),
    'payment_methods' => json_encode(['esewa', 'khalti', 'stripe', 'bank_transfer', 'cash']),
    'is_active' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

$planId = DB::getPdo()->lastInsertId();

// Create organization
DB::table('organizations')->insert([
    'name' => 'Test Organization',
    'slug' => 'test-org',
    'email' => 'test@org.com',
    'phone' => '1234567890',
    'address' => 'Test Address',
    'subscription_plan_id' => $planId,
    'subscription_status' => 'active',
    'subscription_start_date' => now(),
    'subscription_end_date' => now()->addMonth(),
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Test data created successfully!\n";
