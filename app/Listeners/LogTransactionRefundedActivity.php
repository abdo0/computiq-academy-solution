<?php

namespace App\Listeners;

use App\Events\TransactionRefunded;

class LogTransactionRefundedActivity
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
    public function handle(TransactionRefunded $event): void
    {
        $event->transaction->logRefunded($event->user);
    }
}
