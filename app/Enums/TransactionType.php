<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TransactionType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case DONATION = 'donation';
    case WALLET_TOPUP = 'wallet_topup';
    case REFUND = 'refund';
    case FEE = 'fee';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DONATION => __('Donation Payment'),
            self::WALLET_TOPUP => __('Wallet Top-up'),
            self::REFUND => __('Refund'),
            self::FEE => __('Platform Fee'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DONATION => 'success',
            self::WALLET_TOPUP => 'info',
            self::REFUND => 'warning',
            self::FEE => 'gray',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::DONATION => Heroicon::Banknotes,
            self::WALLET_TOPUP => Heroicon::ArrowDownCircle,
            self::REFUND => Heroicon::ArrowPath,
            self::FEE => Heroicon::CurrencyDollar,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::DONATION => __('Payment for a campaign donation'),
            self::WALLET_TOPUP => __('Adding funds to donor wallet'),
            self::REFUND => __('Refunding a previous transaction'),
            self::FEE => __('Platform or service fee'),
        };
    }
}




