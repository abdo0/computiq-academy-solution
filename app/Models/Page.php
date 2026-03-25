<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations, SoftDeletes;

    public $translatable = ['title', 'content', 'meta_title', 'meta_description'];

    protected $fillable = [
        'slug',
        'title',
        'content',
        'is_published',
        'sort_order',
        'meta_title',
        'meta_description',
        'show_in_header',
        'show_in_footer',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'show_in_header' => 'boolean',
            'show_in_footer' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getDisplayTitleAttribute(): string
    {
        $locale = app()->getLocale();
        $title = $this->getAttribute('title');

        if (is_array($title)) {
            return $title[$locale] ?? $title['en'] ?? $this->slug;
        }

        return $this->slug;
    }
}
