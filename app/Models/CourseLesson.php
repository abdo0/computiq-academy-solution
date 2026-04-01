<?php

namespace App\Models;

use App\Services\Learning\VideoEmbedService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class CourseLesson extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'course_module_id',
        'title',
        'description',
        'duration_minutes',
        'content_type',
        'video_source_type',
        'video_provider',
        'video_url',
        'embed_url',
        'is_free',
        'is_active',
        'sort_order',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'description' => 'array',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $lesson) {
            if ($lesson->video_source_type === 'embed') {
                $normalized = VideoEmbedService::normalize($lesson->video_url);
                $lesson->video_provider = $normalized['provider'];
                $lesson->video_url = $normalized['video_url'];
                $lesson->embed_url = $normalized['embed_url'];

                return;
            }

            $lesson->video_provider = null;
            $lesson->video_url = null;
            $lesson->embed_url = null;
        });
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CourseLessonProgress::class, 'course_lesson_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')
            ->singleFile()
            ->acceptsMimeTypes([
                'video/mp4',
                'video/quicktime',
                'video/webm',
                'video/x-msvideo',
                'video/x-matroska',
            ]);

        $this->addMediaCollection('documents');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        //
    }
}
