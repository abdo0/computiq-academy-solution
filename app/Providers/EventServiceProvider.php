<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\TransactionCreated::class => [
            \App\Listeners\LogTransactionActivity::class,
        ],
        \App\Events\TransactionPending::class => [
            \App\Listeners\LogTransactionPendingActivity::class,
        ],
        \App\Events\TransactionCompleted::class => [
            \App\Listeners\LogTransactionCompletedActivity::class,
        ],
        \App\Events\TransactionFailed::class => [
            \App\Listeners\LogTransactionFailedActivity::class,
        ],
        \App\Events\TransactionCanceled::class => [
            \App\Listeners\LogTransactionCanceledActivity::class,
        ],
        \App\Events\TransactionRefundRequested::class => [
            \App\Listeners\LogTransactionRefundRequestedActivity::class,
        ],
        \App\Events\TransactionRefunded::class => [
            \App\Listeners\LogTransactionRefundedActivity::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register model observers
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
