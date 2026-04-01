<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class CourseExam extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'course_module_id',
        'title',
        'pass_mark',
        'max_attempts',
        'time_limit_minutes',
        'is_active',
    ];

    public array $translatable = [
        'title',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'pass_mark' => 'integer',
            'max_attempts' => 'integer',
            'time_limit_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CourseExamQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CourseExamAttempt::class)->orderByDesc('id');
    }
}
