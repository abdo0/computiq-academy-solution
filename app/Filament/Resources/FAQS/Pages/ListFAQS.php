<?php

namespace App\Filament\Resources\FAQS\Pages;

use App\Filament\Exports\FAQExporter;
use App\Filament\Resources\FAQS\FAQResource;
use App\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListFAQS extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = FAQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus),

            ExportAction::make()
                ->label(__('Export FAQs'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(FAQExporter::class)
                ->fileName(fn () => 'faqs-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3),
        ];
    }
}
