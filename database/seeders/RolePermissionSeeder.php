<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions (using kebab-case)
        $permissions = [
            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Project and requirement workflow
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'submit-requirements',
            'edit-own-requirements',
            'delete-own-requirements',
            'view-assigned-requirements',
            
            // Role management
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            
            // Permission management
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            
            // Dashboard
            'view-admin-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with proper hierarchy
        // 1. User role - Regular software users (no admin access)
        $userRole = Role::firstOrCreate(['name' => 'user']);
        
        // 2. Admin role - Business owners (full business management, cannot manage roles/permissions)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // 3. Super Admin role - Developers/Software maintaining company (full system access)
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // 4. Client role - Can submit requirements in assigned projects
        $clientRole = Role::firstOrCreate(
            ['name' => 'client'],
            ['guard_name' => 'web', 'superiority' => 20]
        );

        // 5. Developer role - Can view requirements for assigned projects
        $developerRole = Role::firstOrCreate(
            ['name' => 'developer'],
            ['guard_name' => 'web', 'superiority' => 21]
        );

        // Assign permissions to roles
        
        // Regular users - No admin permissions
        // They get app-specific permissions (you can add these as needed)
        // $userRole->givePermissionTo([]);

        // Business owners (admin) - Can manage their business operations
        $adminRole->givePermissionTo([
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'view-admin-dashboard',
        ]);

        // Developers/Maintainers (super-admin) - Full system control
        $superAdminRole->givePermissionTo(Permission::all());

        // Client role permissions
        $clientRole->givePermissionTo([
            'submit-requirements',
            'edit-own-requirements',
            'delete-own-requirements',
        ]);

        // Developer role permissions
        $developerRole->givePermissionTo([
            'view-assigned-requirements',
        ]);

        // Create default super admin user (for developers/maintainers)
        $admin = User::create([
            'name' => 'System Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@system.com',
            'phone_number' => '+1234567890',
            'phone_number_secondary' => null,
            'address' => 'System Headquarters',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('super-admin');

        // Create default business owner user
        $businessOwner = User::create([
            'name' => 'Business Owner',
            'username' => 'business',
            'email' => 'owner@business.com',
            'phone_number' => '+1987654321',
            'phone_number_secondary' => null,
            'address' => 'Business Office',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $businessOwner->assignRole('admin');
        $businessOwner->assignRole('admin');

        // Create default regular user (software user)
        $testUser = User::create([
            'name' => 'Regular User',
            'username' => 'user',
            'email' => 'user@example.com',
            'phone_number' => '+1555666777',
            'phone_number_secondary' => null,
            'address' => '123 User Street',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $testUser->assignRole('user');
    }
}
