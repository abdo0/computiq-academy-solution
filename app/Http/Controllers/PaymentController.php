<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function verify(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->order?->user_id !== $request->user()?->id) {
            abort(403);
        }

        try {
            $result = $this->paymentService->verifyPayment($transaction);

            if ($result['success']) {
                return response()->success([
                    'transaction' => $result['transaction'] ?? null,
                    'status' => $result['status'] ?? null,
                ], __('Payment verified successfully.'));
            }

            return response()->error(
                $result['error'] ?? __('Payment verification failed.'),
                [
                    'transaction' => $result['transaction'] ?? null,
                    'status' => $result['status'] ?? null,
                ],
                422
            );
        } catch (\Throwable $e) {
            Log::error('Payment verification failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return response()->error(__('Payment verification failed.'), [], 500);
        }
    }

    public function callback(Request $request, string $transactionRef): RedirectResponse
    {
        try {
            $transaction = Transaction::where('transaction_ref', $transactionRef)
                ->with(['paymentGateway', 'order.user'])
                ->firstOrFail();

            $gateway = $transaction->paymentGateway;

            if ($gateway) {
                $this->paymentService->processWebhook($gateway, array_merge(
                    $request->all(),
                    ['transaction_ref' => $transactionRef]
                ));
            }

            $transaction->refresh();
            $verification = $this->paymentService->verifyPayment($transaction);
            $transaction->refresh();

            $paymentState = $verification['success']
                ? 'success'
                : (($verification['status'] ?? null) === 'processing' || ($verification['status'] ?? null) === 'pending' ? 'pending' : 'error');

            return redirect($this->buildFrontendCheckoutUrl($transaction, $paymentState));
        } catch (\Throwable $e) {
            Log::error('Payment callback failed', [
                'transaction_ref' => $transactionRef,
                'error' => $e->getMessage(),
            ]);

            return redirect('/checkout?payment=error');
        }
    }

    protected function buildFrontendCheckoutUrl(Transaction $transaction, string $paymentState): string
    {
        $locale = $transaction->order?->user?->locale ?: 'ar';
        $checkoutPath = $locale !== 'ar' ? "/{$locale}/checkout" : '/checkout';

        $query = http_build_query([
            'payment' => $paymentState,
            'transaction' => $transaction->transaction_ref,
            'transactionId' => $transaction->id,
        ]);

        return "{$checkoutPath}?{$query}";
    }
}
