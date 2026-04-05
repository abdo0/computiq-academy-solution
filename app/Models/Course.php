<?php

namespace App\Models;

use App\Services\Learning\VideoEmbedService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Course extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'course_category_id',
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'image',
        'promo_video_source_type',
        'promo_video_provider',
        'promo_video_url',
        'promo_embed_url',
        'instructor_name',
        'instructor_image',
        'rating',
        'review_count',
        'duration_hours',
        'students_count',
        'price',
        'old_price',
        'is_active',
        'is_live',
        'delivery_type',
        'is_best_seller',
        'sort_order',
    ];

    public $translatable = [
        'title',
        'short_description',
        'description',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_live' => 'boolean',
        'is_best_seller' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $course) {
            if ($course->promo_video_source_type === 'embed') {
                $normalized = VideoEmbedService::normalize($course->promo_video_url);
                $course->promo_video_provider = $normalized['provider'];
                $course->promo_video_url = $normalized['video_url'];
                $course->promo_embed_url = $normalized['embed_url'];

                return;
            }

            if ($course->promo_video_source_type === 'upload') {
                $course->promo_video_provider = null;
                $course->promo_video_url = null;
                $course->promo_embed_url = null;

                return;
            }

            $course->promo_video_source_type = null;
            $course->promo_video_provider = null;
            $course->promo_video_url = null;
            $course->promo_embed_url = null;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(CourseLessonProgress::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(CourseExamAttempt::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(CourseCertificate::class);
    }

    public function certificateTemplate(): HasOne
    {
        return $this->hasOne(CourseCertificateTemplate::class);
    }

    public function certificateTemplates(): HasMany
    {
        return $this->hasMany(CourseCertificateTemplate::class);
    }

    public function exams(): HasManyThrough
    {
        return $this->hasManyThrough(CourseExam::class, CourseModule::class, 'course_id', 'course_module_id')
            ->orderByDesc('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class)->latest();
    }

    public function learningPaths(): BelongsToMany
    {
        return $this->belongsToMany(LearningPath::class, 'learning_path_course')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot(['order_id', 'transaction_id', 'enrolled_at'])
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('promo_video')
            ->singleFile()
            ->acceptsMimeTypes([
                'video/mp4',
                'video/quicktime',
                'video/webm',
                'video/x-msvideo',
                'video/x-matroska',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        //
    }
}
