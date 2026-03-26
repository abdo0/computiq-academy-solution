<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Course extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'course_category_id',
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'image',
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
}
