<?php

namespace App\Models;

use App\Enums\PaymentGatewayFeeType;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PaymentGatewayFee extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations, SoftDeletes;

    public $translatable = ['description'];

    protected $fillable = [
        'fee_type',
        'payment_gateway_id',
        'percentage',
        'fixed_amount',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'fee_type' => PaymentGatewayFeeType::class,
            'percentage' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'description' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function getFeeAmountAttribute(): string
    {
        $percentage = (float) $this->percentage;
        $fixed = (float) $this->fixed_amount;
        $currency = Currency::getDefaultSymbol();

        if ($percentage > 0 && $fixed > 0) {
            return "{$percentage}% + {$fixed} {$currency}";
        }

        if ($percentage > 0) {
            return "{$percentage}%";
        }

        if ($fixed > 0) {
            return "{$fixed} {$currency}";
        }

        return __('Free');
    }
}
