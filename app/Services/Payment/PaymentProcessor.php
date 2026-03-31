<?php

namespace App\Services\Payment;

use App\Enums\ActivityAction;
use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\CartItem;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentProcessor
{
    public function __construct(
        protected PaymentCalculator $calculator,
        protected PromoCodeService $promoCodeService
    ) {}

    /**
     * Create an order and transaction from the user's current cart.
     */
    public function processCheckout(User $user, PaymentGateway $gateway, array $metadata = []): array
    {
        return DB::transaction(function () use ($user, $gateway, $metadata) {
            $paymentMethodCode = $gateway->type->value ?? 'card';
            $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();

            $staleCourseIds = $user->courseEnrollments()
                ->whereIn('course_id', CartItem::where('user_id', $user->id)->pluck('course_id'))
                ->pluck('course_id');

            if ($staleCourseIds->isNotEmpty()) {
                CartItem::where('user_id', $user->id)
                    ->whereIn('course_id', $staleCourseIds)
                    ->delete();
            }

            $cartItems = CartItem::with('course')
                ->where('user_id', $user->id)
                ->get()
                ->filter(fn (CartItem $item) => $item->course !== null);

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException(__('Your cart is empty.'));
            }

            $subtotal = (float) $cartItems->sum(fn (CartItem $item) => (float) $item->price);
            $promoCode = $this->promoCodeService->resolve($metadata['promo_code'] ?? null, true);
            $fees = $this->calculator->calculateCheckoutTotals($subtotal, $gateway, $promoCode);

            $order = Order::create([
                'user_id' => $user->id,
                'payment_gateway_id' => $gateway->id,
                'payment_method_id' => $paymentMethod?->id,
                'promo_code_id' => $promoCode?->id,
                'promo_code' => $promoCode?->code,
                'discount_type' => $promoCode?->discount_type,
                'discount_value' => $promoCode?->discount_value ?? 0,
                'discount_amount' => $fees['discount_amount'],
                'subtotal_before_discount' => $fees['subtotal_before_discount'],
                'subtotal_after_discount' => $fees['subtotal_after_discount'],
                'subtotal_amount' => $fees['subtotal_after_discount'],
                'gateway_processing_fee' => $fees['gateway_processing_fee'],
                'total_amount' => $fees['total_amount'],
                'status' => OrderStatus::PENDING,
                'notes' => $metadata['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $item->course_id,
                    'unit_price' => $item->price,
                    'total_price' => $item->price,
                    'course_snapshot' => [
                        'title' => $item->course->title,
                        'slug' => $item->course->slug,
                        'image' => $item->course->image,
                    ],
                ]);
            }

            $transaction = Transaction::create([
                'type' => TransactionType::CHECKOUT,
                'order_id' => $order->id,
                'payment_gateway_id' => $gateway->id,
                'amount' => $fees['amount'],
                'gateway_processing_fee' => $fees['gateway_processing_fee'],
                'platform_commission' => $fees['platform_commission'],
                'net_amount' => $fees['net_amount'],
                'total_amount' => $fees['total_amount'],
                'status' => TransactionStatus::PENDING,
                'payment_method_id' => $paymentMethod?->id,
                'notes' => $metadata['notes'] ?? __('Course checkout for order :order', ['order' => $order->order_ref]),
            ]);

            $transaction->dispatchCreated($user);
            $transaction->dispatchPending($user);

            Log::info('Order and transaction created for checkout', [
                'order_id' => $order->id,
                'order_ref' => $order->order_ref,
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'user_id' => $user->id,
                'subtotal_before_discount' => $fees['subtotal_before_discount'],
                'discount_amount' => $fees['discount_amount'],
                'amount' => $fees['amount'],
            ]);

            return [
                'order' => $order,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Transaction $transaction, string $status, array $data = []): Transaction
    {
        return DB::transaction(function () use ($transaction, $status, $data) {
            $oldStatus = $transaction->status;

            $transaction->update([
                'status' => $status,
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? $transaction->gateway_transaction_id,
                'gateway_response' => $data['gateway_response'] ?? $transaction->gateway_response,
                'failure_reason' => $data['failure_reason'] ?? $transaction->failure_reason,
            ]);

            $transaction->refresh();

            if ($status === TransactionStatus::COMPLETED->value) {
                if ($transaction->order) {
                    $transaction->order->update([
                        'status' => OrderStatus::PAID,
                        'paid_at' => now(),
                        'payment_gateway_id' => $transaction->payment_gateway_id,
                        'payment_method_id' => $transaction->payment_method_id,
                    ]);

                    $this->fulfillOrder($transaction->order->fresh('items'), $transaction);
                    $this->syncPromoCodeUsageCount($transaction->order->promoCode);
                }
            } elseif ($status === TransactionStatus::FAILED->value) {
                $transaction->order?->update(['status' => OrderStatus::FAILED]);
                $this->syncPromoCodeUsageCount($transaction->order?->promoCode);
            } elseif ($status === TransactionStatus::PROCESSING->value) {
                $transaction->order?->update(['status' => OrderStatus::PROCESSING]);
            } elseif ($status === TransactionStatus::CANCELLED->value) {
                $transaction->order?->update(['status' => OrderStatus::CANCELLED]);
                $this->syncPromoCodeUsageCount($transaction->order?->promoCode);
            }

            if ($oldStatus !== $transaction->status) {
                match ($transaction->status) {
                    TransactionStatus::COMPLETED => $transaction->dispatchCompleted($transaction->order?->user),
                    TransactionStatus::FAILED => $transaction->dispatchFailed($transaction->order?->user),
                    TransactionStatus::CANCELLED => $transaction->dispatchCanceled($transaction->order?->user),
                    TransactionStatus::REFUND_REQUESTED => $transaction->dispatchRefundRequested($transaction->order?->user),
                    TransactionStatus::REFUNDED => $transaction->dispatchRefunded($transaction->order?->user),
                    default => null,
                };
            }

            return $transaction;
        });
    }

    protected function fulfillOrder(Order $order, Transaction $transaction): void
    {
        $courseIdsToRemove = [];

        foreach ($order->items as $item) {
            $enrollment = CourseEnrollment::firstOrCreate(
                [
                    'user_id' => $order->user_id,
                    'course_id' => $item->course_id,
                ],
                [
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'enrolled_at' => now(),
                ]
            );

            if ($enrollment->wasRecentlyCreated) {
                $item->course()->increment('students_count');
            }

            $courseIdsToRemove[] = $item->course_id;
        }

        CartItem::where('user_id', $order->user_id)
            ->whereIn('course_id', $courseIdsToRemove)
            ->delete();
    }

    public function processRefund(Transaction $transaction, ?float $amount = null, ?string $refundReason = null): Transaction
    {
        throw new \RuntimeException(__('Refunds are not implemented in this checkout flow.'));
    }

    protected function syncPromoCodeUsageCount(?PromoCode $promoCode): void
    {
        if ($promoCode) {
            $this->promoCodeService->refreshUsedCount($promoCode);
        }
    }
}
