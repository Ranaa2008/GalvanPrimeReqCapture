<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ViewPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create view permissions
        $permissions = [
            'view-roles' => 'Can view roles list and details',
            'view-permissions' => 'Can view permissions list',
            'edit-roles' => 'Can edit existing roles',
            'delete-roles' => 'Can delete roles',
            'edit-permissions' => 'Can edit existing permissions',
            'delete-permissions' => 'Can delete permissions',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Give super-admin all new permissions
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $allPermissions = Permission::whereIn('name', array_keys($permissions))->get();
            foreach ($allPermissions as $permission) {
                $superAdmin->permissions()->syncWithoutDetaching([
                    $permission->id => ['assignable' => true]
                ]);
            }
        }

        $this->command->info('View permissions seeded successfully!');
    }
}
