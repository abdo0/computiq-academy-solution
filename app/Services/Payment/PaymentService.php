<?php

namespace App\Services\Payment;

use App\Models\Campaign;
use App\Models\Donor;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected PaymentProcessor $processor,
        protected PaymentCalculator $calculator
    ) {}

    /**
     * Initiate a payment
     */
    public function initiatePayment(
        float $amount,
        ?Campaign $campaign = null,
        PaymentGateway $gateway,
        ?Donor $donor = null,
        array $donorData = [],
        array $metadata = []
    ): array {
        try {
            // Create transaction
            $result = $this->processor->processDonation(
                $amount,
                $campaign,
                $gateway,
                $donor,
                $donorData,
                $metadata
            );

            // Extract transaction from result array
            $transaction = $result['transaction'];

            // Get gateway implementation
            $gatewayService = $this->getGatewayService($gateway);

            // Initiate payment with gateway
            $gatewayResponse = $gatewayService->initiatePayment($transaction, [
                'amount' => $transaction->total_amount,
                'currency' => settings('currency', 'USD'),
                'description' => $campaign ? "Donation to: {$campaign->title}" : "General Donation",
                'return_url' => route('payment.callback', ['transactionRef' => $transaction->transaction_ref]),
                'callback_url' => route('payment.webhook', ['gateway' => $gateway->code]),
            ]);

            // Update transaction with gateway response
            $transaction->update([
                'gateway_transaction_id' => $gatewayResponse['transaction_id'] ?? null,
                'gateway_response' => $gatewayResponse,
                'status' => 'processing',
            ]);

            // Check if gateway returned payment_url
            $paymentUrl = $gatewayResponse['payment_url'] ?? null;
            
            if (! $paymentUrl && isset($gatewayResponse['message'])) {
                // Gateway didn't return payment_url - might not be implemented
                throw new \Exception(__('Payment gateway ":gateway" is not fully configured. Please contact support.', [
                    'gateway' => $gateway->name ?? $gateway->code,
                ]));
            }

            return [
                'success' => true,
                'transaction' => $transaction,
                'payment_url' => $paymentUrl,
                'gateway_response' => $gatewayResponse,
            ];
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'campaign_id' => $campaign?->id,
                'gateway_id' => $gateway->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Transaction $transaction): array
    {
        try {
            $gateway = $transaction->paymentGateway;
            $gatewayService = $this->getGatewayService($gateway);

            // لا يمكن التحقق بدون gateway_transaction_id
            if (blank($transaction->gateway_transaction_id)) {
                Log::warning('Payment verification skipped: missing gateway_transaction_id', [
                    'transaction_id' => $transaction->id,
                    'transaction_ref' => $transaction->transaction_ref,
                    'gateway' => $gateway->code ?? null,
                ]);

                return [
                    'success' => false,
                    'transaction' => $transaction,
                    'status' => $transaction->status->value ?? 'pending',
                    'error' => __('Cannot verify payment: missing gateway transaction reference.'),
                ];
            }

            $verification = $gatewayService->verifyPayment((string) $transaction->gateway_transaction_id);

            // Update transaction based on verification
            if ($verification['status'] === 'completed') {
                $this->processor->updateStatus($transaction, 'completed', [
                    'gateway_response' => $verification,
                ]);
                
                return [
                    'success' => true,
                    'transaction' => $transaction->fresh(),
                    'status' => $verification['status'],
                ];
            } elseif ($verification['status'] === 'failed') {
                $this->processor->updateStatus($transaction, 'failed', [
                    'failure_reason' => $verification['message'] ?? __('Payment verification failed'),
                    'gateway_response' => $verification,
                ]);
                
                return [
                    'success' => false,
                    'transaction' => $transaction->fresh(),
                    'status' => $verification['status'],
                    'error' => $verification['message'] ?? __('Payment verification failed'),
                ];
            }

            // Pending or other status
            return [
                'success' => false,
                'transaction' => $transaction->fresh(),
                'status' => $verification['status'] ?? 'pending',
                'error' => $verification['message'] ?? __('Payment is still pending'),
            ];
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook/callback
     */
    public function processWebhook(PaymentGateway $gateway, array $data): Transaction
    {
        try {
            $gatewayService = $this->getGatewayService($gateway);
            $transaction = $gatewayService->processCallback($data);

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'gateway_id' => $gateway->id,
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Transaction $transaction, ?float $amount = null): array
    {
        try {
            $gateway = $transaction->paymentGateway;
            $gatewayService = $this->getGatewayService($gateway);

            $refundResponse = $gatewayService->refund($transaction, $amount);

            // Update transaction
            $this->processor->processRefund($transaction, $amount);

            return [
                'success' => true,
                'transaction' => $transaction->fresh(),
                'refund_response' => $refundResponse,
            ];
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check gateway health
     */
    public function checkGatewayHealth(PaymentGateway $gateway): array
    {
        try {
            $gatewayService = $this->getGatewayService($gateway);
            $healthCheck = $gatewayService->checkHealth();

            return [
                'success' => $healthCheck['status'] === 'healthy',
                'status' => $healthCheck['status'],
                'message' => $healthCheck['message'],
                'details' => $healthCheck['details'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Gateway health check failed', [
                'error' => $e->getMessage(),
                'gateway_id' => $gateway->id,
            ]);

            return [
                'success' => false,
                'status' => 'unhealthy',
                'message' => __('Health check failed: :error', ['error' => $e->getMessage()]),
                'details' => [],
            ];
        }
    }

    /**
     * Get gateway service implementation
     */
    protected function getGatewayService(PaymentGateway $gateway): PaymentGatewayInterface
    {
        $gatewayClass = match ($gateway->code) {
            'qi-card' => \App\Services\Payment\Gateways\QiCardGateway::class,
            'zaincash' => \App\Services\Payment\Gateways\ZainCashGateway::class,
            'fastpay' => \App\Services\Payment\Gateways\FastPayGateway::class,
            'nasaq' => \App\Services\Payment\Gateways\NasaqGateway::class,
            'asia-hawala' => \App\Services\Payment\Gateways\AsiaHawalaGateway::class,
            'tap_payments' => \App\Services\Payment\Gateways\TapPaymentsGateway::class,
            default => throw new \Exception(__('Gateway implementation not found for: :gateway', ['gateway' => $gateway->code])),
        };

        $service = app($gatewayClass);
        $service->setGateway($gateway);

        return $service;
    }
}
