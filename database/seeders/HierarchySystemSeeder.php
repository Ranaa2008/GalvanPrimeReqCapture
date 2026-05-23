<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HierarchySystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create new permissions for user management
        $permissions = [
            'verify-users' => 'Can verify users email and phone',
            'deverify-users' => 'Can remove verification from users',
            'block-users' => 'Can block/unblock users',
            'create-roles' => 'Can create new roles',
            'assign-roles' => 'Can assign roles to users',
            'create-permissions' => 'Can create new permissions',
            'assign-permissions' => 'Can assign permissions to roles/users',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Update role superiority levels
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->update(['superiority' => 1]);
            
            // Give super-admin all permissions and mark them as assignable
            $allPermissions = Permission::all();
            foreach ($allPermissions as $permission) {
                $superAdmin->permissions()->syncWithoutDetaching([
                    $permission->id => ['assignable' => true]
                ]);
            }
        }

        $systemOwner = Role::where('name', 'system-owner')->first();
        if ($systemOwner) {
            $systemOwner->update(['superiority' => 2]);
            
            // Give system-owner key permissions
            $ownerPermissions = [
                'verify-users',
                'deverify-users', 
                'block-users',
                'create-roles',
                'assign-roles',
                'assign-permissions',
                'view-users',
                'create-users',
                'edit-users',
                'delete-users',
            ];
            
            foreach ($ownerPermissions as $permName) {
                $permission = Permission::where('name', $permName)->first();
                if ($permission) {
                    $systemOwner->permissions()->syncWithoutDetaching([
                        $permission->id => ['assignable' => true]
                    ]);
                }
            }
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->update(['superiority' => 3]);
        }

        $user = Role::where('name', 'user')->first();
        if ($user) {
            $user->update(['superiority' => 10]);
        }

        $this->command->info('Hierarchy system seeded successfully!');
    }
}
