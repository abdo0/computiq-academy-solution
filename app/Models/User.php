<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use ActivityLoggable, HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'real_name',
        'employment_id',
        'email',
        'is_active',
        'active_role',
        'locale',
        'language',
        'phone',
        'mobile',
        'skype_id',
        'profile_photo_path',

        'password',
        'provider',
        'provider_id',
        'last_activity_at',
        'country_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected string $guard_name = 'student';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
            'is_active' => 'boolean',
            'active_role' => 'string',
            'last_activity_at' => 'datetime',
        ];
    }

    public static function appRoleMap(): array
    {
        return [
            'student' => 'Student',
            'hr' => 'HR',
            'organization' => 'Organization',
        ];
    }

    // LogsActivity Trait: Define which events to log
    protected static function logAttributes(): array
    {
        return [
            'name',
            'real_name',
            'email',
        ];
    }

    protected static function logName(): string
    {
        return 'user'; // Define a log name for this model
    }

    protected static function logOnlyDirty(): bool
    {
        return true; // Only log attributes that have changed
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "User {$this->name} was {$eventName}"; // Customize the event description
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Register media collections for the user.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();
    }

    /**
     * Register media conversions for the user.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('avatar', 'cover');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('cover');
    }

    // boot method
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($user) {

            // dirty fields
            $dirtyFields = $user->getDirty();
            // info('Dirty fields: ' . json_encode($dirtyFields));
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withPivot(['order_id', 'transaction_id', 'enrolled_at'])
            ->withTimestamps();
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(CourseLessonProgress::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(CourseExamAttempt::class);
    }

    public function courseCertificates(): HasMany
    {
        return $this->hasMany(CourseCertificate::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function ensureDefaultAppRole(): void
    {
        if (! $this->roles()->where('guard_name', $this->guard_name)->exists()) {
            $this->assignRole(
                SpatieRole::findOrCreate(self::appRoleMap()['student'], $this->guard_name)
            );
        }

        if (! $this->active_role) {
            $this->forceFill(['active_role' => 'student'])->save();
        }
    }

    public function availableAppRoles(): array
    {
        $assignedRoleNames = $this->roles()
            ->where('guard_name', $this->guard_name)
            ->pluck('name')
            ->all();

        $availableRoles = [];

        foreach (self::appRoleMap() as $slug => $roleName) {
            if (in_array($roleName, $assignedRoleNames, true)) {
                $availableRoles[] = $slug;
            }
        }

        return $availableRoles ?: ['student'];
    }

    public function resolvedActiveRole(): string
    {
        $availableRoles = $this->availableAppRoles();
        $activeRole = $this->active_role ?: 'student';

        return in_array($activeRole, $availableRoles, true) ? $activeRole : 'student';
    }
}
