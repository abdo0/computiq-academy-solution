<?php

namespace App\Models;

use App\Enums\ActivityAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'action' => ActivityAction::class,
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        ActivityAction|string $action,
        string $description,
        Transaction $transaction,
        ?User $user = null,
    ): self {
        $request = request();

        if (is_string($action)) {
            $action = ActivityAction::tryFrom($action) ?? ActivityAction::SYSTEM;
        }

        if (! $user && auth()->check()) {
            $user = auth()->user();
        }

        return self::create([
            'transaction_id' => $transaction->id,
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'properties' => [],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
