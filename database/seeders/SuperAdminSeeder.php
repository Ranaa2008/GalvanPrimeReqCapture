<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure super-admin role exists
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // Find or create super-admin user
        $superAdmin = User::where('username', 'superadmin')->first();

        if ($superAdmin) {
            // Update existing super-admin
            $superAdmin->update([
                'password' => Hash::make('Super123!@'),
                'email_verified' => true,
                'phone_verified' => true,
            ]);
            
            // Ensure role is assigned
            if (!$superAdmin->hasRole('super-admin')) {
                $superAdmin->assignRole('super-admin');
            }
            
            $this->command->info('Super-admin password updated to: Super123!@');
        } else {
            // Create new super-admin
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@galvanprime.lk',
                'phone_number' => '+94771234567',
                'phone_number_secondary' => null,
                'address' => null,
                'password' => Hash::make('Super123!@'),
                'email_verified' => true,
                'phone_verified' => true,
            ]);
            
            $superAdmin->assignRole('super-admin');
            
            $this->command->info('Super-admin created successfully!');
            $this->command->info('Username: superadmin');
            $this->command->info('Password: Super123!@');
        }
    }
}
