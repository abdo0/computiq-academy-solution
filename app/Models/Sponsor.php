<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $fillable = [
        'name',
        'image',
        'type',
        'is_active',
        'sort_order',
    ];

    /**
     * Scope a query to only include active sponsors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
