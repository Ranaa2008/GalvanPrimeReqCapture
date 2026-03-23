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
            'edit-own-requirements',
            'delete-own-requirements',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $clientRole = Role::firstOrCreate([
            'name' => 'client',
            'guard_name' => 'web',
        ]);

        $clientRole->givePermissionTo($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions to avoid accidental removal from production.
    }
};
