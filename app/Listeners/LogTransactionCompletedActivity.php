<?php

namespace App\Listeners;

use App\Events\TransactionCompleted;

class LogTransactionCompletedActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionCompleted $event): void
    {
        $event->transaction->logActivity(__('has completed a transaction'), $event->user);
    }
}
