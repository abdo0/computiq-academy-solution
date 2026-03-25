<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ContactMessageSubject: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case GENERAL = 'general';
    case DONATION_ISSUE = 'donation_issue';
    case SPONSORSHIP = 'sponsorship';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GENERAL => __('General Inquiry'),
            self::DONATION_ISSUE => __('Donation Issue'),
            self::SPONSORSHIP => __('Sponsorship Request'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GENERAL => 'info',
            self::DONATION_ISSUE => 'warning',
            self::SPONSORSHIP => 'success',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::GENERAL => Heroicon::ChatBubbleLeftRight,
            self::DONATION_ISSUE => Heroicon::ExclamationTriangle,
            self::SPONSORSHIP => Heroicon::HandRaised,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::GENERAL => __('General inquiry or question'),
            self::DONATION_ISSUE => __('Issue related to a donation'),
            self::SPONSORSHIP => __('Request for project sponsorship'),
        };
    }
}

