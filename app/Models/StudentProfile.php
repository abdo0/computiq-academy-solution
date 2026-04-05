<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'university',
        'department',
        'degree',
        'start_year',
        'graduation_year',
        'academic_status',
        'headline',
        'short_bio',
        'city',
        'country',
        'preferred_role',
        'preferred_city',
        'job_available',
        'internship_available',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'skills',
        'projects',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'graduation_year' => 'integer',
        'job_available' => 'boolean',
        'internship_available' => 'boolean',
        'skills' => 'array',
        'projects' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
