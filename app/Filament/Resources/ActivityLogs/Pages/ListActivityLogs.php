<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Exports\ActivityLogExporter;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    public function getTitle(): string
    {
        return __('Activity Logs');
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label(__('Export Activity Logs'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(ActivityLogExporter::class)
                ->fileName(fn () => 'activity-logs-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3), // 3-column layout for column selection
        ];
    }
}
