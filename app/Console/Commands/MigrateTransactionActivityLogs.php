<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Transaction;
use App\Models\TransactionActivityLog;
use Illuminate\Console\Command;

class MigrateTransactionActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction-logs:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing ActivityLog entries for Transaction model to TransactionActivityLog';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting migration of Transaction activity logs...');

        $activityLogs = ActivityLog::where('model_type', Transaction::class)
            ->orderBy('created_at')
            ->get();

        if ($activityLogs->isEmpty()) {
            $this->info('No activity logs found for Transaction model.');

            return self::SUCCESS;
        }

        $this->info("Found {$activityLogs->count()} activity logs to migrate.");

        $bar = $this->output->createProgressBar($activityLogs->count());
        $bar->start();

        $migrated = 0;
        $skipped = 0;

        foreach ($activityLogs as $activityLog) {
            // Check if already migrated
            $exists = TransactionActivityLog::where('transaction_id', $activityLog->model_id)
                ->where('action', $activityLog->action)
                ->where('created_at', $activityLog->created_at)
                ->exists();

            if ($exists) {
                $skipped++;
                $bar->advance();

                continue;
            }

            // Get the transaction
            $transaction = Transaction::find($activityLog->model_id);

            if (! $transaction) {
                $skipped++;
                $bar->advance();

                continue;
            }

            // Create TransactionActivityLog entry
            TransactionActivityLog::create([
                'transaction_id' => $transaction->id,
                'action' => $activityLog->action,
                'description' => $activityLog->description,
                'properties' => $activityLog->properties ?? [],
                'ip_address' => $activityLog->ip_address,
                'user_agent' => $activityLog->user_agent,
                'created_at' => $activityLog->created_at,
                'updated_at' => $activityLog->updated_at,
            ]);

            $migrated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Migration completed!');
        $this->info("Migrated: {$migrated}");
        $this->info("Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
