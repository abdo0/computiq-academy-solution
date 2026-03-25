<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PaymentGatewayFeeType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case GATEWAY_PROCESSING = 'gateway_processing';
    case PLATFORM_COMMISSION = 'platform_commission';
    case SETTLEMENT = 'settlement';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GATEWAY_PROCESSING => __('Gateway Processing Fee'),
            self::PLATFORM_COMMISSION => __('Platform Commission'),
            self::SETTLEMENT => __('Settlement Fee'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GATEWAY_PROCESSING => 'primary',
            self::PLATFORM_COMMISSION => 'success',
            self::SETTLEMENT => 'info',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::GATEWAY_PROCESSING => Heroicon::CreditCard,
            self::PLATFORM_COMMISSION => Heroicon::Banknotes,
            self::SETTLEMENT => Heroicon::ArrowPath,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::GATEWAY_PROCESSING => __('Fee charged by payment gateway (User → Platform)'),
            self::PLATFORM_COMMISSION => __('Platform commission fee (Platform → Organization)'),
            self::SETTLEMENT => __('Fee for transferring funds to organization'),
        };
    }
}
