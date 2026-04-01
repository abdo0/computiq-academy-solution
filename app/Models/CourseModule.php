<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;

class CourseModule extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'course_id',
        'title',
        'sort_order',
    ];

    public $translatable = ['title'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('sort_order');
    }

    public function exam(): HasOne
    {
        return $this->hasOne(CourseExam::class)->latestOfMany();
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(CourseLessonProgress::class);
    }
}
