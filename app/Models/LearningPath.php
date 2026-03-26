<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class LearningPath extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'image',
        'icon',
        'color',
        'estimated_hours',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'title',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_hours' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Courses in this learning path
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'learning_path_course')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
