<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrganizationType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case STANDARD = 'standard';
    case VERIFIED = 'verified';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STANDARD => __('Standard'),
            self::VERIFIED => __('Verified'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::STANDARD => 'gray',
            self::VERIFIED => 'success',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::STANDARD => Heroicon::BuildingOffice,
            self::VERIFIED => Heroicon::CheckBadge,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::STANDARD => __('Standard organization'),
            self::VERIFIED => __('Verified organization with reduced commission'),
        };
    }
}
