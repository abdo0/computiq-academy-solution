<?php

namespace App\Models;

use App\Enums\EmailTemplatePurpose;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class EmailTemplate extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations, SoftDeletes;

    public $translatable = ['name', 'subject', 'body'];

    protected $fillable = [
        'code',
        'name',
        'subject',
        'body',
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
            'subject' => 'array',
            'body' => 'array',
            'purpose' => EmailTemplatePurpose::class,
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

    public function scopeForPurpose($query, EmailTemplatePurpose $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeDefaultForPurpose($query, EmailTemplatePurpose $purpose)
    {
        return $query->where('purpose', $purpose)
            ->where('is_default', true)
            ->where('is_active', true);
    }
}
