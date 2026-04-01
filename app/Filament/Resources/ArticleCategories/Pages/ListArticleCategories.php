<?php

namespace App\Filament\Resources\ArticleCategories\Pages;

use App\Filament\Exports\ArticleCategoryExporter;
use App\Filament\Resources\ArticleCategories\ArticleCategoryResource;
use App\Traits\HasSoftDeleteTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListArticleCategories extends ListRecords
{
    use HasSoftDeleteTabs;

    protected static string $resource = ArticleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Create Article Category'))
                ->icon(Heroicon::Plus),

            ExportAction::make()
                ->label(__('Export Article Categories'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(ArticleCategoryExporter::class)
                ->fileName(fn () => 'article-categories-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3),
        ];
    }
}
