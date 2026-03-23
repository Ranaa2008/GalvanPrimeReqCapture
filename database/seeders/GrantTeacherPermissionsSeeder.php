<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GrantTeacherPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher = Role::where('name', 'teacher')->first();
        
        if (!$teacher) {
            $this->command->error('Teacher role not found! Make sure you created it first.');
            return;
        }

        // User management permissions for teacher
        $permissions = ['view-users', 'create-users', 'edit-users'];

        foreach ($permissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            
            if ($permission) {
                $teacher->givePermissionTo($permission);
                $this->command->info("✓ Granted: {$permName}");
            } else {
                $this->command->error("✗ Permission not found: {$permName}");
            }
        }

        $this->command->info('Teacher permissions granted!');
        $this->command->info('Teacher now has: ' . $teacher->permissions->pluck('name')->implode(', '));
    }
}
