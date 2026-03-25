<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\UserResource;
use App\Traits\HasActiveStatusTabs;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListUsers extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('Users');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('New User'))
                ->icon(Heroicon::Plus),

            ExportAction::make()
                ->label(__('Export Users'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('primary')
                ->outlined()
                ->exporter(UserExporter::class)
                ->fileName(fn () => 'users-'.now()->format('Y-m-d-H-i-s'))
                ->formats([
                    ExportFormat::Xlsx,
                    ExportFormat::Csv,
                ])
                ->columnMappingColumns(3), // 3-column layout for column selection
        ];
    }
}
