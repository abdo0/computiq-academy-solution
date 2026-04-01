<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'course_lesson_progress';

    protected $fillable = [
        'user_id',
        'course_id',
        'course_module_id',
        'course_lesson_id',
        'status',
        'last_position_seconds',
        'opened_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_position_seconds' => 'integer',
            'opened_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }
}
