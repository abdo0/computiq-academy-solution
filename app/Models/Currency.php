<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (Currency $currency): void {
            static::synchronizeDefaultCurrency($currency->id);
        });

        static::deleted(function (): void {
            static::clearCurrencyCache();
            static::ensureDefaultCurrency();
        });
    }

    /**
     * Scope a query to only include active currencies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get the default currency.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function getDefault(): ?self
    {
        return Cache::remember('default_currency_record', 3600, function () {
            return static::query()
                ->where('is_default', true)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first()
                ?? static::query()->where('is_active', true)->orderBy('sort_order')->first()
                ?? static::query()->orderBy('sort_order')->first();
        });
    }

    public static function getDefaultCode(): string
    {
        return static::getDefault()?->code ?: 'IQD';
    }

    public static function getDefaultSymbol(): string
    {
        return static::getDefault()?->symbol ?: 'د.ع';
    }

    public static function getDefaultCurrencyData(): array
    {
        $currency = static::getDefault();

        return [
            'code' => $currency?->code ?: 'IQD',
            'symbol' => $currency?->symbol ?: 'د.ع',
            'name' => $currency?->name ?: 'Iraqi Dinar',
        ];
    }

    public static function clearCurrencyCache(): void
    {
        Cache::forget('default_currency_record');
        Cache::forget('site_settings');
        Cache::forget('site_settings_v2');
    }

    protected static function synchronizeDefaultCurrency(?int $preferredCurrencyId = null): void
    {
        static::clearCurrencyCache();

        $preferredCurrency = $preferredCurrencyId
            ? static::query()->find($preferredCurrencyId)
            : null;

        if ($preferredCurrency?->is_default) {
            static::query()
                ->whereKeyNot($preferredCurrency->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            static::query()
                ->whereKey($preferredCurrency->id)
                ->update(['is_active' => true]);

            return;
        }

        static::ensureDefaultCurrency($preferredCurrencyId);
    }

    protected static function ensureDefaultCurrency(?int $preferredCurrencyId = null): void
    {
        $defaultCurrency = static::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        if ($defaultCurrency) {
            return;
        }

        $fallbackCurrency = null;

        if ($preferredCurrencyId) {
            $preferredCurrency = static::query()->find($preferredCurrencyId);

            if ($preferredCurrency?->is_active) {
                $fallbackCurrency = $preferredCurrency;
            }
        }

        $fallbackCurrency = $fallbackCurrency
            ?? static::query()->where('is_active', true)->orderBy('sort_order')->first()
            ?? static::query()->orderBy('sort_order')->first();

        if (! $fallbackCurrency) {
            return;
        }

        static::query()
            ->whereKey($fallbackCurrency->id)
            ->update([
                'is_default' => true,
                'is_active' => true,
            ]);

        static::clearCurrencyCache();
    }
}
