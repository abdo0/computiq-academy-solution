<?php

namespace App\Services;

use App\Exports\ActivityLogExcelExport;
use App\Models\ActivityLog;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityLogExcelService
{
    /**
     * Export activity logs to Excel file
     */
    public function exportActivityLogs($activityLogs = null, ?string $filename = null): BinaryFileResponse
    {
        $filename = $filename ?? 'activity_logs_export_'.now()->format('Y_m_d_H_i_s').'.xlsx';

        return Excel::download(new ActivityLogExcelExport($activityLogs), $filename);
    }

    /**
     * Get activity logs with applied filters for export
     */
    public function getFilteredActivityLogs(array $filters = [])
    {
        $query = ActivityLog::with(['user']);

        // Add branch relationship if Branch model exists
        if (class_exists(\App\Models\Branch::class)) {
            $query->with(['branch']);
        }

        // Apply filters
        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->get();
    }
}
