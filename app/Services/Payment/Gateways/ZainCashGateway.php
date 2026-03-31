<?php

namespace App\Services\Payment\Gateways;

use App\Enums\TransactionStatus;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ZainCash Payment Gateway v2 Integration
 * API Docs: https://pg-api-uat.zaincash.iq
 *
 * Flow:
 *  1. POST /oauth2/token (client_credentials) → access_token
 *  2. POST /api/v2/payment-gateway/transaction/init → redirectUrl + transactionId
 *  3. Redirect customer to redirectUrl
 *  4. Customer completes payment → ZainCash redirects to successUrl/failureUrl?token=JWT
 *  5. Verify JWT with api_key (HS256) and/or call inquiry endpoint
 */
class ZainCashGateway extends BaseGateway
{
    protected function getRequiredConfigKeys(): array
    {
        return ['client_id', 'client_secret', 'service_type'];
    }

    /**
     * Always read ZainCash config from config/zaincash.php (.env),
     * not from the payment_gateways DB table.
     */
    public function getConfig(): array
    {
        return config('zaincash', []);
    }

    // ===========================================================================
    // OAuth2 Token Management
    // ===========================================================================

    /**
     * Get (or refresh) the OAuth2 access token.
     * Cached for (expires_in - 60) seconds.
     */
    protected function getAccessToken(): string
    {
        $config = $this->getConfig();
        $clientId = $config['client_id'];
        $cacheKey = "zaincash_access_token_{$clientId}";

        return Cache::remember($cacheKey, now()->addSeconds(86400 - 60), function () use ($config) {
            $response = Http::asForm()->post(
                $this->baseUrl().'/oauth2/token',
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'scope' => 'payment:read payment:write reverse:write',
                ]
            );

            Log::info('ZainCash v2 - OAuth2 Token Request', [
                'status' => $response->status(),
                'client_id' => $config['client_id'],
            ]);

            if (! $response->successful() || ! isset($response->json()['access_token'])) {
                Log::error('ZainCash v2 - OAuth2 Token Failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new \Exception('ZainCash: Failed to obtain access token. '.json_encode($response->json()));
            }

            return $response->json()['access_token'];
        });
    }

    protected function baseUrl(): string
    {
        $config = $this->getConfig();

        return rtrim($config['base_url'] ?? 'https://pg-api-uat.zaincash.iq', '/');
    }

    // ===========================================================================
    // Initiate Payment
    // ===========================================================================

    public function initiatePayment(Transaction $transaction, array $data = []): array
    {
        try {
            $config = $this->getConfig();
            $token = $this->getAccessToken();
            $amount = (int) round($data['amount']);
            $successUrl = $data['return_url'] ?? url('/payment/callback/'.$transaction->transaction_ref.'?status=success');
            $failureUrl = $data['failure_url'] ?? url('/payment/callback/'.$transaction->transaction_ref.'?status=failure');

            $payload = [
                'language' => $config['language'] ?? 'en',
                'externalReferenceId' => (string) Str::uuid(),
                'orderId' => $transaction->transaction_ref,
                'serviceType' => $config['service_type'] ?? 'Course Checkout',
                'amount' => [
                    'value' => (string) $amount,
                    'currency' => strtoupper($data['currency'] ?? $config['currency'] ?? Currency::getDefaultCode()),
                ],
                'redirectUrls' => [
                    'successUrl' => $successUrl,
                    'failureUrl' => $failureUrl,
                ],
            ];

            Log::info('ZainCash v2 - initiatePayment Request', [
                'transaction_ref' => $transaction->transaction_ref,
                'customer_phone_omitted' => true,
                'provided_customer_phone_ignored' => array_key_exists('customer_phone', $data),
                'payload' => $payload,
            ]);

            $response = Http::withToken($token)
                ->post($this->baseUrl().'/api/v2/payment-gateway/transaction/init', $payload);

            $body = $response->json();

            Log::info('ZainCash v2 - initiatePayment Response', [
                'transaction_ref' => $transaction->transaction_ref,
                'status' => $response->status(),
                'body' => $body,
            ]);

            if (! $response->successful() || ($body['status'] ?? '') !== 'SUCCESS') {
                $errMsg = $body['message'] ?? $body['error'] ?? json_encode($body);
                throw new \Exception("ZainCash init failed: {$errMsg}");
            }

            $gatewayTxnId = $body['transactionDetails']['transactionId'];
            $redirectUrl = $body['redirectUrl'];

            return [
                'status' => 'processing',
                'transaction_id' => $gatewayTxnId,
                'payment_url' => $redirectUrl,
                'gateway_response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('ZainCash v2 - initiatePayment Exception', [
                'transaction_ref' => $transaction->transaction_ref ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ===========================================================================
    // Verify Payment (Inquiry API)
    // ===========================================================================

    public function verifyPayment(string $transactionId): array
    {
        try {
            $token = $this->getAccessToken();

            Log::info('ZainCash v2 - verifyPayment (Inquiry)', [
                'gateway_transaction_id' => $transactionId,
            ]);

            $response = Http::withToken($token)
                ->get($this->baseUrl().'/api/v2/payment-gateway/transaction/inquiry/'.$transactionId);

            $body = $response->json();

            Log::info('ZainCash v2 - verifyPayment Response', [
                'gateway_transaction_id' => $transactionId,
                'body' => $body,
            ]);

            if (! $response->successful()) {
                return [
                    'status' => 'failed',
                    'transaction_id' => $transactionId,
                    'message' => $body['message'] ?? json_encode($body),
                    'gateway_response' => $body,
                ];
            }

            $rawStatus = strtoupper($body['status'] ?? '');
            $normalizedStatus = $this->mapStatus($rawStatus);

            return [
                'status' => $normalizedStatus,
                'transaction_id' => $transactionId,
                'message' => $rawStatus,
                'gateway_response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('ZainCash v2 - verifyPayment Exception', [
                'gateway_transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ];
        }
    }

    // ===========================================================================
    // Callback / Webhook
    // ===========================================================================

    /**
     * Process the redirect callback from ZainCash.
     * After payment, ZainCash redirects to:
     *   successUrl?token=JWT_TOKEN  or  failureUrl?token=JWT_TOKEN
     *
     * The JWT is signed with HS256 using the API key (same as client_secret for test env).
     * We decode and verify, then call the inquiry API for the final source of truth.
     */
    public function processCallback(array $data): Transaction
    {
        $jwtToken = $data['token'] ?? null;
        $statusFromUrl = $data['status'] ?? null;    // 'success' or 'failure' from our own URL param
        $orderId = $data['orderId'] ?? null;
        $transactionRef = $data['transaction_ref'] ?? null;

        // Decode the JWT from the redirect (without verifying signature first — verification done via inquiry)
        $decoded = [];
        if ($jwtToken) {
            try {
                $parts = explode('.', $jwtToken);
                $payloadB64 = $parts[1] ?? '';
                $padLen = strlen($payloadB64) % 4;
                if ($padLen) {
                    $payloadB64 .= str_repeat('=', 4 - $padLen);
                }
                $decoded = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payloadB64)), true) ?? [];
            } catch (\Throwable) {
                $decoded = [];
            }
        }

        $gatewayOrderId = $decoded['data']['orderId'] ?? $orderId;
        $gatewayTxnId = $decoded['data']['transactionId'] ?? null;
        $currentStatus = $decoded['data']['currentStatus'] ?? null;

        // Find our local transaction
        $transaction = Transaction::query()
            ->when($gatewayTxnId, fn ($q) => $q->where('gateway_transaction_id', $gatewayTxnId))
            ->when($transactionRef, fn ($q) => $q->orWhere('transaction_ref', $transactionRef))
            ->when($gatewayOrderId, fn ($q) => $q->orWhere('transaction_ref', $gatewayOrderId))
            ->firstOrFail();

        // Use the Inquiry API as source of truth
        if ($gatewayTxnId ?? $transaction->gateway_transaction_id) {
            $inquiry = $this->verifyPayment($gatewayTxnId ?? $transaction->gateway_transaction_id);
            $normalizedStatus = $inquiry['status'];
        } else {
            $normalizedStatus = $this->mapStatusFromRedirect($statusFromUrl, $currentStatus);
        }

        $enumStatus = match ($normalizedStatus) {
            'completed' => TransactionStatus::COMPLETED->value,
            'failed' => TransactionStatus::FAILED->value,
            default => TransactionStatus::PROCESSING->value,
        };

            $processor = app(\App\Services\Payment\PaymentProcessor::class);

            return $processor->updateStatus($transaction, $enumStatus, [
                'gateway_transaction_id' => $gatewayTxnId ?? $transaction->gateway_transaction_id,
                'gateway_response' => $decoded ?: $data,
            ]);
        }

    // ===========================================================================
    // Helpers
    // ===========================================================================

    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'SUCCESS' => 'completed',
            'FAILED', 'EXPIRED' => 'failed',
            'REFUNDED' => 'failed',
            'PENDING', 'OTP_SENT',
            'CUSTOMER_AUTHENTICATION_REQUIRED' => 'pending',
            default => 'pending',
        };
    }

    protected function mapStatusFromRedirect(?string $urlStatus, ?string $jwtStatus): string
    {
        if ($jwtStatus) {
            return $this->mapStatus(strtoupper($jwtStatus));
        }

        return match ($urlStatus) {
            'success' => 'completed',
            'failure' => 'failed',
            default => 'pending',
        };
    }
}
