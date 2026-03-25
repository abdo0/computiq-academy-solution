<?php

namespace App\Services\Payment\Gateways;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TapPaymentsGateway extends BaseGateway
{
    protected function getRequiredConfigKeys(): array
    {
        return ['secret_key'];
    }

    public function initiatePayment(Transaction $transaction, array $data = []): array
    {
        try {
            $secretKey = $this->getConfigValue('secret_key');
            $apiUrl = $this->getConfigValue('api_url', 'https://api.tap.company/v2');

            if (! $secretKey) {
                throw new \Exception(__('Tap Payments secret key is not configured'));
            }

            // Prepare payment request for Tap Payments
            // Tap Payments requires a source_id for charges
            // Use configured source_id or default to 'src_all' (allows all payment methods)
            $sourceId = $this->getConfigValue('source_id');
            
            // If no source_id configured, use 'src_all' as default
            if (! $sourceId) {
                $sourceId = 'src_all';
                Log::info('Tap Payments using default source_id: src_all', [
                    'transaction_id' => $transaction->id,
                    'note' => 'Using src_all to allow all payment methods. Configure source_id in gateway settings for specific payment method.',
                ]);
            }
            
            $payload = [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? strtoupper(settings('currency', 'USD')),
                'threeDSecure' => true,
                'save_card' => false,
                'description' => $data['description'] ?? "Donation #{$transaction->transaction_ref}",
                'statement_descriptor' => 'Donation',
                'source' => [
                    'id' => $sourceId,
                ],
                'metadata' => [
                    'transaction_ref' => $transaction->transaction_ref,
                    'transaction_id' => $transaction->id,
                    'donation_id' => $transaction->donation_id,
                ],
                'reference' => [
                    'transaction' => $transaction->transaction_ref,
                ],
                'receipt' => [
                    'email' => true,
                    'sms' => true,
                ],
                'customer' => [
                    'first_name' => $transaction->donation?->donor?->name ?? 'Anonymous',
                    'email' => $transaction->donation?->donor?->email ?? null,
                    'phone' => [
                        'country_code' => '964',
                        'number' => $transaction->donation?->donor?->phone ?? null,
                    ],
                ],
                'redirect' => [
                    'url' => $data['return_url'],
                ],
                'post' => [
                    'url' => $data['callback_url'],
                ],
            ];

            // Remove null values
            $payload = array_filter($payload, function ($value) {
                return $value !== null;
            });

            $requestUrl = "{$apiUrl}/charges";
            $requestHeaders = [
                'Authorization' => "Bearer {$secretKey}",
                'Content-Type' => 'application/json',
            ];

            // ============================================
            // LOG: عملية إنشاء عملية دفع - Request Details
            // ============================================
            Log::info('TapPayments - عملية إنشاء عملية دفع (Initiate Payment) - Request Details', [
                'operation' => 'initiate_payment',
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'request_url' => $requestUrl,
                'request_method' => 'POST',
                'request_headers' => $requestHeaders,
                'request_body' => $payload,
                'gateway_config' => [
                    'api_url' => $apiUrl,
                    'source_id' => $sourceId,
                ],
                'transaction_data' => [
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? strtoupper(settings('currency', 'USD')),
                    'description' => $data['description'] ?? "Donation #{$transaction->transaction_ref}",
                    'return_url' => $data['return_url'] ?? null,
                    'callback_url' => $data['callback_url'] ?? null,
                ],
                'timestamp' => now()->toIso8601String(),
            ]);

            // Make API request to Tap Payments
            $response = Http::timeout(30)
                ->withHeaders($requestHeaders)
                ->post($requestUrl, $payload);

            // ============================================
            // LOG: عملية إنشاء عملية دفع - Response Details
            // ============================================
            $responseData = $response->json();
            $responseHeaders = $response->headers();
            
            Log::info('TapPayments - عملية إنشاء عملية دفع (Initiate Payment) - Response Details', [
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
                    'status' => 'pending',
                    'transaction_id' => $responseData['id'] ?? null,
                    'payment_url' => $responseData['transaction']['url'] ?? $responseData['url'] ?? null,
                    'message' => __('Payment initiated successfully'),
                    'gateway_response' => $responseData,
                ];
            }

            // Handle error response
            $errorData = $responseData;
            $errorMessage = $errorData['message'] ?? __('Payment initiation failed');
            
            Log::error('TapPayments - عملية إنشاء عملية دفع (Initiate Payment) - HTTP Error', [
                'operation' => 'initiate_payment',
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'status_code' => $response->status(),
                'error_message' => $errorMessage,
                'response_data' => $errorData,
                'response_body' => $response->body(),
                'request_url' => $requestUrl,
                'request_body' => $payload,
            ]);

            throw new \Exception($errorMessage);
        } catch (\Exception $e) {
            Log::error('TapPayments - عملية إنشاء عملية دفع (Initiate Payment) - Exception', [
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
            $secretKey = $this->getConfigValue('secret_key');
            $apiUrl = $this->getConfigValue('api_url', 'https://api.tap.company/v2');

            if (! $secretKey) {
                throw new \Exception(__('Tap Payments secret key is not configured'));
            }

            $requestUrl = "{$apiUrl}/charges/{$transactionId}";
            $requestHeaders = [
                'Authorization' => "Bearer {$secretKey}",
                'Content-Type' => 'application/json',
            ];

            // ============================================
            // LOG: عملية التحقق من عملية دفع - Request Details
            // ============================================
            Log::info('TapPayments - عملية التحقق من عملية دفع (Verify Payment) - Request Details', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'request_url' => $requestUrl,
                'request_method' => 'GET',
                'request_headers' => $requestHeaders,
                'gateway_config' => [
                    'api_url' => $apiUrl,
                ],
                'timestamp' => now()->toIso8601String(),
            ]);

            // Get charge details from Tap Payments API
            $response = Http::timeout(30)
                ->withHeaders($requestHeaders)
                ->get($requestUrl);

            // ============================================
            // LOG: عملية التحقق من عملية دفع - Response Details
            // ============================================
            $responseData = $response->json();
            $responseHeaders = $response->headers();
            
            Log::info('TapPayments - عملية التحقق من عملية دفع (Verify Payment) - Response Details', [
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
                $chargeData = $responseData;
                
                // Tap Payments charge status mapping
                $status = match ($chargeData['status'] ?? '') {
                    'CAPTURED', 'AUTHORIZED' => 'completed',
                    'FAILED', 'CANCELLED', 'VOID', 'DECLINED' => 'failed',
                    'INITIATED', 'IN_PROGRESS' => 'processing',
                    default => 'pending',
                };

                return [
                    'status' => $status,
                    'transaction_id' => $chargeData['id'] ?? $transactionId,
                    'message' => $chargeData['response']['message'] ?? __('Payment verified'),
                    'gateway_response' => $chargeData,
                ];
            }

            // If charge not found or error, return pending status
            Log::warning('TapPayments - عملية التحقق من عملية دفع (Verify Payment) - HTTP Error', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'status_code' => $response->status(),
                'response_data' => $responseData,
                'response_body' => $response->body(),
                'request_url' => $requestUrl,
            ]);

            return [
                'status' => 'pending',
                'message' => __('Payment verification failed'),
                'gateway_response' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('TapPayments - عملية التحقق من عملية دفع (Verify Payment) - Exception', [
                'operation' => 'verify_payment',
                'gateway_transaction_id' => $transactionId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'pending',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function processCallback(array $data): Transaction
    {
        try {
            // Tap Payments can send data via webhook (POST) or callback URL (GET with query params)
            // Handle both cases: webhook sends full charge object, callback sends tap_id in query
            $chargeId = $data['id'] ?? $data['charge_id'] ?? $data['tap_id'] ?? null;
            $status = $data['status'] ?? $data['state'] ?? null;

            // If we have tap_id from query params, fetch charge details from API
            if ($chargeId && ! isset($data['status'])) {
                $verification = $this->verifyPayment($chargeId);
                $status = $verification['status'];
                $data = array_merge($data, $verification['gateway_response'] ?? []);
            }

            if (! $chargeId) {
                throw new \Exception('Charge ID not found in callback data');
            }

            // Find transaction by gateway_transaction_id
            $transaction = Transaction::where('gateway_transaction_id', $chargeId)->first();

            // If not found, try to find by transaction_ref from metadata or query params
            if (! $transaction) {
                $transactionRef = $data['metadata']['transaction_ref'] 
                    ?? $data['reference']['transaction'] 
                    ?? null;
                
                if ($transactionRef) {
                    $transaction = Transaction::where('transaction_ref', $transactionRef)->first();
                }
            }

            if (! $transaction) {
                throw new \Exception("Transaction not found for charge ID: {$chargeId}");
            }

            // Map Tap Payments status to our status
            $transactionStatus = match ($status) {
                'CAPTURED', 'AUTHORIZED' => 'completed',
                'FAILED', 'CANCELLED', 'VOID', 'DECLINED' => 'failed',
                'INITIATED', 'IN_PROGRESS' => 'processing',
                default => 'processing',
            };

            // Update transaction status using PaymentProcessor
            $processor = app(\App\Services\Payment\PaymentProcessor::class);
            $processor->updateStatus($transaction, $transactionStatus, [
                'gateway_response' => $data,
            ]);

            Log::info('Tap Payments callback processed', [
                'transaction_id' => $transaction->id,
                'charge_id' => $chargeId,
                'status' => $transactionStatus,
            ]);

            return $transaction->fresh();
        } catch (\Exception $e) {
            Log::error('Tap Payments callback processing failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    public function checkHealth(): array
    {
        try {
            // First check configuration
            $configCheck = parent::checkHealth();
            if ($configCheck['status'] !== 'healthy') {
                return $configCheck;
            }

            // Try to make a test API call to Tap Payments
            $secretKey = $this->getConfigValue('secret_key');
            $apiUrl = $this->getConfigValue('api_url', 'https://api.tap.company/v2');

            // Tap Payments health check - test API connectivity
            // We'll try to list charges with limit=1 to test the API
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$secretKey}",
                    'Content-Type' => 'application/json',
                ])
                ->get("{$apiUrl}/charges", [
                    'limit' => 1,
                ]);

            // For health check, we consider API healthy if:
            // - 200-299: Successful response
            // - 400-404: API is reachable, just invalid request (means API is working)
            // - 401/403: API is reachable, auth issue (means API is working)
            // Only 500+ or network errors are unhealthy
            $statusCode = $response->status();

            if ($response->successful() || ($statusCode >= 400 && $statusCode < 500)) {
                return [
                    'status' => 'healthy',
                    'message' => __('Gateway is healthy and responding'),
                    'details' => [
                        'status_code' => $statusCode,
                        'api_url' => $apiUrl,
                        'note' => $statusCode >= 400 ? __('API is reachable. Status code indicates API is working.') : __('API connection successful'),
                    ],
                ];
            }

            // 500+ errors or other issues
            return [
                'status' => 'unhealthy',
                'message' => __('Gateway API returned server error: :status', ['status' => $statusCode]),
                'details' => [
                    'status_code' => $statusCode,
                    'response' => substr($response->body(), 0, 200),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Tap Payments health check failed', [
                'error' => $e->getMessage(),
                'gateway_id' => $this->gateway->id,
            ]);

            return [
                'status' => 'unhealthy',
                'message' => __('Health check failed: :error', ['error' => $e->getMessage()]),
                'details' => [
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }
}
