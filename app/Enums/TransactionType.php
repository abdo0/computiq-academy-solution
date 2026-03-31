<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TransactionType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case CHECKOUT = 'checkout';
    case DONATION = 'donation';
    case REFUND = 'refund';
    case FEE = 'fee';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CHECKOUT => __('Course Checkout'),
            self::DONATION => __('Donation Payment'),
            self::REFUND => __('Refund'),
            self::FEE => __('Platform Fee'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CHECKOUT => 'success',
            self::DONATION => 'success',
            self::REFUND => 'warning',
            self::FEE => 'gray',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::CHECKOUT => Heroicon::ShoppingBag,
            self::DONATION => Heroicon::Banknotes,
            self::REFUND => Heroicon::ArrowPath,
            self::FEE => Heroicon::CurrencyDollar,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::CHECKOUT => __('Payment for one or more courses'),
            self::DONATION => __('Legacy donation payment type'),
            self::REFUND => __('Refunding a previous transaction'),
            self::FEE => __('Platform or service fee'),
        };
    }
}
