<?php

namespace Database\Seeders;

use App\Models\Superadmin;
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
            'name' => 'Super Admin NMG Development',
            'email' => 'superadmin@nmgdevelopment.xyz',
            'password' => Hash::make('Password@9243'), // Change this in production!
            'phone' => '+977-9866889314',
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
