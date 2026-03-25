<?php

namespace App\Services\Payment\Gateways;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QiCardGateway extends BaseGateway
{
    protected function getRequiredConfigKeys(): array
    {
        return ['api_key', 'api_secret', 'merchant_id'];
    }

    public function checkHealth(): array
    {
        try {
            // First check configuration
            $configCheck = parent::checkHealth();
            if ($configCheck['status'] !== 'healthy') {
                return $configCheck;
            }

            // Try to make a test API call (ping/status endpoint if available)
            $apiKey = $this->getConfigValue('api_key');
            $endpoint = $this->getConfigValue('endpoint', 'https://api.qicard.com');

            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                ])
                ->get("{$endpoint}/health");

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'message' => __('Gateway is healthy and responding'),
                    'details' => [
                        'response_time' => $response->transferStats?->getTransferTime() ?? null,
                        'status_code' => $response->status(),
                    ],
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => __('Gateway API returned error: :status', ['status' => $response->status()]),
                'details' => [
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Qi Card health check failed', [
                'error' => $e->getMessage(),
                'gateway_id' => $this->gateway->id,
            ]);

            return [
                'status' => 'unhealthy',
                'message' => __('Health check failed: :error', ['error' => $e->getMessage()]),
                'details' => [],
            ];
        }
    }

    public function initiatePayment(Transaction $transaction, array $data = []): array
    {
        try {
            $apiKey = $this->getConfigValue('api_key');
            $apiSecret = $this->getConfigValue('api_secret');
            $merchantId = $this->getConfigValue('merchant_id');
            $endpoint = $this->getConfigValue('endpoint', 'https://api.qicard.com/payment');

            // Prepare payment request
            $payload = [
                'merchant_id' => $merchantId,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? (settings('currency', 'USD')),
                'order_id' => $transaction->transaction_ref,
                'description' => $data['description'] ?? "Donation #{$transaction->transaction_ref}",
                'return_url' => $data['return_url'],
                'callback_url' => $data['callback_url'],
                'customer' => [
                    'name' => $transaction->donation?->donor?->name ?? 'Anonymous',
                    'email' => $transaction->donation?->donor?->email ?? null,
                    'phone' => $transaction->donation?->donor?->phone ?? null,
                ],
            ];

            $requestUrl = "{$endpoint}/initiate";
            $requestHeaders = [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ];

            // ============================================
            // LOG: عملية إنشاء عملية دفع - Request Details
            // ============================================
            Log::info('QiCard - عملية إنشاء عملية دفع (Initiate Payment) - Request Details', [
                'operation' => 'initiate_payment',
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'request_url' => $requestUrl,
                'request_method' => 'POST',
                'request_headers' => $requestHeaders,
                'request_body' => $payload,
                'gateway_config' => [
                    'merchant_id' => $merchantId,
                    'endpoint' => $endpoint,
                ],
                'transaction_data' => [
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? (settings('currency', 'USD')),
                    'description' => $data['description'] ?? "Donation #{$transaction->transaction_ref}",
                    'return_url' => $data['return_url'] ?? null,
                    'callback_url' => $data['callback_url'] ?? null,
                ],
                'timestamp' => now()->toIso8601String(),
            ]);

            // Make API request
            $response = Http::withHeaders($requestHeaders)
                ->post($requestUrl, $payload);

            // ============================================
            // LOG: عملية إنشاء عملية دفع - Response Details
            // ============================================
            $responseData = $response->json();
            $responseHeaders = $response->headers();
            
            Log::info('QiCard - عملية إنشاء عملية دفع (Initiate Payment) - Response Details', [
                'operation' => 'initiate_payment',
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'response_status_code' => $response->status(),
                'response_headers' => $responseHeaders,
                'response_body' => $responseData,
                'response_body_raw' => $response->body(),
                'is_successful' => $response->successful(),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                return [
                    'status' => 'processing',
                    'transaction_id' => $responseData['transaction_id'] ?? null,
                    'payment_url' => $responseData['payment_url'] ?? null,
                    'gateway_response' => $responseData,
                ];
            }

            Log::error('QiCard - عملية إنشاء عملية دفع (Initiate Payment) - HTTP Error', [
                'operation' => 'initiate_payment',
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_data' => $responseData,
                'request_url' => $requestUrl,
                'request_body' => $payload,
            ]);

            throw new \Exception('Qi Card API error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('QiCard - عملية إنشاء عملية دفع (Initiate Payment) - Exception', [
                'operation' => 'initiate_payment',
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref ?? null,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function verifyPayment(string $transactionId): array
    {
        try {
            $apiKey = $this->getConfigValue('api_key');
            $endpoint = $this->getConfigValue('endpoint', 'https://api.qicard.com/payment');

            $requestUrl = "{$endpoint}/verify/{$transactionId}";
            $requestHeaders = [
                'Authorization' => "Bearer {$apiKey}",
            ];

            // ============================================
            // LOG: عملية التحقق من عملية دفع - Request Details
            // ============================================
            Log::info('QiCard - عملية التحقق من عملية دفع (Verify Payment) - Request Details', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'request_url' => $requestUrl,
                'request_method' => 'GET',
                'request_headers' => $requestHeaders,
                'gateway_config' => [
                    'endpoint' => $endpoint,
                ],
                'timestamp' => now()->toIso8601String(),
            ]);

            $response = Http::withHeaders($requestHeaders)
                ->get($requestUrl);

            // ============================================
            // LOG: عملية التحقق من عملية دفع - Response Details
            // ============================================
            $responseData = $response->json();
            $responseHeaders = $response->headers();
            
            Log::info('QiCard - عملية التحقق من عملية دفع (Verify Payment) - Response Details', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'response_status_code' => $response->status(),
                'response_headers' => $responseHeaders,
                'response_body' => $responseData,
                'response_body_raw' => $response->body(),
                'is_successful' => $response->successful(),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                $data = $responseData;

                return [
                    'status' => $data['status'] === 'success' ? 'completed' : 'failed',
                    'transaction_id' => $transactionId,
                    'message' => $data['message'] ?? null,
                    'gateway_response' => $data,
                ];
            }

            Log::error('QiCard - عملية التحقق من عملية دفع (Verify Payment) - HTTP Error', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'response_data' => $responseData,
                'request_url' => $requestUrl,
            ]);

            return [
                'status' => 'failed',
                'transaction_id' => $transactionId,
                'message' => 'Verification failed',
            ];
        } catch (\Exception $e) {
            Log::error('QiCard - عملية التحقق من عملية دفع (Verify Payment) - Exception', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'failed',
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function processCallback(array $data): Transaction
    {
        // Verify webhook signature
        $signature = $data['signature'] ?? null;
        $expectedSignature = $this->generateSignature($data);

        if ($signature !== $expectedSignature) {
            throw new \Exception('Invalid webhook signature');
        }

        // Find transaction
        $transaction = Transaction::where('gateway_transaction_id', $data['transaction_id'])
            ->orWhere('transaction_ref', $data['order_id'])
            ->firstOrFail();

        // Update transaction status
        if ($data['status'] === 'success') {
            $transaction->update([
                'status' => 'completed',
                'gateway_response' => $data,
            ]);

            // Update campaign raised amount via donation
            if ($transaction->donation) {
                $transaction->donation->campaign->increment('raised_amount', $transaction->donation->amount);
            }
        } else {
            $transaction->update([
                'status' => 'failed',
                'failure_reason' => $data['message'] ?? 'Payment failed',
                'gateway_response' => $data,
            ]);
        }

        return $transaction->fresh();
    }

    protected function generateSignature(array $data): string
    {
        $apiSecret = $this->getConfigValue('api_secret');
        $signatureString = $data['transaction_id'].$data['amount'].$data['order_id'].$apiSecret;

        return hash('sha256', $signatureString);
    }
}
