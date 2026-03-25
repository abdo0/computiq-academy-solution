<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Update wallet available balances daily
Schedule::command('wallets:update-available-balances')
    ->daily()
    ->at('00:00');

// Verify pending transactions every 15 minutes
// Verifies transactions created in the last hour
Schedule::command('transactions:verify-pending', [
    '--minutes' => 60,
])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
