<?php

namespace App\Console\Commands;

use App\Enums\ActivityAction;
use App\Models\Transaction;
use App\Models\TransactionActivityLog;
use Illuminate\Console\Command;

class CreateTransactionLogsForExistingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction-logs:create-for-existing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create activity logs for existing transactions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating activity logs for existing transactions...');

        $transactions = Transaction::orderBy('created_at')->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions found.');

            return self::SUCCESS;
        }

        $this->info("Found {$transactions->count()} transactions.");

        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        $created = 0;
        $skipped = 0;

        foreach ($transactions as $transaction) {
            // Check if log already exists for this transaction creation
            $exists = TransactionActivityLog::where('transaction_id', $transaction->id)
                ->where('action', ActivityAction::CREATED)
                ->whereDate('created_at', $transaction->created_at->toDateString())
                ->exists();

            if ($exists) {
                $skipped++;
                $bar->advance();

                continue;
            }

            // Create a log entry for the transaction creation
            TransactionActivityLog::create([
                'transaction_id' => $transaction->id,
                'action' => ActivityAction::CREATED,
                'description' => __('Transaction :ref was created', ['ref' => $transaction->transaction_ref]),
                'properties' => [
                    'transaction_ref' => $transaction->transaction_ref,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status->value,
                    'type' => $transaction->type->value,
                ],
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->created_at,
            ]);

            // If transaction was updated, create update log
            if ($transaction->updated_at && $transaction->updated_at->gt($transaction->created_at)) {
                TransactionActivityLog::create([
                    'transaction_id' => $transaction->id,
                    'action' => ActivityAction::UPDATED,
                    'description' => __('Transaction :ref was updated', ['ref' => $transaction->transaction_ref]),
                    'properties' => [
                        'transaction_ref' => $transaction->transaction_ref,
                        'current_status' => $transaction->status->value,
                    ],
                    'ip_address' => null,
                    'user_agent' => null,
                    'created_at' => $transaction->updated_at,
                    'updated_at' => $transaction->updated_at,
                ]);
            }

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Logs creation completed!');
        $this->info("Created: {$created}");
        $this->info("Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
