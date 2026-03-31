<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements FilamentUser
{
    use ActivityLoggable, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'last_activity_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected string $guard_name = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function hasAdminAccess(): bool
    {
        return $this->is_active;
    }

    protected static function logAttributes(): array
    {
        return [
            'name',
            'email',
            'is_active',
        ];
    }

    protected static function logName(): string
    {
        return 'admin';
    }

    protected static function logOnlyDirty(): bool
    {
        return true;
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Admin {$this->name} was {$eventName}";
    }
}
