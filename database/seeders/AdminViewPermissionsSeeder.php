<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminViewPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Give admin role view-only permissions
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo(['view-roles', 'view-permissions']);
            $this->command->info('Admin role granted view permissions for roles and permissions!');
        } else {
            $this->command->warn('Admin role not found!');
        }
    }
}
