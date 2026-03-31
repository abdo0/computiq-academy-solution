<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PromoCodeDiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_ref',
        'user_id',
        'payment_gateway_id',
        'payment_method_id',
        'promo_code_id',
        'promo_code',
        'discount_type',
        'discount_value',
        'discount_amount',
        'subtotal_before_discount',
        'subtotal_after_discount',
        'subtotal_amount',
        'gateway_processing_fee',
        'total_amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal_before_discount' => 'decimal:2',
            'subtotal_after_discount' => 'decimal:2',
            'gateway_processing_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'discount_type' => PromoCodeDiscountType::class,
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $order) {
            if (blank($order->order_ref)) {
                $order->order_ref = 'ORD-'.strtoupper(Str::random(12));
            }

            if (blank($order->status)) {
                $order->status = OrderStatus::PENDING;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
