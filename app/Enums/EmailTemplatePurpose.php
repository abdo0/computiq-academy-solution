<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EmailTemplatePurpose: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case WELCOME = 'welcome';
    case PASSWORD_RESET = 'password_reset';
    case PASSWORD_RESET_OTP = 'password_reset_otp';
    case EMAIL_VERIFICATION = 'email_verification';
    case EMAIL_OTP = 'email_otp';
    case DONATION_RECEIPT = 'donation_receipt';
    case CAMPAIGN_UPDATE = 'campaign_update';
    case TRANSACTION_CONFIRMATION = 'transaction_confirmation';
    case NOTIFICATION = 'notification';
    case GENERAL = 'general';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::WELCOME => __('Welcome Email'),
            self::PASSWORD_RESET => __('Password Reset'),
            self::PASSWORD_RESET_OTP => __('Password Reset OTP'),
            self::EMAIL_VERIFICATION => __('Email Verification'),
            self::EMAIL_OTP => __('Email OTP'),
            self::DONATION_RECEIPT => __('Donation Receipt'),
            self::CAMPAIGN_UPDATE => __('Campaign Update'),
            self::TRANSACTION_CONFIRMATION => __('Transaction Confirmation'),
            self::NOTIFICATION => __('Notification'),
            self::GENERAL => __('General'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WELCOME => 'success',
            self::PASSWORD_RESET => 'warning',
            self::PASSWORD_RESET_OTP => 'warning',
            self::EMAIL_VERIFICATION => 'info',
            self::EMAIL_OTP => 'info',
            self::DONATION_RECEIPT => 'primary',
            self::CAMPAIGN_UPDATE => 'info',
            self::TRANSACTION_CONFIRMATION => 'success',
            self::NOTIFICATION => 'gray',
            self::GENERAL => 'secondary',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::WELCOME => Heroicon::HandRaised,
            self::PASSWORD_RESET => Heroicon::Key,
            self::PASSWORD_RESET_OTP => Heroicon::Key,
            self::EMAIL_VERIFICATION => Heroicon::Envelope,
            self::EMAIL_OTP => Heroicon::DocumentText,
            self::DONATION_RECEIPT => Heroicon::DocumentText,
            self::CAMPAIGN_UPDATE => Heroicon::Megaphone,
            self::TRANSACTION_CONFIRMATION => Heroicon::CheckCircle,
            self::NOTIFICATION => Heroicon::Bell,
            self::GENERAL => Heroicon::Envelope,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::WELCOME => __('Welcome email sent to new users'),
            self::PASSWORD_RESET => __('Password reset email with reset link'),
            self::PASSWORD_RESET_OTP => __('Password reset email with OTP code'),
            self::EMAIL_VERIFICATION => __('Email verification message'),
            self::EMAIL_OTP => __('Email OTP verification code'),
            self::DONATION_RECEIPT => __('Receipt sent after donation'),
            self::CAMPAIGN_UPDATE => __('Campaign status or milestone updates'),
            self::TRANSACTION_CONFIRMATION => __('Transaction confirmation email'),
            self::NOTIFICATION => __('General notification email'),
            self::GENERAL => __('General purpose email template'),
        };
    }
}
