<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class CourseExamOption extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'course_exam_question_id',
        'option_text',
        'is_correct',
        'sort_order',
    ];

    public array $translatable = [
        'option_text',
    ];

    protected function casts(): array
    {
        return [
            'option_text' => 'array',
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CourseExamQuestion::class, 'course_exam_question_id');
    }
}
