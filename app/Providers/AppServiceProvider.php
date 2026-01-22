<?php

namespace App\Providers;

use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Goal;
use App\Observers\TransactionObserver;
use App\Observers\GoalObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return (bool) $user->is_admin;
        });

        Gate::define('financeUser', function (User $user) {
            return ! (bool) $user->is_admin;
        });

        Transaction::observe(TransactionObserver::class);
        Goal::observe(GoalObserver::class);
    }
}
