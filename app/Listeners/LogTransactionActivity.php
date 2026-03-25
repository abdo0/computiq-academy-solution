<?php

namespace App\Listeners;

use App\Events\TransactionCreated;

class LogTransactionActivity
{
    /**
     * Handle the event.
     */
    public function handle(TransactionCreated $event): void
    {
        $event->transaction->logCreated($event->user);
    }
}
