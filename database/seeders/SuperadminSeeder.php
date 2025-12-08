<?php

namespace Database\Seeders;

use App\Models\Superadmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default superadmin account
        Superadmin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@abs.com',
            'password' => Hash::make('password'), // Change this in production!
            'phone' => '+977-9800000000',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // You can create additional superadmins here if needed
        // Superadmin::create([
        //     'name' => 'Admin Two',
        //     'email' => 'admin2@abs.com',
        //     'password' => Hash::make('password'),
        //     'is_active' => true,
        //     'email_verified_at' => now(),
        // ]);
    }
}
