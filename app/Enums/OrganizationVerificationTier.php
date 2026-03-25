<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrganizationVerificationTier: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case BASIC = 'basic';
    case STANDARD = 'standard';
    case ENHANCED = 'enhanced';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BASIC => __('Basic'),
            self::STANDARD => __('Standard'),
            self::ENHANCED => __('Enhanced'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BASIC => 'gray',
            self::STANDARD => 'primary',
            self::ENHANCED => 'success',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::BASIC => Heroicon::Sparkles,
            self::STANDARD => Heroicon::AdjustmentsVertical,
            self::ENHANCED => Heroicon::ShieldCheck,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::BASIC => __('Identity verified with foundational documentation.'),
            self::STANDARD => __('Includes regulatory registrations and tax compliance.'),
            self::ENHANCED => __('Full enhanced due diligence for high-volume organizations.'),
        };
    }
}
