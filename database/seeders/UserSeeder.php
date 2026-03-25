<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '+964-1-234-5678',

                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'phone' => '+964-1-234-5679',

                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password'),
                'phone' => '+964-1-234-5680',

                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'phone' => '+964-1-234-5681',

                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign roles
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $userRole = Role::where('name', 'User')->first();

        if ($superAdminRole && ! $admin->hasRole($superAdminRole)) {
            $admin->assignRole($superAdminRole);
        }

        if ($superAdminRole && ! $manager->hasRole($superAdminRole)) {
            $manager->assignRole($superAdminRole);
        }

        if ($userRole && ! $employee->hasRole($userRole)) {
            $employee->assignRole($userRole);
        }

        if ($userRole && ! $user->hasRole($userRole)) {
            $user->assignRole($userRole);
        }

    }
}
