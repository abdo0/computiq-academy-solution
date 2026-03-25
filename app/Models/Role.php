<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Role extends \Spatie\Permission\Models\Role
{
    use ActivityLoggable, HasTranslations;

    public $translatable = ['name', 'display_name'];

    /** Scopes */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
