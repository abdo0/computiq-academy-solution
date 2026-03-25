<?php

namespace App\Listeners;

use App\Models\Backup;
use Spatie\Backup\Events\BackupWasSuccessful;

class LogSuccessfulBackup
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
    public function handle(BackupWasSuccessful $event): void
    {
        $disk = $event->backupDestination->diskName();
        $path = $event->backupDestination->path();

        Backup::create([
            'disk' => $disk,
            'path' => $path,
            'name' => basename($path),
            'size' => $event->backupDestination->size(),
        ]);
    }
}
