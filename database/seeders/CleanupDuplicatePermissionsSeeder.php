<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CleanupDuplicatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete permissions with spaces (incorrect format)
        $duplicatePermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
        ];

        foreach ($duplicatePermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $this->command->info("Deleting duplicate: {$permName}");
                $permission->delete();
            }
        }

        $this->command->info('✓ Duplicate permissions cleaned up!');
        $this->command->info('Remaining permissions should only use hyphens (e.g., view-users)');
    }
}
