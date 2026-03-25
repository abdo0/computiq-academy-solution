<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class State extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations;

    protected $fillable = [
        'country_id',
        'code',
        'name',
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
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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
