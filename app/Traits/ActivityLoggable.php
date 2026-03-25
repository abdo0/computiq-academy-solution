<?php

namespace App\Traits;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait ActivityLoggable
{
    /**
     * Boot the trait and register model events.
     */
    protected static function bootActivityLoggable(): void
    {
        if (app()->runningInConsole() || ! request()->is('admin*')) {
            return;
        }
        // Log when a model is created
        static::created(function (Model $model) {
            $model->logActivity(ActivityAction::CREATED, '', $model);
        });

        // Log when a model is updated
        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            // Filter out timestamps and other system fields
            $filteredChanges = $model->filterActivityChanges($changes, $original);

            if (! empty($filteredChanges)) {
                $model->logActivity(
                    ActivityAction::UPDATED,
                    '',
                    $model,
                    [
                        'changes' => $filteredChanges,
                        'original' => array_intersect_key($original, $filteredChanges),
                    ]
                );
            }
        });

        // Log when a model is deleted
        static::deleted(function (Model $model) {
            $model->logActivity(ActivityAction::DELETED, '', $model);
        });

        // Log when a model is restored (soft delete) - only if model uses soft deletes
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function (Model $model) {
                $model->logActivity(ActivityAction::RESTORED, '', $model);
            });
        }
    }

    /**
     * Log an activity for this model.
     */
    public function logActivity(
        ActivityAction|string $action,
        string $description = '',
        ?Model $model = null,
        array $properties = []
    ): ActivityLog {
        $model = $model ?? $this;

        return ActivityLog::log(
            action: $action,
            description: $description,
            model: $model,
            properties: $properties
        );
    }

    /**
     * Log a custom activity with description.
     */
    public function logCustomActivity(
        ActivityAction|string $action,
        string $description,
        array $properties = []
    ): ActivityLog {
        return $this->logActivity($action, $description, $this, $properties);
    }

    /**
     * Log a view activity.
     */
    public function logViewed(string $description = ''): ActivityLog
    {
        $description = $description ?: __(':model was viewed', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::VIEWED, $description);
    }

    /**
     * Log an export activity.
     */
    public function logExported(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model data was exported', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::EXPORTED, $description, $this, $properties);
    }

    /**
     * Log an import activity.
     */
    public function logImported(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model data was imported', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::IMPORTED, $description, $this, $properties);
    }

    /**
     * Log an approval activity.
     */
    public function logApproved(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model was approved', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::APPROVED, $description, $this, $properties);
    }

    /**
     * Log a rejection activity.
     */
    public function logRejected(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model was rejected', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::REJECTED, $description, $this, $properties);
    }

    /**
     * Log an assignment activity.
     */
    public function logAssigned(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model was assigned', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::ASSIGNED, $description, $this, $properties);
    }

    /**
     * Log an unassignment activity.
     */
    public function logUnassigned(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model assignment was removed', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::UNASSIGNED, $description, $this, $properties);
    }

    /**
     * Log a status change activity.
     */
    public function logStatusChanged(string $from, string $to, string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __(':model status was changed from :from to :to', [
            'model' => __(class_basename($this)),
            'from' => $from,
            'to' => $to,
        ]);

        return $this->logActivity(ActivityAction::STATUS_CHANGED, $description, $this, array_merge($properties, [
            'from' => $from,
            'to' => $to,
        ]));
    }

    /**
     * Log a comment activity.
     */
    public function logCommented(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __('Comment was added to :model', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::COMMENTED, $description, $this, $properties);
    }

    /**
     * Log a notification activity.
     */
    public function logNotified(string $description = '', array $properties = []): ActivityLog
    {
        $description = $description ?: __('Notification was sent for :model', ['model' => __(class_basename($this))]);

        return $this->logActivity(ActivityAction::NOTIFIED, $description, $this, $properties);
    }

    /**
     * Log a system activity.
     */
    public function logSystemActivity(string $description, array $properties = []): ActivityLog
    {
        return $this->logActivity(ActivityAction::SYSTEM, $description, $this, $properties);
    }

    /**
     * Filter changes to exclude system fields and sensitive data.
     */
    public function filterActivityChanges(array $changes, array $original): array
    {
        // Fields to exclude from activity logging
        $excludedFields = [
            'updated_at',
            'created_at',
            'deleted_at',
            'remember_token',
            'email_verified_at',
            'password',
            'password_confirmation',
            'api_token',
            'last_login_at',
            'last_activity_at',
        ];

        // Add model-specific excluded fields if defined
        if (method_exists($this, 'getActivityExcludedFields')) {
            $excludedFields = array_merge($excludedFields, $this->getActivityExcludedFields());
        }

        // Filter out excluded fields
        $filteredChanges = array_diff_key($changes, array_flip($excludedFields));

        // Additional filtering for sensitive data patterns
        $filteredChanges = $this->filterSensitiveData($filteredChanges);

        return $filteredChanges;
    }

    /**
     * Filter sensitive data from changes.
     */
    protected function filterSensitiveData(array $changes): array
    {
        $sensitivePatterns = [
            'password',
            'secret',
            'token',
            'key',
            'auth',
            'credential',
        ];

        foreach ($changes as $field => $value) {
            $fieldLower = strtolower($field);

            foreach ($sensitivePatterns as $pattern) {
                if (str_contains($fieldLower, $pattern)) {
                    unset($changes[$field]);
                    break;
                }
            }
        }

        return $changes;
    }

    /**
     * Get activity logs for this model.
     */
    public function activityLogs()
    {
        return ActivityLog::where('model_type', get_class($this))
            ->where('model_id', $this->getKey())
            ->latest();
    }

    /**
     * Get recent activity logs for this model.
     */
    public function recentActivityLogs(int $days = 7)
    {
        return $this->activityLogs()->recent($days);
    }

    /**
     * Get activity logs by action for this model.
     */
    public function activityLogsByAction(ActivityAction|string $action)
    {
        return $this->activityLogs()->byAction($action);
    }

    /**
     * Get the last activity for this model.
     */
    public function lastActivity(): ?ActivityLog
    {
        return $this->activityLogs()->first();
    }

    /**
     * Check if this model has any activity logs.
     */
    public function hasActivityLogs(): bool
    {
        return $this->activityLogs()->exists();
    }

    /**
     * Get activity count for this model.
     */
    public function getActivityCountAttribute(): int
    {
        return $this->activityLogs()->count();
    }

    /**
     * Get activity count by action for this model.
     */
    public function getActivityCountByAction(ActivityAction|string $action): int
    {
        return $this->activityLogsByAction($action)->count();
    }

    /**
     * Get activity summary for this model.
     */
    public function getActivitySummary(): array
    {
        $summary = [];

        foreach (ActivityAction::cases() as $action) {
            $count = $this->getActivityCountByAction($action);
            if ($count > 0) {
                $summary[$action->value] = [
                    'action' => $action,
                    'count' => $count,
                    'label' => $action->getLabel(),
                ];
            }
        }

        return $summary;
    }

    /**
     * Backwards-compatible no-op: disable activity logging for the next operation.
     */
    public function withoutActivityLogging(): static
    {
        return $this;
    }

    /**
     * Backwards-compatible no-op: enable activity logging (default behavior).
     */
    public function withActivityLogging(): static
    {
        return $this;
    }

}
