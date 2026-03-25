<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Filament\Exports\ArticleExporter;
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

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->icon(Heroicon::DocumentText)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->icon(Heroicon::Folder)
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('author.name')
                    ->label(__('Author'))
                    ->icon(Heroicon::User)
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('is_published')
                    ->label(__('Published'))
                    ->icon(Heroicon::CheckCircle)
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Yes') : __('No'))
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label(__('Published At'))
                    ->icon(Heroicon::Calendar)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon(Heroicon::Clock)
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('article_category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name', fn ($query) => $query->active()->orderBy('sort_order'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('is_published')
                    ->label(__('Published'))
                    ->options([
                        true => __('Published'),
                        false => __('Draft'),
                    ])
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
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
                        ->exporter(ArticleExporter::class)
                        ->fileName(fn () => 'selected-articles-'.now()->format('Y-m-d-H-i-s'))
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
            ->defaultSort('created_at', 'desc');
    }
}
