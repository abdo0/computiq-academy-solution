<?php

namespace App\Console\Commands;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:verify-pending 
                            {--minutes=60 : Only verify transactions created within the last N minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify pending and processing transactions with payment gateways';

    public function __construct(
        protected PaymentService $paymentService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $this->info(__('Starting verification of pending transactions...'));

        // Get pending and processing transactions created within the last N minutes
        // Only verify transactions that have a gateway_transaction_id (payment was initiated)
        $transactions = Transaction::query()
            ->whereIn('status', [TransactionStatus::PENDING, TransactionStatus::PROCESSING])
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->whereNotNull('gateway_transaction_id')
            ->whereHas('paymentGateway') // Ensure payment gateway exists
            ->with(['paymentGateway', 'donation.campaign'])
            ->orderBy('created_at', 'desc') // Process newest first
            ->get();

        if ($transactions->isEmpty()) {
            $this->info(__('No pending transactions found to verify.'));

            return Command::SUCCESS;
        }

        $this->info(__('Found :count transactions to verify.', ['count' => $transactions->count()]));

        $verified = 0;
        $failed = 0;
        $stillPending = 0;
        $errors = 0;

        $this->withProgressBar($transactions, function (Transaction $transaction) use (&$verified, &$failed, &$stillPending, &$errors) {
            try {
                $verification = $this->paymentService->verifyPayment($transaction);

                if ($verification['success']) {
                    $verified++;
                    $this->newLine();
                    $this->line("✓ Transaction {$transaction->transaction_ref}: " . __('Verified and completed'));
                } elseif (isset($verification['status']) && $verification['status'] === 'failed') {
                    $failed++;
                    $this->newLine();
                    $this->warn("✗ Transaction {$transaction->transaction_ref}: " . __('Verification failed'));
                } else {
                    $stillPending++;
                    // Transaction is still pending, no action needed
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("✗ Transaction {$transaction->transaction_ref}: " . $e->getMessage());

                Log::error('Transaction verification error', [
                    'transaction_id' => $transaction->id,
                    'transaction_ref' => $transaction->transaction_ref,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });

        $this->newLine(2);
        $this->info(__('Verification completed:'));
        $this->table(
            ['Status', 'Count'],
            [
                [__('Verified & Completed'), $verified],
                [__('Failed'), $failed],
                [__('Still Pending'), $stillPending],
                [__('Errors'), $errors],
                [__('Total'), $transactions->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
