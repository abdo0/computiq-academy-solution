<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

class CourseExamQuestion extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'course_exam_id',
        'question',
        'sort_order',
    ];

    public array $translatable = [
        'question',
    ];

    protected function casts(): array
    {
        return [
            'question' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CourseExam::class, 'course_exam_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CourseExamOption::class)->orderBy('sort_order');
    }

    public function normalizeCorrectOptions(): void
    {
        /** @var Collection<int, CourseExamOption> $options */
        $options = $this->options()->orderBy('sort_order')->get();

        if ($options->isEmpty()) {
            return;
        }

        $correctOption = $options->firstWhere('is_correct', true) ?? $options->first();

        if (! $correctOption) {
            return;
        }

        $this->options()
            ->whereKeyNot($correctOption->getKey())
            ->update(['is_correct' => false]);

        if (! $correctOption->is_correct) {
            $correctOption->update(['is_correct' => true]);
        }
    }
}
