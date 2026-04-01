<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Exports\PageExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->icon(Heroicon::Document)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->icon(Heroicon::Hashtag)
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('is_published')
                    ->label(__('Published'))
                    ->icon(Heroicon::CheckCircle)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No'))
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('show_in_header')
                    ->label(__('Show in Header'))
                    ->icon(Heroicon::ArrowUpOnSquare)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No'))
                    ->color(fn ($state) => $state ? 'info' : 'secondary')
                    ->sortable(),

                TextColumn::make('show_in_footer')
                    ->label(__('Show in Footer'))
                    ->icon(Heroicon::ArrowDownOnSquare)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No'))
                    ->color(fn ($state) => $state ? 'info' : 'secondary')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon(Heroicon::Clock)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_published')
                    ->label(__('Published'))
                    ->options([
                        true => __('Published'),
                        false => __('Draft'),
                    ])
                    ->searchable()
                    ->preload(),

                SelectFilter::make('show_in_header')
                    ->label(__('Show in Header'))
                    ->options([
                        true => __('Yes'),
                        false => __('No'),
                    ])
                    ->searchable()
                    ->preload(),

                SelectFilter::make('show_in_footer')
                    ->label(__('Show in Footer'))
                    ->options([
                        true => __('Yes'),
                        false => __('No'),
                    ])
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(3)
            ->deferFilters(false)
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip(__('Edit')),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip(__('Delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('Export Selected'))
                        ->icon(Heroicon::ArrowDownTray)
                        ->color('primary')
                        ->outlined()
                        ->exporter(PageExporter::class)
                        ->fileName(fn () => 'selected-pages-'.now()->format('Y-m-d-H-i-s'))
                        ->formats([
                            ExportFormat::Xlsx,
                            ExportFormat::Csv,
                        ])
                        ->columnMappingColumns(3),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->defaultSort('id', 'asc');
    }
}
