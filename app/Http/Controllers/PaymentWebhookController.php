<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request, string $gateway): JsonResponse
    {
        try {
            $paymentGateway = PaymentGateway::active()
                ->where('code', $gateway)
                ->firstOrFail();

            $transaction = $this->paymentService->processWebhook($paymentGateway, $request->all());

            return response()->success([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status?->value ?? $transaction->status,
            ], __('Webhook processed successfully.'));
        } catch (\Throwable $e) {
            Log::error('Payment webhook failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->error(__('Webhook processing failed.'), [], 500);
        }
    }
}
