<?php

namespace App\Listeners;

use App\Events\TransactionCanceled;

class LogTransactionCanceledActivity
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
    public function handle(TransactionCanceled $event): void
    {
        $event->transaction->logCanceled($event->user);
    }
}
