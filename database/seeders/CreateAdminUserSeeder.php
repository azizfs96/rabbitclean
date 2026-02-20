<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user already exists
        $existingUser = User::where('email', 'aziz@admin.com')->first();
        
        if ($existingUser) {
            $this->command->warn('User aziz@admin.com already exists. Skipping...');
            return;
        }

        $adminUser = User::create([
            'first_name' => 'Aziz',
            'last_name' => 'Admin',
            'email' => 'aziz@admin.com',
            'password' => Hash::make('aziz123'),
            'mobile' => '0500000000',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminUser->assignRole('admin');

        // Assign permissions if config exists
        $permissions = config('acl.permissions', []);
        foreach ($permissions as $permission => $value) {
            if (is_array($value) && in_array('admin', $value)) {
                $adminUser->givePermissionTo($permission);
            }
        }

        $this->command->info('Admin user created successfully: aziz@admin.com');
    }
}
