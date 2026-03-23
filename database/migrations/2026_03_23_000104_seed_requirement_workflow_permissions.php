<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'view-projects',
            'create-projects',
            'edit-projects',
            'delete-projects',
            'submit-requirements',
            'edit-own-requirements',
            'delete-own-requirements',
            'view-assigned-requirements',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $clientRole = Role::firstOrCreate(
            ['name' => 'client', 'guard_name' => 'web'],
            ['superiority' => 20]
        );

        $developerRole = Role::firstOrCreate(
            ['name' => 'developer', 'guard_name' => 'web'],
            ['superiority' => 21]
        );

        $clientRole->givePermissionTo([
            'submit-requirements',
            'edit-own-requirements',
            'delete-own-requirements',
        ]);
        $developerRole->givePermissionTo(['view-assigned-requirements']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions/roles on rollback to avoid accidental data loss.
    }
};
