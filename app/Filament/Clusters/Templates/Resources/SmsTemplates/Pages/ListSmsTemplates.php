<?php

namespace App\Filament\Clusters\Templates\Resources\SmsTemplates\Pages;

use App\Filament\Clusters\Templates\Resources\SmsTemplates\SmsTemplateResource;
use App\Filament\Exports\SmsTemplateExporter;
use App\Filament\Imports\SmsTemplateImporter;
use App\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSmsTemplates extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = SmsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label(__('Import Templates'))
                ->icon(Heroicon::ArrowUpTray)
                ->color('success')
                ->outlined()
                ->importer(SmsTemplateImporter::class),
            ExportAction::make()
                ->label(__('Export Templates'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(SmsTemplateExporter::class)
                ->fileName(fn () => 'sms-templates-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3),
            CreateAction::make()
                ->label(__('Create Template')),
        ];
    }
}
