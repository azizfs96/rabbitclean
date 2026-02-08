<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAzizAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'aziz@admin.com'],
            [
                'first_name' => 'Aziz',
                'last_name' => 'Admin',
                'password' => Hash::make('aziz123'),
                'mobile' => '0555555555',
                'is_active' => true,
            ]
        );

        $user->update(['password' => Hash::make('aziz123')]);
        $user->syncRoles(['admin']);
        $user->givePermissionTo('root');

        $this->command->info('تم إنشاء/تحديث المستخدم aziz@admin.com بصلاحية أدمن.');
    }
}
