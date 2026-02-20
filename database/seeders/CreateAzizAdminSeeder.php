<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CreateAzizAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'aziz@admin.com'],
            [
                'first_name' => 'Aziz',
                'last_name' => 'Admin',
                'password' => 'aziz123',
                'mobile' => '0555555555',
                'is_active' => true,
            ]
        );

        $user->update(['password' => 'aziz123', 'is_active' => true]);
        $user->syncRoles(['root']);

        // مزامنة كل صلاحيات root من config مع المستخدم (للشريط الجانبي و @can)
        $rootPermissions = [];
        foreach (config('acl.permissions', []) as $permission => $roles) {
            if (is_array($roles) && in_array('root', $roles, true)) {
                $rootPermissions[] = $permission;
            }
        }
        $user->syncPermissions($rootPermissions);

        $this->command->info('تم إنشاء/تحديث المستخدم aziz@admin.com بدور root مع كل الصلاحيات.');
    }
}
