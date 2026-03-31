<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_gateway_id' => ['nullable', 'exists:payment_gateways,id'],
            'promo_code' => ['nullable', 'string', 'max:100'],
        ]);

        $gateway = null;

        if (! empty($validated['payment_gateway_id'])) {
            $gateway = PaymentGateway::active()->find($validated['payment_gateway_id']);

            if (! $gateway) {
                return response()->error(__('Selected payment gateway is not available.'), [], 422);
            }
        }

        $result = $this->paymentService->quoteCheckout(
            $request->user(),
            $gateway,
            $validated['promo_code'] ?? null,
        );

        if (! ($result['success'] ?? false)) {
            return response()->error($result['error'] ?? __('Unable to quote checkout.'), [], 422);
        }

        return response()->success($result, __('Checkout quote retrieved successfully.'));
    }

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_gateway_id' => ['required', 'exists:payment_gateways,id'],
            'promo_code' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $gateway = PaymentGateway::active()->find($validated['payment_gateway_id']);

        if (! $gateway) {
            return response()->error(__('Selected payment gateway is not available.'), [], 422);
        }

        try {
            $result = $this->paymentService->initiateCheckout($request->user(), $gateway, [
                'promo_code' => $validated['promo_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! ($result['success'] ?? false)) {
                return response()->error(
                    $result['error'] ?? __('Checkout initiation failed.'),
                    [],
                    422
                );
            }

            return response()->success([
                'order' => $result['order'],
                'transaction' => $result['transaction'],
                'payment_url' => $result['payment_url'],
                'promo' => $result['promo'] ?? null,
                'totals' => $result['totals'] ?? null,
            ], __('Checkout initiated successfully.'), 201);
        } catch (\Throwable $e) {
            Log::error('Checkout initiation controller failed', [
                'user_id' => $request->user()?->id,
                'gateway_id' => $gateway->id,
                'error' => $e->getMessage(),
            ]);

            return response()->error($e->getMessage() ?: __('Checkout initiation failed.'), [], 422);
        }
    }
}
