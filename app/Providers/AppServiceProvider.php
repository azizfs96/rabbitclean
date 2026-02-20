<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // مستخدم root يملك كل الصلاحيات (@can في الشريط الجانبي وغيره)
        Gate::before(function ($user, string $ability) {
            if ($user && method_exists($user, 'getRoleNames')) {
                $roleNames = $user->getRoleNames();
                if ($roleNames->isNotEmpty() && strtolower((string) $roleNames->first()) === 'root') {
                    return true;
                }
            }
            return null;
        });
    }
}
