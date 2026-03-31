<?php

namespace App\Models;

use App\Enums\PaymentGatewayType;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class PaymentGateway extends Model implements HasMedia
{
    use ActivityLoggable, HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'processing_fee_percentage',
        'processing_fee_fixed',
        'configuration',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'type' => PaymentGatewayType::class,
            'processing_fee_percentage' => 'decimal:2',
            'processing_fee_fixed' => 'decimal:2',
            'configuration' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(PaymentGatewayFee::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getProcessingFeeAttribute(): string
    {
        $percentage = (float) $this->processing_fee_percentage;
        $fixed = (float) $this->processing_fee_fixed;
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10)
            ->performOnCollections('logo');

        $this->addMediaConversion('preview')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('logo');
    }
}
