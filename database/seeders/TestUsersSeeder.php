<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Superadmin;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\OrganizationSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Superadmin (already exists from SuperadminSeeder, but let's ensure it)
        $superadmin = Superadmin::firstOrCreate(
            ['email' => 'superadmin@abs.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '+977-9800000000',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Create a test organization
        $organization = Organization::firstOrCreate(
            ['email' => 'testorg@abs.com'],
            [
                'name' => 'Test Organization',
                'slug' => 'test-organization',
                'phone' => '+977-9800000001',
                'address' => 'Kathmandu, Nepal',
                'is_active' => true,
            ]
        );

        // 3. Create subscription for the organization
        $plan = SubscriptionPlan::first();
        if ($plan && !$organization->subscription) {
            OrganizationSubscription::create([
                'organization_id' => $organization->id,
                'subscription_plan_id' => $plan->id,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'is_active' => true,
            ]);
        }

        // 4. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@abs.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'phone' => '+977-9800000002',
                'email_verified_at' => now(),
            ]
        );

        // Attach admin to organization
        if (!$admin->organizations()->where('organization_id', $organization->id)->exists()) {
            $admin->organizations()->attach($organization->id, [
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        // 5. Create Staff/Team Member User
        $staff = User::firstOrCreate(
            ['email' => 'staff@abs.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password'),
                'user_type' => 'team_member',
                'phone' => '+977-9800000003',
                'email_verified_at' => now(),
            ]
        );

        // Attach staff to organization
        if (!$staff->organizations()->where('organization_id', $organization->id)->exists()) {
            $staff->organizations()->attach($organization->id, [
                'role' => 'team_member',
                'status' => 'active',
            ]);
        }

        // 6. Create Customer User
        $customer = User::firstOrCreate(
            ['email' => 'customer@abs.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'phone' => '+977-9800000004',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->newLine();
        $this->command->info('=== TEST LOGIN CREDENTIALS ===');
        $this->command->newLine();
        $this->command->info('SUPERADMIN:');
        $this->command->info('  URL: http://localhost:8000/superadmin/login');
        $this->command->info('  Email: superadmin@abs.com');
        $this->command->info('  Password: password');
        $this->command->newLine();
        $this->command->info('ADMIN (Organization Admin):');
        $this->command->info('  URL: http://localhost:8000/login');
        $this->command->info('  Email: admin@abs.com');
        $this->command->info('  Password: password');
        $this->command->newLine();
        $this->command->info('STAFF (Team Member):');
        $this->command->info('  URL: http://localhost:8000/login');
        $this->command->info('  Email: staff@abs.com');
        $this->command->info('  Password: password');
        $this->command->newLine();
        $this->command->info('CUSTOMER:');
        $this->command->info('  URL: http://localhost:8000/login');
        $this->command->info('  Email: customer@abs.com');
        $this->command->info('  Password: password');
        $this->command->newLine();
    }
}
