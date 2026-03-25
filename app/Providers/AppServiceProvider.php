<?php

namespace App\Providers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
        $this->configureAuthorization();
        // $this->configureDatabaseLogging();
        $this->registerObservers();
    }

    /**
     * Configure authorization and gates
     */
    protected function configureAuthorization(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('SuperAdmin') ? true : null;
        });
    }

    /**
     * Configure database query logging
     */
    protected function configureDatabaseLogging(): void
    {
        DB::listen(function ($query) {
            Log::info('SQL Query', [
                'sql' => $query->sql,
            ]);
        });
    }

    /**
     * Register model observers
     */
    protected function registerObservers(): void
    {
        // Observers can be registered here
    }
}
