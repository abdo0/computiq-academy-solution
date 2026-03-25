<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Exports\RoleExporter;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus),

            ExportAction::make()
                ->label(__('Export Roles'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(RoleExporter::class)
                ->fileName(fn () => 'roles-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All'))
                ->icon('heroicon-o-shield-check')
                ->badge(static::getModel()::count()),
            'active' => Tab::make(__('Active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(static::getModel()::where('is_active', true)->count()),
            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(static::getModel()::where('is_active', false)->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
