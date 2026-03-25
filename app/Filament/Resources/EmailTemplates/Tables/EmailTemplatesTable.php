<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Enums\EmailTemplatePurpose;
use App\Filament\Exports\EmailTemplateExporter;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->icon(Heroicon::Hashtag)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->icon(Heroicon::DocumentText)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purpose')
                    ->label(__('Purpose'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('is_default')
                    ->label(__('Default'))
                    ->icon(Heroicon::Star)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No'))
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->sortable(),

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
                SelectFilter::make('purpose')
                    ->label(__('Purpose'))
                    ->options(EmailTemplatePurpose::class)
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_default')
                    ->label(__('Default'))
                    ->boolean(),

                TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
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
                        ->exporter(EmailTemplateExporter::class)
                        ->fileName(fn () => 'selected-email-templates-'.now()->format('Y-m-d-H-i-s'))
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
