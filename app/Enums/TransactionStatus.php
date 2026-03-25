<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TransactionStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUND_REQUESTED = 'refund_requested';
    case REFUNDED = 'refunded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::PROCESSING => __('Processing'),
            self::COMPLETED => __('Completed'),
            self::FAILED => __('Failed'),
            self::CANCELLED => __('Cancelled'),
            self::REFUND_REQUESTED => __('Refund Requested'),
            self::REFUNDED => __('Refunded'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
            self::REFUND_REQUESTED => 'warning',
            self::REFUNDED => 'info',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::PENDING => Heroicon::Clock,
            self::PROCESSING => Heroicon::ArrowPath,
            self::COMPLETED => Heroicon::CheckCircle,
            self::FAILED => Heroicon::XCircle,
            self::CANCELLED => Heroicon::XMark,
            self::REFUND_REQUESTED => Heroicon::ArrowUturnLeft,
            self::REFUNDED => Heroicon::ArrowPathRoundedSquare,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PENDING => __('Transaction is pending payment'),
            self::PROCESSING => __('Transaction is being processed'),
            self::COMPLETED => __('Transaction completed successfully'),
            self::FAILED => __('Transaction failed'),
            self::CANCELLED => __('Transaction was cancelled'),
            self::REFUND_REQUESTED => __('Refund has been requested'),
            self::REFUNDED => __('Transaction was refunded'),
        };
    }
}
