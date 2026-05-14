<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('view-purchases', fn (User $user) => in_array($user->role, [User::ROLE_ADMIN, User::ROLE_USER], true));

        Gate::define('manage-purchases', fn (User $user) => $user->role === User::ROLE_ADMIN);

        Gate::define('run-legacy-migration', fn (User $user) => $user->role === User::ROLE_ADMIN);
    }
}
