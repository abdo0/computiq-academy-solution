<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use App\Models\CartItem;
use App\Models\Currency;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected PaymentProcessor $processor,
        protected PaymentCalculator $calculator,
        protected PromoCodeService $promoCodeService
    ) {}

    public function quoteCheckout(User $user, ?PaymentGateway $gateway = null, ?string $promoCode = null): array
    {
        try {
            $cartItems = CartItem::with('course')
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->filter(fn (CartItem $item) => $item->course !== null);

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException(__('Your cart is empty.'));
            }

            $resolvedPromoCode = $this->promoCodeService->resolve($promoCode);
            $subtotal = (float) $cartItems->sum(fn (CartItem $item) => (float) $item->price);
            $totals = $this->calculator->calculateCheckoutTotals($subtotal, $gateway, $resolvedPromoCode);

            return [
                'success' => true,
                'items' => $cartItems->map(fn (CartItem $item) => [
                    'id' => (string) $item->id,
                    'course_id' => (string) $item->course_id,
                    'price' => $this->formatMoney((float) $item->price),
                    'course' => $item->course ? [
                        'id' => (string) $item->course->id,
                        'title' => $item->course->title,
                        'slug' => $item->course->slug,
                        'image' => $item->course->image,
                    ] : null,
                ])->values()->all(),
                'count' => $cartItems->count(),
                'gateway' => $gateway ? [
                    'id' => (string) $gateway->id,
                    'code' => $gateway->code,
                    'name' => $gateway->name,
                    'type' => $gateway->type?->value,
                ] : null,
                'promo' => $this->promoCodeService->toSummary($resolvedPromoCode, $totals['discount_amount']),
                'totals' => $this->formatTotals($totals),
                'currency' => Currency::getDefaultCurrencyData(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initiate checkout for the authenticated user's cart.
     */
    public function initiateCheckout(User $user, PaymentGateway $gateway, array $metadata = []): array
    {
        try {
            $result = $this->processor->processCheckout($user, $gateway, $metadata);
            $transaction = $result['transaction'];
            $order = $result['order'];
            $gatewayService = $this->getGatewayService($gateway);

            $gatewayPayload = [
                'amount' => $transaction->total_amount,
                'currency' => Currency::getDefaultCode(),
                'description' => __('Course checkout :order', ['order' => $order->order_ref]),
                'return_url' => route('api.v1.payments.callback', ['transactionRef' => $transaction->transaction_ref]),
                'failure_url' => route('api.v1.payments.callback', ['transactionRef' => $transaction->transaction_ref, 'status' => 'failure']),
                'callback_url' => route('api.payments.webhook', ['gateway' => $gateway->code]),
            ];

            // ZainCash hosted checkout does not require or accept the local profile phone.
            if ($gateway->code !== 'zaincash') {
                $gatewayPayload['customer_phone'] = $user->phone ?: $user->mobile;
            }

            $gatewayResponse = $gatewayService->initiatePayment($transaction, $gatewayPayload);

            $transaction->update([
                'gateway_transaction_id' => $gatewayResponse['transaction_id'] ?? null,
                'gateway_response' => $gatewayResponse,
                'status' => $gatewayResponse['status'] ?? 'processing',
            ]);

            $paymentUrl = $gatewayResponse['payment_url'] ?? null;

            if (! $paymentUrl) {
                throw new \RuntimeException($gatewayResponse['message'] ?? __('Payment URL was not returned by the gateway.'));
            }

            return [
                'success' => true,
                'order' => $order,
                'transaction' => $transaction,
                'payment_url' => $paymentUrl,
                'promo' => $this->promoCodeService->toSummary(
                    $order->promoCode,
                    (float) $order->discount_amount
                ),
                'totals' => $this->formatTotals([
                    'subtotal_before_discount' => (float) $order->subtotal_before_discount,
                    'discount_amount' => (float) $order->discount_amount,
                    'subtotal_after_discount' => (float) $order->subtotal_after_discount,
                    'gateway_processing_fee' => (float) $order->gateway_processing_fee,
                    'total_amount' => (float) $order->total_amount,
                ]),
                'currency' => Currency::getDefaultCurrencyData(),
                'gateway_response' => $gatewayResponse,
            ];
        } catch (\Exception $e) {
            Log::error('Checkout initiation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
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
            if ($transaction->status === \App\Enums\TransactionStatus::COMPLETED) {
                return [
                    'success' => true,
                    'transaction' => $transaction,
                    'status' => $transaction->status->value,
                ];
            }

            $gateway = $transaction->paymentGateway;
            $gatewayService = $this->getGatewayService($gateway);

            if (blank($transaction->gateway_transaction_id)) {
                Log::warning('Payment verification skipped because gateway transaction id is missing', [
                    'transaction_id' => $transaction->id,
                    'transaction_ref' => $transaction->transaction_ref,
                    'gateway' => $gateway->code ?? null,
                ]);

                return [
                    'success' => false,
                    'transaction' => $transaction,
                    'status' => $transaction->status?->value ?? 'pending',
                    'error' => __('Cannot verify payment: missing gateway transaction reference.'),
                ];
            }

            $verification = $gatewayService->verifyPayment((string) $transaction->gateway_transaction_id);

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

            if ($verification['status'] === 'processing') {
                $this->processor->updateStatus($transaction, 'processing', [
                    'gateway_response' => $verification,
                ]);
            }

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
            return $gatewayService->processCallback($data);
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
            'zaincash' => \App\Services\Payment\Gateways\ZainCashGateway::class,
            'tap_payments' => \App\Services\Payment\Gateways\TapPaymentsGateway::class,
            'qi-card' => \App\Services\Payment\Gateways\QiCardGateway::class,
            'fastpay' => \App\Services\Payment\Gateways\FastPayGateway::class,
            'nasaq' => \App\Services\Payment\Gateways\NasaqGateway::class,
            'asia-hawala' => \App\Services\Payment\Gateways\AsiaHawalaGateway::class,
            default => throw new \Exception(__('Gateway implementation not found for: :gateway', ['gateway' => $gateway->code])),
        };

        $service = app($gatewayClass);
        $service->setGateway($gateway);

        return $service;
    }

    protected function formatTotals(array $totals): array
    {
        return [
            'subtotal_before_discount' => $this->formatMoney((float) ($totals['subtotal_before_discount'] ?? 0)),
            'discount_amount' => $this->formatMoney((float) ($totals['discount_amount'] ?? 0)),
            'subtotal_after_discount' => $this->formatMoney((float) ($totals['subtotal_after_discount'] ?? $totals['amount'] ?? 0)),
            'gateway_processing_fee' => $this->formatMoney((float) ($totals['gateway_processing_fee'] ?? 0)),
            'total_amount' => $this->formatMoney((float) ($totals['total_amount'] ?? 0)),
        ];
    }

    protected function formatMoney(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }
}
