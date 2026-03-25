<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Article extends Model implements HasMedia
{
    use ActivityLoggable, HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    public $translatable = ['title', 'content', 'excerpt'];

    protected $fillable = [
        'slug',
        'title',
        'content',
        'excerpt',
        'article_category_id',
        'author_id',
        'featured_image',
        'published_at',
        'is_published',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'excerpt' => 'array',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
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
