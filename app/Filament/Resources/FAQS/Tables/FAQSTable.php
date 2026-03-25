<?php

namespace App\Filament\Resources\FAQS\Tables;

use App\Filament\Exports\FAQExporter;
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

class FAQSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label(__('Question'))
                    ->icon(Heroicon::QuestionMarkCircle)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category')
                    ->label(__('Category'))
                    ->icon(Heroicon::Tag)
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->icon(Heroicon::CheckCircle)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Active') : __('Inactive'))
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon(Heroicon::Clock)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('Category'))
                    ->options(function () {
                        return \App\Models\FAQ::whereNotNull('category')
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(1)
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
                        ->exporter(FAQExporter::class)
                        ->fileName(fn () => 'selected-faqs-'.now()->format('Y-m-d-H-i-s'))
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
