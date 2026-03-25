<?php

namespace App\Actions;

use App\Models\Backup;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class CreateBackupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Create Backup'))
            ->action(function () {
                try {
                    $dbConfig = Config::get('database.connections.mysql');

                    $backupDisk = 'local';
                    $backupFolder = 'backups';
                    Storage::disk($backupDisk)->makeDirectory($backupFolder);

                    $filename = 'backup-'.Carbon::now()->format('Y-m-d-H-i-s').'.sql';
                    $path = "{$backupFolder}/{$filename}";

                    $mysqldumpPath = Config::get('backup.mysql.mysqldump_path', 'mysqldump');
                    $command = [$mysqldumpPath, '-u', $dbConfig['username'], '-h', $dbConfig['host'], '-P', $dbConfig['port'], $dbConfig['database']];
                    if (! empty($dbConfig['password'])) {
                        $command[] = '-p'.$dbConfig['password'];
                    }

                    $process = new Process($command);
                    $process->setTimeout(null)->run();

                    if (! $process->isSuccessful()) {
                        throw new Exception($process->getErrorOutput());
                    }

                    Storage::disk($backupDisk)->put($path, $process->getOutput());

                    Backup::create([
                        'name' => $filename,
                        'disk' => $backupDisk,
                        'path' => $path,
                        'size' => Storage::disk($backupDisk)->size($path),
                    ]);

                    Notification::make()
                        ->title(__('Backup created successfully'))
                        ->success()
                        ->send();
                } catch (Exception $e) {
                    Notification::make()
                        ->title(__('There was an error creating the backup.'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
