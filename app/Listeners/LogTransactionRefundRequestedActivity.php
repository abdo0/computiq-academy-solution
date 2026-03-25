<?php

namespace App\Listeners;

use App\Events\TransactionRefundRequested;

class LogTransactionRefundRequestedActivity
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
    public function handle(TransactionRefundRequested $event): void
    {
        $event->transaction->logActivity(__('has requested a transaction refund'), $event->user);
    }
}
