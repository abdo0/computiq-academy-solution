<?php

namespace App\Listeners;

use App\Events\TransactionFailed;

class LogTransactionFailedActivity
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
    public function handle(TransactionFailed $event): void
    {
        $event->transaction->logActivity(__('has failed a transaction'), $event->user);
    }
}
