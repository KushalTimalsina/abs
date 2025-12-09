<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Ideal for small organizations just starting out.',
                'price' => 5000, // Rs 50 (in paisa)
                'max_team_members' => 5,
                'slot_scheduling_days' => 2,
                'payment_methods' => ['cash'],
                'features' => [
                    'basic_analytics',
                    'standard_support',
                ],
                'sort_order' => 1,
                'is_active' => true,
                'online_payment_enabled' => false,
            ],
            [
                'name' => 'Mid',
                'slug' => 'mid',
                'description' => 'For growing teams needing more flexibility.',
                'price' => 8000, // Rs 80 (in paisa)
                'max_team_members' => 10,
                'slot_scheduling_days' => 7, // 1 week
                'payment_methods' => ['cash', 'esewa', 'khalti', 'stripe'],
                'features' => [
                    'advanced_analytics',
                    'priority_support',
                    'online_payments',
                ],
                'sort_order' => 2,
                'is_active' => true,
                'online_payment_enabled' => true,
            ],
            [
                'name' => 'Top',
                'slug' => 'top',
                'description' => 'Full access for established organizations.',
                'price' => 10000, // Rs 100 (in paisa)
                'max_team_members' => 20,
                'slot_scheduling_days' => 14, // 2 weeks
                'payment_methods' => ['cash', 'esewa', 'khalti', 'stripe'],
                'features' => [
                    'premium_analytics',
                    'dedicated_support',
                    'online_payments',
                    'api_access',
                ],
                'sort_order' => 3,
                'is_active' => true,
                'online_payment_enabled' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
