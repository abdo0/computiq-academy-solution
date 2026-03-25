<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TestZainCashTransaction extends Command
{
    protected $signature = 'zaincash:test
                            {--amount=1000 : Amount in IQD}
                            {--phone= : Customer wallet phone (e.g. 9647802999569)}
                            {--check= : Check status of an existing gateway transactionId}';

    protected $description = 'Test the ZainCash v2 payment gateway integration (OAuth2 flow)';

    public function handle(): int
    {
        $this->info('=== ZainCash v2 Integration Test ===');
        $this->newLine();

        $baseUrl      = config('zaincash.base_url', 'https://pg-api-uat.zaincash.iq');
        $clientId     = config('zaincash.client_id');
        $clientSecret = config('zaincash.client_secret');
        $serviceType  = config('zaincash.service_type', 'General Donation');

        // -----------------------------------------------
        // STEP 1: Get OAuth2 access token
        // -----------------------------------------------
        $this->info('STEP 1: Requesting OAuth2 access token...');
        $this->line("  POST {$baseUrl}/oauth2/token");

        $tokenResponse = Http::asForm()->post("{$baseUrl}/oauth2/token", [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'scope'         => 'payment:read payment:write reverse:write',
        ]);

        if (! $tokenResponse->successful() || ! isset($tokenResponse->json()['access_token'])) {
            $this->error('✗ Failed to obtain access token!');
            $this->line('  HTTP Status: ' . $tokenResponse->status());
            $this->line('  Response: ' . json_encode($tokenResponse->json(), JSON_PRETTY_PRINT));
            return self::FAILURE;
        }

        $accessToken = $tokenResponse->json()['access_token'];
        $expiresIn   = $tokenResponse->json()['expires_in'] ?? 'unknown';
        $this->info("  ✓ Access token obtained (expires in {$expiresIn}s)");
        $this->line('  Token (first 60 chars): ' . substr($accessToken, 0, 60) . '...');
        $this->newLine();

        // -----------------------------------------------
        // STEP 2 (optional): Check existing transaction
        // -----------------------------------------------
        if ($checkTxnId = $this->option('check')) {
            $this->info("STEP 2 (Inquiry): Checking transaction {$checkTxnId}...");
            $this->line("  GET {$baseUrl}/api/v2/payment-gateway/transaction/inquiry/{$checkTxnId}");

            $inquiryResponse = Http::withToken($accessToken)
                ->get("{$baseUrl}/api/v2/payment-gateway/transaction/inquiry/{$checkTxnId}");

            $this->displayResponse($inquiryResponse);
            return self::SUCCESS;
        }

        // -----------------------------------------------
        // STEP 3: Create a test transaction
        // -----------------------------------------------
        $amount      = (int) $this->option('amount');
        $orderRef    = 'TEST-' . strtoupper(Str::random(8));
        $extRef      = (string) Str::uuid();
        $successUrl  = url('/payment/callback/' . $orderRef . '?status=success');
        $failureUrl  = url('/payment/callback/' . $orderRef . '?status=failure');
        $customerPhone = $this->option('phone');

        $this->info('STEP 2: Creating payment transaction...');
        $this->line("  POST {$baseUrl}/api/v2/payment-gateway/transaction/init");
        $this->table(
            ['Field', 'Value'],
            [
                ['Amount', "{$amount} IQD"],
                ['Order ID', $orderRef],
                ['External Ref', $extRef],
                ['Service Type', $serviceType],
                ['Customer Phone', $customerPhone ?: '(not provided)'],
                ['Success URL', $successUrl],
                ['Failure URL', $failureUrl],
            ]
        );

        $payload = [
            'language'            => config('zaincash.language', 'en'),
            'externalReferenceId' => $extRef,
            'orderId'             => $orderRef,
            'serviceType'         => $serviceType,
            'amount'              => [
                'value'    => (string) $amount,
                'currency' => strtoupper(config('zaincash.currency', 'IQD')),
            ],
            'redirectUrls' => [
                'successUrl' => $successUrl,
                'failureUrl' => $failureUrl,
            ],
        ];

        if ($customerPhone) {
            $payload['customer'] = ['phone' => $customerPhone];
        }

        $initResponse = Http::withToken($accessToken)
            ->post("{$baseUrl}/api/v2/payment-gateway/transaction/init", $payload);

        $body = $initResponse->json();

        $this->newLine();
        $this->line('HTTP Status: ' . $initResponse->status());
        $this->line('Response:');
        $this->line(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($initResponse->successful() && ($body['status'] ?? '') === 'SUCCESS') {
            $txnId      = $body['transactionDetails']['transactionId'] ?? 'N/A';
            $redirectUrl = $body['redirectUrl'] ?? 'N/A';

            $this->newLine();
            $this->info('✓ Transaction created successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Transaction ID', $txnId],
                    ['Redirect URL', $redirectUrl],
                    ['Expires At', $body['expiryTime'] ?? 'N/A'],
                ]
            );
            $this->newLine();
            $this->warn('→ Now redirect your customer to:');
            $this->line("  {$redirectUrl}");
            $this->newLine();
            $this->line('Test customer wallets (PIN: 1111, OTP: 111111):');
            $this->table(['#', 'MSISDN'], [
                [1, '9647802999569'],
                [2, '9647829744432'],
                [3, '9647829744464'],
                [4, '9647829744474'],
            ]);
            $this->newLine();
            $this->line('To check the transaction status later, run:');
            $this->line("  php artisan zaincash:test --check={$txnId}");

            return self::SUCCESS;
        }

        $this->error('✗ Transaction creation failed!');
        return self::FAILURE;
    }

    protected function displayResponse($response): void
    {
        $this->line('HTTP Status: ' . $response->status());
        $this->line('Response:');
        $this->line(json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
