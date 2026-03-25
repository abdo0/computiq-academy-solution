<?php

namespace App\Filament\Clusters\Templates\Resources\EmailTemplates\Pages;

use App\Filament\Clusters\Templates\Resources\EmailTemplates\EmailTemplateResource;
use App\Filament\Exports\EmailTemplateExporter;
use App\Filament\Imports\EmailTemplateImporter;
use App\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListEmailTemplates extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label(__('Import Templates'))
                ->icon(Heroicon::ArrowUpTray)
                ->color('success')
                ->outlined()
                ->importer(EmailTemplateImporter::class),
            ExportAction::make()
                ->label(__('Export Templates'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(EmailTemplateExporter::class)
                ->fileName(fn () => 'email-templates-'.now()->format('Y-m-d-H-i-s'))
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
