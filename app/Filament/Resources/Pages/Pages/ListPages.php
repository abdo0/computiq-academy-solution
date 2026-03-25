<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Exports\PageExporter;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus),

            ExportAction::make()
                ->label(__('Export Pages'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(PageExporter::class)
                ->fileName(fn () => 'pages-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3),
        ];
    }

    public function getTabs(): array
    {
        $model = static::getModel();

        return [
            __('All') => Tab::make()
                ->icon('heroicon-o-squares-2x2')
                ->badge($model::count())
                ->badgeColor('primary'),

            __('Published') => Tab::make()
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', true))
                ->badge($model::where('is_published', true)->count())
                ->badgeColor('success'),

            __('Draft') => Tab::make()
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', false))
                ->badge($model::where('is_published', false)->count())
                ->badgeColor('gray'),

            __('Trashed') => Tab::make()
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($model::onlyTrashed()->count())
                ->badgeColor('danger'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return __('Published');
    }
}
