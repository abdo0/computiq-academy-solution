<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::PROCESSING => __('Processing'),
            self::PAID => __('Paid'),
            self::FAILED => __('Failed'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::PENDING => Heroicon::Clock,
            self::PROCESSING => Heroicon::ArrowPath,
            self::PAID => Heroicon::CheckCircle,
            self::FAILED => Heroicon::XCircle,
            self::CANCELLED => Heroicon::XMark,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PENDING => __('Order is waiting for payment'),
            self::PROCESSING => __('Order payment is being processed'),
            self::PAID => __('Order has been paid successfully'),
            self::FAILED => __('Order payment failed'),
            self::CANCELLED => __('Order was cancelled'),
        };
    }
}
