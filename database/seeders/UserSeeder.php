<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Demo Student',
                'password' => Hash::make('password'),
                'phone' => '+964-1-234-5681',
                'active_role' => 'student',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->ensureDefaultAppRole();
    }
}
