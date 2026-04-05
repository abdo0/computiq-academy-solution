<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CourseCertificateTemplate extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'course_id',
        'x1',
        'y1',
        'x2',
        'y2',
        'text_color',
        'font_size',
        'font_family',
        'text_align',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'x1' => 'float',
            'y1' => 'float',
            'x2' => 'float',
            'y2' => 'float',
            'font_size' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('template_image')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/svg+xml',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media?->mime_type === 'image/svg+xml') {
            return;
        }

        $this->addMediaConversion('preview')
            ->width(1400)
            ->performOnCollections('template_image');
    }

    public function hasUsableImage(): bool
    {
        return $this->hasMedia('template_image');
    }
}
