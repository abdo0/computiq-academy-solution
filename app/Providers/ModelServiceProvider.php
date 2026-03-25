<?php

namespace App\Providers;

use App\Observers\DatabaseNotificationObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerObservers();
        $this->configureModelBehavior();
    }

    /**
     * Register model observers
     */
    protected function registerObservers(): void
    {
        DatabaseNotification::observe(DatabaseNotificationObserver::class);
    }

    /**
     * Configure global model behavior
     */
    protected function configureModelBehavior(): void
    {
        // Disable mass assignment protection for all models
        Model::unguard();

        // Automatically eager load relationships for all models
        Model::automaticallyEagerLoadRelationships();
    }
}
