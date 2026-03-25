<?php

namespace App\Models;

use App\Enums\SmsTemplatePurpose;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class SmsTemplate extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations, SoftDeletes;

    public $translatable = ['name', 'content'];

    protected $fillable = [
        'code',
        'name',
        'content',
        'purpose',
        'is_default',
        'is_active',
        'variables',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'content' => 'array',
            'purpose' => SmsTemplatePurpose::class,
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'variables' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForPurpose($query, SmsTemplatePurpose $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeDefaultForPurpose($query, SmsTemplatePurpose $purpose)
    {
        return $query->where('purpose', $purpose)
            ->where('is_default', true)
            ->where('is_active', true);
    }
}
