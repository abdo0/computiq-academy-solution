<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory, HasTranslations;

    protected $guarded = [];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'extra_data' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        $clearCaches = function () {
            \Illuminate\Support\Facades\Cache::forget('home_dynamic_sections');
            \Illuminate\Support\Facades\Cache::forget('home_sections_api');
            \Illuminate\Support\Facades\Cache::forget('hero_content');
            \Illuminate\Support\Facades\Cache::forget('site_settings_v2');
        };

        static::saved($clearCaches);
        static::deleted($clearCaches);
    }
}
