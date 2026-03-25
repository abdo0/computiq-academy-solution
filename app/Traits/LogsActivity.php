<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            self::logActivity('created', 'Created '.class_basename($model), $model);
        });

        static::updated(function (Model $model) {
            if ($model->wasChanged()) {
                self::logActivity('updated', 'Updated '.class_basename($model), $model, [
                    'old' => $model->getOriginal(),
                    'attributes' => $model->getChanges(),
                ]);
            }
        });

        static::deleted(function (Model $model) {
            self::logActivity('deleted', 'Deleted '.class_basename($model), $model);
        });
    }

    protected static function logActivity(string $action, string $description, Model $model, array $properties = [])
    {
        ActivityLog::log($action, '', $model, $properties);
        // ActivityLog::log($action, $description, $model, $properties);
    }
}
