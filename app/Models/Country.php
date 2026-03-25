<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations;

    protected $fillable = [
        'code',
        'name',
        'iso2',
        'phone_code',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class)->orderBy('sort_order');
    }

    public function getTitleAttribute(): string
    {
        $title = $this->getTranslation('name', app()->getLocale());

        if (blank($title) && config()->has('app.fallback_locale')) {
            $title = $this->getTranslation('name', config('app.fallback_locale'));
        }

        if (blank($title) && is_array($this->name)) {
            $title = collect($this->name)->first();
        }

        return $title ?? '';
    }
}
