<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'admin' => ['SuperAdmin', 'Admin', 'Manager', 'Employee'],
            'student' => ['Student'],
            'trainer' => ['Trainer'],
        ] as $guard => $roles) {
            foreach ($roles as $roleName) {
                Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => $guard,
                ]);
            }
        }
    }
}
