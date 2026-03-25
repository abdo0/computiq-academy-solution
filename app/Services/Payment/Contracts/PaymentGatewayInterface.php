<?php

namespace App\Services\Payment\Contracts;

use App\Models\PaymentGateway;
use App\Models\Transaction;

interface PaymentGatewayInterface
{
    /**
     * Initialize payment with gateway
     */
    public function initiatePayment(Transaction $transaction, array $data = []): array;

    /**
     * Verify payment status with gateway
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Process payment callback/webhook
     */
    public function processCallback(array $data): Transaction;

    /**
     * Refund a transaction
     */
    public function refund(Transaction $transaction, ?float $amount = null): array;

    /**
     * Get gateway configuration
     */
    public function getConfig(): array;

    /**
     * Set payment gateway model
     */
    public function setGateway(PaymentGateway $gateway): self;

    /**
     * Check gateway health/connectivity
     */
    public function checkHealth(): array;
}
