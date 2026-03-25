<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SmsTemplatePurpose: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case WELCOME = 'welcome';
    case OTP_VERIFICATION = 'otp_verification';
    case DONATION_CONFIRMATION = 'donation_confirmation';
    case TRANSACTION_ALERT = 'transaction_alert';
    case CAMPAIGN_REMINDER = 'campaign_reminder';
    case NOTIFICATION = 'notification';
    case GENERAL = 'general';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WELCOME => __('Welcome SMS'),
            self::OTP_VERIFICATION => __('OTP Verification'),
            self::DONATION_CONFIRMATION => __('Donation Confirmation'),
            self::TRANSACTION_ALERT => __('Transaction Alert'),
            self::CAMPAIGN_REMINDER => __('Campaign Reminder'),
            self::NOTIFICATION => __('Notification'),
            self::GENERAL => __('General'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WELCOME => 'success',
            self::OTP_VERIFICATION => 'warning',
            self::DONATION_CONFIRMATION => 'primary',
            self::TRANSACTION_ALERT => 'info',
            self::CAMPAIGN_REMINDER => 'info',
            self::NOTIFICATION => 'gray',
            self::GENERAL => 'secondary',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::WELCOME => Heroicon::HandRaised,
            self::OTP_VERIFICATION => Heroicon::ShieldCheck,
            self::DONATION_CONFIRMATION => Heroicon::Banknotes,
            self::TRANSACTION_ALERT => Heroicon::Bell,
            self::CAMPAIGN_REMINDER => Heroicon::Megaphone,
            self::NOTIFICATION => Heroicon::ChatBubbleLeftRight,
            self::GENERAL => Heroicon::ChatBubbleLeftRight,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::WELCOME => __('Welcome SMS sent to new users'),
            self::OTP_VERIFICATION => __('OTP code for verification'),
            self::DONATION_CONFIRMATION => __('SMS confirmation after donation'),
            self::TRANSACTION_ALERT => __('Transaction status alert'),
            self::CAMPAIGN_REMINDER => __('Campaign reminder message'),
            self::NOTIFICATION => __('General notification SMS'),
            self::GENERAL => __('General purpose SMS template'),
        };
    }
}
