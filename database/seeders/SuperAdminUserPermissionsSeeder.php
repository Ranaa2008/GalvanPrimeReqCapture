<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminUserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super-admin')->first();
        
        if (!$superAdmin) {
            $this->command->error('Super-admin role not found!');
            return;
        }

        // User management permissions
        $userPermissions = [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
        ];

        foreach ($userPermissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName]);
            
            // Sync with assignable = true
            $superAdmin->permissions()->syncWithoutDetaching([
                $permission->id => ['assignable' => true]
            ]);
        }

        $this->command->info('Super-admin user management permissions granted!');
    }
}
