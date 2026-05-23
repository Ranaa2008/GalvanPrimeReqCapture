<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class StandardizePermissionNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔧 Standardizing permission names to kebab-case...');

        // Mapping of old names (with spaces) to new names (with hyphens)
        $permissionMapping = [
            'view users' => 'view-users',
            'create users' => 'create-users',
            'edit users' => 'edit-users',
            'delete users' => 'delete-users',
            'view roles' => 'view-roles',
            'create roles' => 'create-roles',
            'edit roles' => 'edit-roles',
            'delete roles' => 'delete-roles',
            'view permissions' => 'view-permissions',
            'create permissions' => 'create-permissions',
            'edit permissions' => 'edit-permissions',
            'delete permissions' => 'delete-permissions',
            'view admin dashboard' => 'view-admin-dashboard',
        ];

        foreach ($permissionMapping as $oldName => $newName) {
            $oldPermission = Permission::where('name', $oldName)->first();
            $newPermission = Permission::where('name', $newName)->first();

            if ($oldPermission) {
                if ($newPermission) {
                    // New permission exists, migrate relationships then delete old
                    $this->command->info("Migrating '{$oldName}' to '{$newName}'...");
                    
                    // Get all roles that have the old permission
                    $roles = $oldPermission->roles;
                    foreach ($roles as $role) {
                        // Check if role already has new permission
                        if (!$role->hasPermissionTo($newName)) {
                            // Copy the pivot data (assignable flag)
                            $pivotData = DB::table('role_has_permissions')
                                ->where('permission_id', $oldPermission->id)
                                ->where('role_id', $role->id)
                                ->first();
                            
                            $assignable = $pivotData->assignable ?? false;
                            
                            $role->permissions()->attach($newPermission->id, [
                                'assignable' => $assignable
                            ]);
                            
                            $this->command->info("  ✓ Migrated for role: {$role->name}");
                        }
                        
                        // Remove old permission from role
                        $role->permissions()->detach($oldPermission->id);
                    }
                    
                    // Delete old permission
                    $oldPermission->delete();
                    $this->command->info("  ✓ Deleted old permission: {$oldName}");
                } else {
                    // Just rename the old permission
                    $this->command->info("Renaming '{$oldName}' to '{$newName}'...");
                    $oldPermission->name = $newName;
                    $oldPermission->save();
                    $this->command->info("  ✓ Renamed");
                }
            }
        }

        $this->command->info('');
        $this->command->info('✅ Permission standardization complete!');
        $this->command->info('All permissions now use kebab-case (e.g., view-users)');
    }
}
