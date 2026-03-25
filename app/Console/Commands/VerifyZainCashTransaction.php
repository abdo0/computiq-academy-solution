<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Exception;
use Illuminate\Console\Command;

class VerifyZainCashTransaction extends Command
{
    protected $signature = 'zaincash:verify {--transaction_ref=} {--gateway_transaction_id=}';

    protected $description = 'Verify a ZainCash transaction via the gateway API';

    public function __construct(public PaymentService $paymentService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $transactionRef = $this->option('transaction_ref');
        $gatewayTransactionId = $this->option('gateway_transaction_id');

        if (! $transactionRef && ! $gatewayTransactionId) {
            $this->error('Provide --transaction_ref or --gateway_transaction_id');

            return self::FAILURE;
        }

        $transaction = Transaction::query()
            ->when($transactionRef, fn ($query) => $query->where('transaction_ref', $transactionRef))
            ->when(
                $gatewayTransactionId,
                fn ($query) => $query->orWhere('gateway_transaction_id', $gatewayTransactionId)
            )
            ->latest('Id')
            ->first();

        if (! $transaction) {
            $this->error('Transaction not found');

            return self::FAILURE;
        }

        $this->info('Verifying transaction: '.$transaction->transaction_ref);

        try {
            $result = $this->paymentService->verifyPayment($transaction);

            $this->line('Status: '.($result['status'] ?? 'unknown'));
            $this->line('Gateway Transaction ID: '.($transaction->gateway_transaction_id ?? 'N/A'));
            $this->line('Message: '.($result['error'] ?? $result['message'] ?? ''));

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Verification failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

