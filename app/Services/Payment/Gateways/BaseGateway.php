<?php

namespace App\Services\Payment\Gateways;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseGateway implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->gateway->configuration ?? [];
    }

    protected function getConfigValue(string $key, $default = null)
    {
        return $this->getConfig()[$key] ?? $default;
    }

    /**
     * Default implementation - override in specific gateways
     */
    public function initiatePayment(Transaction $transaction, array $data = []): array
    {
        Log::warning('Gateway initiatePayment not implemented', [
            'gateway' => $this->gateway->code,
            'transaction_id' => $transaction->id,
        ]);

        return [
            'status' => 'pending',
            'message' => 'Payment initiation not implemented for this gateway',
        ];
    }

    public function verifyPayment(string $transactionId): array
    {
        Log::warning('Gateway verifyPayment not implemented', [
            'gateway' => $this->gateway->code,
            'transaction_id' => $transactionId,
        ]);

        return [
            'status' => 'pending',
            'message' => 'Payment verification not implemented for this gateway',
        ];
    }

    public function processCallback(array $data): Transaction
    {
        Log::warning('Gateway processCallback not implemented', [
            'gateway' => $this->gateway->code,
            'data' => $data,
        ]);

        throw new \Exception('Callback processing not implemented for this gateway');
    }

    public function refund(Transaction $transaction, ?float $amount = null): array
    {
        Log::warning('Gateway refund not implemented', [
            'gateway' => $this->gateway->code,
            'transaction_id' => $transaction->id,
        ]);

        return [
            'status' => 'failed',
            'message' => 'Refund not implemented for this gateway',
        ];
    }

    public function checkHealth(): array
    {
        // Default implementation - check if configuration exists
        $config = $this->getConfig();

        if (empty($config)) {
            return [
                'status' => 'unhealthy',
                'message' => __('Gateway configuration is missing'),
                'details' => [],
            ];
        }

        // Check required configuration keys
        $requiredKeys = $this->getRequiredConfigKeys();
        $missingKeys = [];

        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (! empty($missingKeys)) {
            return [
                'status' => 'unhealthy',
                'message' => __('Missing required configuration: :keys', ['keys' => implode(', ', $missingKeys)]),
                'details' => ['missing_keys' => $missingKeys],
            ];
        }

        return [
            'status' => 'healthy',
            'message' => __('Gateway configuration is valid'),
            'details' => [],
        ];
    }

    /**
     * Get required configuration keys for this gateway
     * Override in specific gateway implementations
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['api_key', 'api_secret'];
    }
}
