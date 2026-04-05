<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class CourseCategory extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'is_active',
        'show_on_home',
        'sort_order',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_home' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flushCategoryCaches = static function (): void {
            Cache::forget('course_categories_api');
            Cache::forget('home_course_categories');
        };

        static::saved($flushCategoryCaches);
        static::deleted($flushCategoryCaches);
        static::restored($flushCategoryCaches);
        static::forceDeleted($flushCategoryCaches);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CourseCategory::class, 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        //
    }
}
