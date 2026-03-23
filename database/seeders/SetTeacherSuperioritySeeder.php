<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SetTeacherSuperioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher = Role::where('name', 'teacher')->first();
        
        if (!$teacher) {
            $this->command->error('Teacher role not found!');
            return;
        }

        // Set teacher superiority to 4 (lower rank than admin which is 3)
        // Hierarchy: 1 = Super Admin, 2 = System Owner, 3 = Admin/Business Owner, 4+ = Custom roles
        $teacher->superiority = 4;
        $teacher->save();

        $this->command->info("✓ Teacher role superiority set to 4");
        
        // Show current hierarchy
        $this->command->info("\nCurrent Role Hierarchy (lower number = higher rank):");
        $roles = Role::orderBy('superiority')->get();
        foreach ($roles as $role) {
            $this->command->info("  {$role->superiority}. {$role->name}");
        }
    }
}
