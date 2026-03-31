<?php

namespace App\Services\Payment;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PromoCode;
use Carbon\CarbonInterface;

class PromoCodeService
{
    public function normalize(?string $code): ?string
    {
        $normalized = strtoupper(trim((string) $code));

        return $normalized !== '' ? $normalized : null;
    }

    public function resolve(?string $code, bool $lockForUpdate = false): ?PromoCode
    {
        $normalized = $this->normalize($code);

        if ($normalized === null) {
            return null;
        }

        $query = PromoCode::query()->where('code', $normalized);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $promoCode = $query->first();

        if (! $promoCode || ! $promoCode->is_active) {
            throw new \RuntimeException(__('The promo code you entered is invalid.'));
        }

        if ($promoCode->starts_at && $promoCode->starts_at->isFuture()) {
            throw new \RuntimeException(__('This promo code is not active yet.'));
        }

        if ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
            throw new \RuntimeException(__('This promo code has expired.'));
        }

        if ($promoCode->usage_limit !== null && $this->getActiveUsageCount($promoCode) >= $promoCode->usage_limit) {
            throw new \RuntimeException(__('This promo code has reached its usage limit.'));
        }

        return $promoCode;
    }

    public function getActiveUsageCount(PromoCode $promoCode): int
    {
        return $promoCode->orders()
            ->whereIn('status', [
                OrderStatus::PENDING->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::PAID->value,
            ])
            ->count();
    }

    public function refreshUsedCount(PromoCode $promoCode): void
    {
        $promoCode->forceFill([
            'used_count' => $promoCode->orders()
                ->where('status', OrderStatus::PAID->value)
                ->count(),
        ])->saveQuietly();
    }

    public function toSummary(?PromoCode $promoCode, float $discountAmount = 0): ?array
    {
        if (! $promoCode) {
            return null;
        }

        return [
            'id' => (string) $promoCode->id,
            'code' => $promoCode->code,
            'discount_type' => $promoCode->discount_type?->value,
            'discount_value' => $this->formatMoney((float) $promoCode->discount_value),
            'discount_amount' => $this->formatMoney($discountAmount),
            'starts_at' => $this->formatDate($promoCode->starts_at),
            'expires_at' => $this->formatDate($promoCode->expires_at),
        ];
    }

    protected function formatMoney(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }

    protected function formatDate(?CarbonInterface $value): ?string
    {
        return $value?->toISOString();
    }
}
