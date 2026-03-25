<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ActivityAction: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case LOGIN_FAILED = 'login_failed';
    case PASSWORD_CHANGED = 'password_changed';
    case PROFILE_UPDATED = 'profile_updated';
    case EXPORTED = 'exported';
    case IMPORTED = 'imported';
    case VIEWED = 'viewed';
    case DOWNLOADED = 'downloaded';
    case UPLOADED = 'uploaded';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case ASSIGNED = 'assigned';
    case UNASSIGNED = 'unassigned';
    case STATUS_CHANGED = 'status_changed';
    case COMMENTED = 'commented';
    case NOTIFIED = 'notified';
    case SYSTEM = 'system';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREATED => __('Created'),
            self::UPDATED => __('Updated'),
            self::DELETED => __('Deleted'),
            self::RESTORED => __('Restored'),
            self::LOGIN => __('Login'),
            self::LOGOUT => __('Logout'),
            self::LOGIN_FAILED => __('Failed Login'),
            self::PASSWORD_CHANGED => __('Password Changed'),
            self::PROFILE_UPDATED => __('Profile Updated'),
            self::EXPORTED => __('Exported'),
            self::IMPORTED => __('Imported'),
            self::VIEWED => __('Viewed'),
            self::DOWNLOADED => __('Downloaded'),
            self::UPLOADED => __('Uploaded'),
            self::APPROVED => __('Approved'),
            self::REJECTED => __('Rejected'),
            self::ASSIGNED => __('Assigned'),
            self::UNASSIGNED => __('Unassigned'),
            self::STATUS_CHANGED => __('Status Changed'),
            self::COMMENTED => __('Commented'),
            self::NOTIFIED => __('Notified'),
            self::SYSTEM => __('System'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CREATED => 'success',
            self::UPDATED => 'warning',
            self::DELETED => 'danger',
            self::RESTORED => 'info',
            self::LOGIN => 'success',
            self::LOGOUT => 'gray',
            self::LOGIN_FAILED => 'danger',
            self::PASSWORD_CHANGED => 'warning',
            self::PROFILE_UPDATED => 'info',
            self::EXPORTED => 'info',
            self::IMPORTED => 'success',
            self::VIEWED => 'gray',
            self::DOWNLOADED => 'info',
            self::UPLOADED => 'success',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::ASSIGNED => 'info',
            self::UNASSIGNED => 'warning',
            self::STATUS_CHANGED => 'warning',
            self::COMMENTED => 'info',
            self::NOTIFIED => 'info',
            self::SYSTEM => 'gray',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::CREATED => Heroicon::PlusCircle,
            self::UPDATED => Heroicon::PencilSquare,
            self::DELETED => Heroicon::Trash,
            self::RESTORED => Heroicon::ArrowUturnLeft,
            self::LOGIN => Heroicon::ArrowRightOnRectangle,
            self::LOGOUT => Heroicon::ArrowLeftOnRectangle,
            self::LOGIN_FAILED => Heroicon::ExclamationTriangle,
            self::PASSWORD_CHANGED => Heroicon::Key,
            self::PROFILE_UPDATED => Heroicon::UserCircle,
            self::EXPORTED => Heroicon::ArrowDownTray,
            self::IMPORTED => Heroicon::ArrowUpTray,
            self::VIEWED => Heroicon::Eye,
            self::DOWNLOADED => Heroicon::ArrowDownTray,
            self::UPLOADED => Heroicon::ArrowUpTray,
            self::APPROVED => Heroicon::CheckCircle,
            self::REJECTED => Heroicon::XCircle,
            self::ASSIGNED => Heroicon::UserPlus,
            self::UNASSIGNED => Heroicon::UserMinus,
            self::STATUS_CHANGED => Heroicon::ArrowPath,
            self::COMMENTED => Heroicon::ChatBubbleLeftRight,
            self::NOTIFIED => Heroicon::Bell,
            self::SYSTEM => Heroicon::Cog6Tooth,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::CREATED => __('A new record was created'),
            self::UPDATED => __('An existing record was modified'),
            self::DELETED => __('A record was deleted'),
            self::RESTORED => __('A deleted record was restored'),
            self::LOGIN => __('User successfully logged in'),
            self::LOGOUT => __('User logged out'),
            self::LOGIN_FAILED => __('Failed login attempt'),
            self::PASSWORD_CHANGED => __('User password was changed'),
            self::PROFILE_UPDATED => __('User profile was updated'),
            self::EXPORTED => __('Data was exported'),
            self::IMPORTED => __('Data was imported'),
            self::VIEWED => __('Record was viewed'),
            self::DOWNLOADED => __('File was downloaded'),
            self::UPLOADED => __('File was uploaded'),
            self::APPROVED => __('Record was approved'),
            self::REJECTED => __('Record was rejected'),
            self::ASSIGNED => __('Record was assigned to user'),
            self::UNASSIGNED => __('Record assignment was removed'),
            self::STATUS_CHANGED => __('Record status was changed'),
            self::COMMENTED => __('Comment was added'),
            self::NOTIFIED => __('Notification was sent'),
            self::SYSTEM => __('System-generated activity'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }

    public static function getCommonActions(): array
    {
        return [
            self::CREATED,
            self::UPDATED,
            self::DELETED,
            self::LOGIN,
            self::LOGOUT,
            self::LOGIN_FAILED,
        ];
    }

    public static function getSystemActions(): array
    {
        return [
            self::EXPORTED,
            self::IMPORTED,
            self::SYSTEM,
            self::NOTIFIED,
        ];
    }
}
