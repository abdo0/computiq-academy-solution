<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Define all permissions with their groups
        $permissions = [
            // Activity Logs
            ['name' => 'access_activity_logs', 'group' => 'Activity Logs'],
            ['name' => 'view_activity_logs', 'group' => 'Activity Logs'],
            ['name' => 'create_activity_logs', 'group' => 'Activity Logs'],
            ['name' => 'edit_activity_logs', 'group' => 'Activity Logs'],
            ['name' => 'delete_activity_logs', 'group' => 'Activity Logs'],

            // Customers (Clients)
            ['name' => 'access_customers', 'group' => 'Customers'],
            ['name' => 'view_customers', 'group' => 'Customers'],
            ['name' => 'create_customers', 'group' => 'Customers'],
            ['name' => 'edit_customers', 'group' => 'Customers'],
            ['name' => 'delete_customers', 'group' => 'Customers'],

            // Visits
            ['name' => 'access_visits', 'group' => 'Visits'],
            ['name' => 'view_visits', 'group' => 'Visits'],
            ['name' => 'create_visits', 'group' => 'Visits'],
            ['name' => 'edit_visits', 'group' => 'Visits'],
            ['name' => 'delete_visits', 'group' => 'Visits'],

            // Branches
            ['name' => 'access_branches', 'group' => 'Branches'],
            ['name' => 'view_branches', 'group' => 'Branches'],
            ['name' => 'create_branches', 'group' => 'Branches'],
            ['name' => 'edit_branches', 'group' => 'Branches'],
            ['name' => 'delete_branches', 'group' => 'Branches'],

            // Reports
            ['name' => 'access_reports', 'group' => 'Reports'],
            ['name' => 'view_reports', 'group' => 'Reports'],
            ['name' => 'create_reports', 'group' => 'Reports'],
            ['name' => 'edit_reports', 'group' => 'Reports'],
            ['name' => 'delete_reports', 'group' => 'Reports'],

            // Settings
            ['name' => 'access_settings', 'group' => 'Settings'],
            ['name' => 'view_settings', 'group' => 'Settings'],
            ['name' => 'create_settings', 'group' => 'Settings'],
            ['name' => 'edit_settings', 'group' => 'Settings'],
            ['name' => 'delete_settings', 'group' => 'Settings'],

            // Users
            ['name' => 'access_users', 'group' => 'Users'],
            ['name' => 'view_users', 'group' => 'Users'],
            ['name' => 'create_users', 'group' => 'Users'],
            ['name' => 'edit_users', 'group' => 'Users'],
            ['name' => 'delete_users', 'group' => 'Users'],

            // Roles
            ['name' => 'access_roles', 'group' => 'Roles'],
            ['name' => 'view_roles', 'group' => 'Roles'],
            ['name' => 'create_roles', 'group' => 'Roles'],
            ['name' => 'edit_roles', 'group' => 'Roles'],
            ['name' => 'delete_roles', 'group' => 'Roles'],

            // Backups
            ['name' => 'access_backups', 'group' => 'Backups'],
            ['name' => 'view_backups', 'group' => 'Backups'],
            ['name' => 'create_backups', 'group' => 'Backups'],
            ['name' => 'edit_backups', 'group' => 'Backups'],
            ['name' => 'delete_backups', 'group' => 'Backups'],

            // Currencies
            ['name' => 'access_currencies', 'group' => 'Currencies'],
            ['name' => 'view_currencies', 'group' => 'Currencies'],
            ['name' => 'create_currencies', 'group' => 'Currencies'],
            ['name' => 'edit_currencies', 'group' => 'Currencies'],
            ['name' => 'delete_currencies', 'group' => 'Currencies'],

            // Countries
            ['name' => 'access_countries', 'group' => 'Countries'],
            ['name' => 'view_countries', 'group' => 'Countries'],
            ['name' => 'create_countries', 'group' => 'Countries'],
            ['name' => 'edit_countries', 'group' => 'Countries'],
            ['name' => 'delete_countries', 'group' => 'Countries'],

            // Genders
            ['name' => 'access_genders', 'group' => 'Genders'],
            ['name' => 'view_genders', 'group' => 'Genders'],
            ['name' => 'create_genders', 'group' => 'Genders'],
            ['name' => 'edit_genders', 'group' => 'Genders'],
            ['name' => 'delete_genders', 'group' => 'Genders'],

            // Organizations
            ['name' => 'access_organizations', 'group' => 'Organizations'],
            ['name' => 'view_organizations', 'group' => 'Organizations'],
            ['name' => 'create_organizations', 'group' => 'Organizations'],
            ['name' => 'edit_organizations', 'group' => 'Organizations'],
            ['name' => 'delete_organizations', 'group' => 'Organizations'],

            // Custom Permissions
            ['name' => 'export_data', 'group' => 'Custom'],
            ['name' => 'import_data', 'group' => 'Custom'],
        ];

        // Sync permissions - create missing ones and remove extra ones
        $existingPermissions = Permission::pluck('name')->toArray();

        // Create or update permissions
        $createdCount = 0;
        $updatedCount = 0;
        foreach ($permissions as $permissionData) {
            $permission = Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'guard_name' => 'web',
                ],
                [
                    'group' => $permissionData['group'],
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $createdCount++;
            } elseif ($permission->wasChanged()) {
                $updatedCount++;
            }
        }

        // Remove permissions that are no longer in the list
        $permissionNames = array_column($permissions, 'name');
        $permissionsToRemove = array_diff($existingPermissions, $permissionNames);
        $removedCount = 0;
        if (! empty($permissionsToRemove)) {
            $removedCount = Permission::whereIn('name', $permissionsToRemove)->count();
            Permission::whereIn('name', $permissionsToRemove)->delete();
        }

        // Log the sync results
        if ($createdCount > 0 || $updatedCount > 0 || $removedCount > 0) {
            $this->command->info("Permission sync completed: {$createdCount} created, {$updatedCount} updated, {$removedCount} removed");
        }

        // Get roles
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $employeeRole = Role::where('name', 'Employee')->first();
        $userRole = Role::where('name', 'User')->first();

        // Super Admin - All permissions
        if ($superAdminRole && $superAdminRole->permissions()->count() === 0) {
            $superAdminRole->givePermissionTo(Permission::all());
        }

        // Admin - Most permissions except some sensitive ones
        if ($adminRole && $adminRole->permissions()->count() === 0) {
            $adminPermissions = [
                'access_activity_logs', 'view_activity_logs',
                'access_customers', 'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
                'access_visits', 'view_visits', 'create_visits', 'edit_visits', 'delete_visits',
                'access_branches', 'view_branches', 'create_branches', 'edit_branches', 'delete_branches',
                'access_reports', 'view_reports', 'create_reports', 'edit_reports', 'delete_reports',
                'access_settings', 'view_settings', 'create_settings', 'edit_settings', 'delete_settings',
                'access_users', 'view_users', 'create_users', 'edit_users', 'delete_users',
                'access_roles', 'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
                'access_backups', 'view_backups', 'create_backups', 'edit_backups', 'delete_backups',
                'access_currencies', 'view_currencies', 'create_currencies', 'edit_currencies', 'delete_currencies',
                'access_countries', 'view_countries', 'create_countries', 'edit_countries', 'delete_countries',
                'access_genders', 'view_genders', 'create_genders', 'edit_genders', 'delete_genders',
                'export_data', 'import_data',
                'access_organizations', 'view_organizations', 'create_organizations', 'edit_organizations', 'delete_organizations',
            ];
            $adminRole->givePermissionTo($adminPermissions);
        }

        // Manager - Limited permissions
        if ($managerRole && $managerRole->permissions()->count() === 0) {
            $managerPermissions = [
                'access_customers', 'view_customers', 'create_customers', 'edit_customers',
                'access_visits', 'view_visits', 'create_visits', 'edit_visits',
                'access_branches', 'view_branches',
                'access_reports', 'view_reports', 'create_reports',
                'access_users', 'view_users', 'create_users', 'edit_users',
                'access_currencies', 'view_currencies',
                'access_countries', 'view_countries',
                'access_genders', 'view_genders',
                'export_data',
                'access_organizations', 'view_organizations',
            ];
            $managerRole->givePermissionTo($managerPermissions);
        }

        // Employee - Basic permissions
        if ($employeeRole && $employeeRole->permissions()->count() === 0) {
            $employeePermissions = [
                'access_customers', 'view_customers', 'create_customers', 'edit_customers',
                'access_visits', 'view_visits', 'create_visits', 'edit_visits',
                'access_reports', 'view_reports',
                'access_currencies', 'view_currencies',
                'access_countries', 'view_countries',
                'access_genders', 'view_genders',
            ];
            $employeeRole->givePermissionTo($employeePermissions);
        }

        // User - Very limited permissions
        if ($userRole && $userRole->permissions()->count() === 0) {
            $userPermissions = [
                'access_customers', 'view_customers',
                'access_visits', 'view_visits',
                'access_reports', 'view_reports',
            ];
            $userRole->givePermissionTo($userPermissions);
        }

    }
}
