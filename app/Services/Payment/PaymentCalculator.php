<?php

namespace App\Services\Payment;

use App\Enums\PaymentGatewayFeeType;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayFee;
use App\Models\PromoCode;
use App\Enums\PromoCodeDiscountType;

class PaymentCalculator
{
    /**
     * Calculate checkout totals for an order subtotal.
     */
    public function calculateCheckoutTotals(float $subtotal, ?PaymentGateway $gateway = null, ?PromoCode $promoCode = null): array
    {
        $discountAmount = $this->calculateDiscountAmount($subtotal, $promoCode);
        $discountedSubtotal = max($subtotal - $discountAmount, 0);
        $gatewayFee = $gateway ? $this->calculateGatewayProcessingFee($discountedSubtotal, $gateway) : 0;
        $platformCommission = $gateway ? $this->calculatePlatformCommission($discountedSubtotal, $gateway) : 0;
        $netAmount = $discountedSubtotal - $platformCommission;
        $totalAmount = $discountedSubtotal + $gatewayFee;

        return [
            'amount' => $this->roundMoney($discountedSubtotal),
            'subtotal_before_discount' => $this->roundMoney($subtotal),
            'discount_amount' => $this->roundMoney($discountAmount),
            'subtotal_after_discount' => $this->roundMoney($discountedSubtotal),
            'gateway_processing_fee' => $this->roundMoney($gatewayFee),
            'platform_commission' => $this->roundMoney($platformCommission),
            'net_amount' => $this->roundMoney($netAmount),
            'total_amount' => $this->roundMoney($totalAmount),
        ];
    }

    /**
     */
    protected function calculateGatewayProcessingFee(float $amount, PaymentGateway $gateway): float
    {
        $fee = PaymentGatewayFee::where('payment_gateway_id', $gateway->id)
            ->where('fee_type', PaymentGatewayFeeType::GATEWAY_PROCESSING)
            ->where('is_active', true)
            ->first();

        if (! $fee) {
            $percentage = $gateway->processing_fee_percentage ?? 0;
            $fixed = $gateway->processing_fee_fixed ?? 0;

            return $this->calculateFee($amount, (float) $percentage, (float) $fixed);
        }

        return $this->calculateFee($amount, (float) $fee->percentage, (float) $fee->fixed_amount);
    }

    /**
     * Platform commission is optional in the course checkout flow.
     */
    protected function calculatePlatformCommission(float $amount, PaymentGateway $gateway): float
    {
        $fee = PaymentGatewayFee::where('fee_type', PaymentGatewayFeeType::PLATFORM_COMMISSION)
            ->where('payment_gateway_id', $gateway->id)
            ->where('is_active', true)
            ->first();

        if (! $fee) {
            return 0;
        }

        return $this->calculateFee($amount, (float) $fee->percentage, (float) $fee->fixed_amount);
    }

    protected function calculateFee(float $amount, float $percentage, float $fixed): float
    {
        return ($amount * $percentage / 100) + $fixed;
    }

    protected function calculateDiscountAmount(float $subtotal, ?PromoCode $promoCode = null): float
    {
        if (! $promoCode) {
            return 0;
        }

        $discountAmount = match ($promoCode->discount_type) {
            PromoCodeDiscountType::FIXED => (float) $promoCode->discount_value,
            PromoCodeDiscountType::PERCENTAGE => $subtotal * ((float) $promoCode->discount_value / 100),
            default => 0,
        };

        return min($discountAmount, $subtotal);
    }

    protected function roundMoney(float $amount): float
    {
        return round($amount, 2);
    }
}
