<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Context;

class DatabaseNotificationObserver
{
    /**
     * Handle the DatabaseNotification "creating" event.
     */
    public function creating(DatabaseNotification $databaseNotification): void
    {
        // Branch ID assignment removed
    }

    /**
     * Handle the DatabaseNotification "created" event.
     */
    public function created(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "updated" event.
     */
    public function updated(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "deleted" event.
     */
    public function deleted(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "restored" event.
     */
    public function restored(DatabaseNotification $databaseNotification): void
    {
        //
    }

    /**
     * Handle the DatabaseNotification "force deleted" event.
     */
    public function forceDeleted(DatabaseNotification $databaseNotification): void
    {
        //
    }
}
