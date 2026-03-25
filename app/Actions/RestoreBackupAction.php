<?php

namespace App\Actions;

use App\Models\Backup;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RestoreBackupAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'restore';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Restore'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(__('Restore Backup'))
            ->modalSubheading(__('Are you sure you would like to restore this backup? This action will overwrite your current database. This action cannot be undone.'))
            ->modalSubmitActionLabel(__('Yes, Restore'))
            ->action(function (Backup $record) {
                $dbConfig = Config::get('database.connections.mysql');
                $tempDisk = 'local';
                $tempBackupPath = 'temp/backups-table-dump.sql';

                try {
                    // 1. Dump the current `backups` table
                    $mysqldumpPath = Config::get('backup.mysql.mysqldump_path', 'mysqldump');
                    $dumpCommand = [$mysqldumpPath, '-u', $dbConfig['username'], '-h', $dbConfig['host'], '-P', $dbConfig['port'], $dbConfig['database'], 'backups'];
                    if (! empty($dbConfig['password'])) {
                        $dumpCommand[] = '-p'.$dbConfig['password'];
                    }

                    $dumpProcess = new Process($dumpCommand);
                    $dumpProcess->setTimeout(null)->run();
                    if (! $dumpProcess->isSuccessful()) {
                        throw new Exception('Failed to dump current backups table: '.$dumpProcess->getErrorOutput());
                    }
                    Storage::disk($tempDisk)->put($tempBackupPath, $dumpProcess->getOutput());

                    // 2. Restore the full database
                    $mysqlPath = Config::get('backup.mysql.mysql_path', 'mysql');
                    $restoreCommand = [$mysqlPath, '-u', $dbConfig['username'], '-h', $dbConfig['host'], '-P', $dbConfig['port'], $dbConfig['database']];
                    if (! empty($dbConfig['password'])) {
                        $restoreCommand[] = '-p'.$dbConfig['password'];
                    }

                    $restoreProcess = new Process($restoreCommand);
                    $restoreProcess->setInput(Storage::disk($record->disk)->get($record->path));
                    $restoreProcess->setTimeout(null)->run();
                    if (! $restoreProcess->isSuccessful()) {
                        throw new Exception('Database restore failed: '.$restoreProcess->getErrorOutput());
                    }

                    // 3. Restore the `backups` table over the new database
                    $importProcess = new Process($restoreCommand);
                    $importProcess->setInput(Storage::disk($tempDisk)->get($tempBackupPath));
                    $importProcess->setTimeout(null)->run();
                    Storage::disk($tempDisk)->delete($tempBackupPath);

                    if (! $importProcess->isSuccessful()) {
                        Notification::make()->title(__('Backup restored, but failed to preserve the latest backups list.'))->warning()->body($importProcess->getErrorOutput())->send();

                        return;
                    }

                    Notification::make()->title(__('Backup restored successfully'))->success()->send();

                } catch (Exception $e) {
                    if (Storage::disk($tempDisk)->exists($tempBackupPath)) {
                        Storage::disk($tempDisk)->delete($tempBackupPath);
                    }
                    Notification::make()->title(__('There was an error restoring the backup.'))->body($e->getMessage())->danger()->send();
                }
            });
    }
}
