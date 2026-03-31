<?php

namespace App\Models;

use App\Enums\ActivityAction;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Events\TransactionCanceled;
use App\Events\TransactionCompleted;
use App\Events\TransactionCreated;
use App\Events\TransactionFailed;
use App\Events\TransactionPending;
use App\Events\TransactionRefunded;
use App\Events\TransactionRefundRequested;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use ActivityLoggable, HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_ref',
        'type',
        'order_id',
        'payment_gateway_id',
        'payment_method_id',
        'amount',
        'gateway_processing_fee',
        'platform_commission',
        'net_amount',
        'total_amount',
        'status',
        'gateway_transaction_id',
        'gateway_response',
        'failure_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_processing_fee' => 'decimal:2',
            'platform_commission' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'gateway_response' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $transaction) {
            if (blank($transaction->transaction_ref)) {
                $transaction->transaction_ref = 'TXN-'.strtoupper(Str::random(12));
            }

            if (blank($transaction->type)) {
                $transaction->type = TransactionType::CHECKOUT;
            }

            if (blank($transaction->status)) {
                $transaction->status = TransactionStatus::PENDING;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(TransactionActivityLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', TransactionStatus::FAILED);
    }

    public function scopeOfType($query, TransactionType $type)
    {
        return $query->where('type', $type);
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::FAILED;
    }

    public function logActivity(string $description, ?User $user = null): TransactionActivityLog
    {
        if ($user) {
            $description = __(':name :action', [
                'name' => $user->name,
                'action' => $description,
            ]);
        }

        return TransactionActivityLog::log(ActivityAction::SYSTEM, $description, $this, $user);
    }

    public function logCreated(?User $user = null): TransactionActivityLog
    {
        return $this->logActivity(__('has created a transaction'), $user);
    }

    public function logStatusChange(TransactionStatus $oldStatus, TransactionStatus $newStatus, ?User $user = null): TransactionActivityLog
    {
        $action = match ($newStatus) {
            TransactionStatus::COMPLETED => __('has completed a transaction'),
            TransactionStatus::FAILED => __('has failed a transaction'),
            TransactionStatus::CANCELLED => __('has canceled a transaction'),
            TransactionStatus::PROCESSING => __('has set transaction to pending'),
            TransactionStatus::REFUNDED => __('has refunded a transaction'),
            TransactionStatus::REFUND_REQUESTED => __('has requested a transaction refund'),
            default => __('has changed transaction status'),
        };

        return $this->logActivity($action, $user);
    }

    public function logCanceled(?User $user = null): TransactionActivityLog
    {
        return $this->logActivity(__('has canceled a transaction'), $user);
    }

    public function logRefunded(?User $user = null): TransactionActivityLog
    {
        return $this->logActivity(__('has refunded a transaction'), $user);
    }

    public function dispatchCreated(?User $user = null): void
    {
        TransactionCreated::dispatch($this, $user ?? Auth::user());
    }

    public function dispatchPending(?User $user = null): void
    {
        TransactionPending::dispatch($this, $user ?? Auth::user());
    }

    public function dispatchCompleted(?User $user = null): void
    {
        TransactionCompleted::dispatch($this, $user ?? Auth::user());
    }

    public function dispatchFailed(?User $user = null): void
    {
        TransactionFailed::dispatch($this, $user ?? Auth::user());
    }

    public function dispatchCanceled(?User $user = null): void
    {
        TransactionCanceled::dispatch($this, $user ?? Auth::user());
    }

    public function dispatchRefundRequested(?User $user = null): void
    {
        TransactionRefundRequested::dispatch($this, $user ?? Auth::user());
    }

    public function dispatchRefunded(?User $user = null): void
    {
        TransactionRefunded::dispatch($this, $user ?? Auth::user());
    }
}
