<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrganizationVerificationStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case UNVERIFIED = 'unverified';
    case PENDING = 'pending';
    case IN_REVIEW = 'in_review';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNVERIFIED => __('Unverified'),
            self::PENDING => __('Pending'),
            self::IN_REVIEW => __('In Review'),
            self::VERIFIED => __('Verified'),
            self::REJECTED => __('Rejected'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::UNVERIFIED => 'gray',
            self::PENDING => 'info',
            self::IN_REVIEW => 'warning',
            self::VERIFIED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::UNVERIFIED => Heroicon::QuestionMarkCircle,
            self::PENDING => Heroicon::Clock,
            self::IN_REVIEW => Heroicon::MagnifyingGlass,
            self::VERIFIED => Heroicon::ShieldCheck,
            self::REJECTED => Heroicon::XCircle,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::UNVERIFIED => __('No verification documents submitted yet.'),
            self::PENDING => __('Awaiting compliance review.'),
            self::IN_REVIEW => __('Compliance is reviewing submitted documents.'),
            self::VERIFIED => __('Organization passed all verification requirements.'),
            self::REJECTED => __('Verification was rejected due to issues in documentation.'),
        };
    }
}
