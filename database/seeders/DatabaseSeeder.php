<?php

namespace Database\Seeders;

use App\Models\User;
<<<<<<< HEAD
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(ScheduleSeeder::class);
        $this->call(NotificationManageSeeder::class);
        $this->call(PaymentGatewaySeeder::class);
        $this->call(LanguageSeeder::class);

        $rootUser = User::factory()->create([
            'first_name' => 'aziz',
            'last_name' => 'admin',
            'email' => 'aziz@admin.com',
            'password' => bcrypt('aziz123'),
            'mobile' => '0555555555',
            'is_active' => true,
        ]);
        $permissions = config('acl.permissions');

        foreach ($permissions as $permission => $value) {
            $rootUser->givePermissionTo($permission);
        }
        $rootUser->assignRole('root');

        
        if (app()->environment('local')) {
            $this->call(UsersTableSeeder::class);
            $this->call(VariantSeeder::class);
            $this->call(AddressSeeder::class);
            $this->call(ProductSeeder::class);
            $this->call(RatingSeeder::class);

        }
       

        $this->installPassportClient();
    }

    private function installPassportClient()
    {
        $this->command->warn('Installing passport client');
        Artisan::call('passport:install');
        $this->command->info('Passport client installed');
=======
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
>>>>>>> 7d2250222b1076404c7124acb2f73be59dd3ce1a
    }
}
