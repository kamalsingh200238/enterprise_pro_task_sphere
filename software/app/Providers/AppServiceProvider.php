<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Gate;
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
        // define gate for viewing logs
        Gate::define('view-logs', function (User $user) {
            return $user->hasRole([UserRole::Admin, UserRole::Supervisor]); // implement based on your user role system
        });

        // Define gate for exporting logs
        Gate::define('export-logs', function (User $user) {
            return $user->hasRole([UserRole::Admin, UserRole::Supervisor]); // same check for export permission
        });
    }
}
