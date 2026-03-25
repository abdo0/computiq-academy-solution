<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrganizationStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';
    case BLOCKED = 'blocked';
    case SUSPICIOUS = 'suspicious';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
            self::PENDING => __('Pending'),
            self::SUSPENDED => __('Suspended'),
            self::BANNED => __('Banned'),
            self::BLOCKED => __('Blocked'),
            self::SUSPICIOUS => __('Suspicious'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::PENDING => 'warning',
            self::SUSPENDED => 'gray',
            self::BANNED => 'danger',
            self::BLOCKED => 'danger',
            self::SUSPICIOUS => 'warning',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::ACTIVE => Heroicon::CheckCircle,
            self::INACTIVE => Heroicon::XCircle,
            self::PENDING => Heroicon::Clock,
            self::SUSPENDED => Heroicon::PauseCircle,
            self::BANNED => Heroicon::ShieldExclamation,
            self::BLOCKED => Heroicon::NoSymbol,
            self::SUSPICIOUS => Heroicon::ExclamationTriangle,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::ACTIVE => __('Organization is active and can receive donations'),
            self::INACTIVE => __('Organization is inactive and cannot receive donations'),
            self::PENDING => __('Organization is pending verification'),
            self::SUSPENDED => __('Organization is temporarily suspended'),
            self::BANNED => __('Organization is banned and cannot receive donations'),
            self::BLOCKED => __('Organization is blocked due to compliance issues'),
            self::SUSPICIOUS => __('Organization has suspicious activity and requires review'),
        };
    }
}
