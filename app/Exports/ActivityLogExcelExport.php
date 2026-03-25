<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogExcelExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected $activityLogs;

    public function __construct($activityLogs = null)
    {
        $this->activityLogs = $activityLogs;
    }

    /**
     * Get the collection of activity logs to export
     */
    public function collection()
    {
        if ($this->activityLogs) {
            return $this->activityLogs;
        }

        $query = ActivityLog::with(['user']);

        // Add branch relationship if Branch model exists
        if (class_exists(\App\Models\Branch::class)) {
            $query->with(['branch']);
        }

        return $query->latest()->get();
    }

    /**
     * Define the headings for the export
     */
    public function headings(): array
    {
        $headings = [
            __('ID'),
            __('Activity'),
            __('User'),
            __('Action Type'),
            __('Model'),
            __('Record ID'),
            __('Description'),
            __('IP Address'),
            __('User Agent'),
            __('Created At'),
            __('Updated At'),
        ];

        // Add branch column if Branch model exists
        if (class_exists(\App\Models\Branch::class)) {
            array_splice($headings, 3, 0, [__('Branch')]);
        }

        return $headings;
    }

    /**
     * Map each activity log to an array for export
     */
    public function map($activityLog): array
    {
        $data = [
            $activityLog->id,
            $activityLog->rendered_message,
            $activityLog->user?->name ?? __('System'),
            $activityLog->action_label ?? __('Unknown'),
            $activityLog->model_type ? __(class_basename($activityLog->model_type)) : __('Unknown'),
            $activityLog->model_id ?? __('N/A'),
            $activityLog->description ?? __('No description'),
            $activityLog->ip_address ?? __('N/A'),
            $activityLog->user_agent ?? __('N/A'),
            $activityLog->created_at ? $activityLog->created_at->format('Y-m-d H:i:s') : null,
            $activityLog->updated_at ? $activityLog->updated_at->format('Y-m-d H:i:s') : null,
        ];

        // Add branch data if Branch model exists
        if (class_exists(\App\Models\Branch::class)) {
            array_splice($data, 3, 0, [$activityLog->branch?->name ?? __('N/A')]);
        }

        return $data;
    }

    /**
     * Set column widths for better readability
     */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,  // ID
            'B' => 40, // Activity
            'C' => 20, // User
            'D' => 15, // Action Type
            'E' => 15, // Model
            'F' => 10, // Record ID
            'G' => 30, // Description
            'H' => 15, // IP Address
            'I' => 30, // User Agent
            'J' => 20, // Created At
            'K' => 20, // Updated At
        ];

        // Add branch column width if Branch model exists
        if (class_exists(\App\Models\Branch::class)) {
            $widths = [
                'A' => 8,  // ID
                'B' => 40, // Activity
                'C' => 20, // User
                'D' => 20, // Branch
                'E' => 15, // Action Type
                'F' => 15, // Model
                'G' => 10, // Record ID
                'H' => 30, // Description
                'I' => 15, // IP Address
                'J' => 30, // User Agent
                'K' => 20, // Created At
                'L' => 20, // Updated At
            ];
        }

        return $widths;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '4F81BD'], // Blue header
                ],
            ],
        ];
    }
}
