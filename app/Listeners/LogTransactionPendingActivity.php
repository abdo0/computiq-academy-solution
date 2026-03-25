<?php

namespace App\Listeners;

use App\Events\TransactionPending;

class LogTransactionPendingActivity
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
    public function handle(TransactionPending $event): void
    {
        $event->transaction->logActivity(__('has set transaction to pending'), $event->user);
    }
}
