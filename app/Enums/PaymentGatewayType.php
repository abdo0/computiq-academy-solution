<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PaymentGatewayType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case CARD = 'card';
    case MOBILE_WALLET = 'mobile_wallet';
    case BANK = 'bank';
    case MONEY_TRANSFER = 'money_transfer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CARD => __('Card'),
            self::MOBILE_WALLET => __('Mobile Wallet'),
            self::BANK => __('Bank'),
            self::MONEY_TRANSFER => __('Money Transfer'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CARD => 'primary',
            self::MOBILE_WALLET => 'success',
            self::BANK => 'info',
            self::MONEY_TRANSFER => 'warning',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::CARD => Heroicon::CreditCard,
            self::MOBILE_WALLET => Heroicon::DevicePhoneMobile,
            self::BANK => Heroicon::BuildingLibrary,
            self::MONEY_TRANSFER => Heroicon::ArrowPath,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::CARD => __('Card payment gateway'),
            self::MOBILE_WALLET => __('Mobile wallet payment gateway'),
            self::BANK => __('Bank payment gateway'),
            self::MONEY_TRANSFER => __('Money transfer service'),
        };
    }
}
