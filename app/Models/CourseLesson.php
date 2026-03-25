<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class CourseLesson extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'course_module_id',
        'title',
        'duration_minutes',
        'is_free',
        'sort_order',
    ];

    public $translatable = ['title'];

    protected $casts = [
        'is_free' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }
}
