<?php

namespace App\Filament\Resources\LearningPaths\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Table;

class LearningPathsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label(__('Thumbnail')),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->icon(Heroicon::Map)
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', 'ar') ?: $record->getTranslation('title', 'en')),
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->icon(Heroicon::Link)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('icon')
                    ->label(__('Icon'))
                    ->icon(Heroicon::Sparkles)
                    ->searchable(),
                ColorColumn::make('color')
                    ->label(__('Color'))
                    ->searchable(),
                TextColumn::make('estimated_hours')
                    ->label(__('Estimated Hours'))
                    ->icon(Heroicon::Clock)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('courses_count')
                    ->label(__('Courses'))
                    ->icon(Heroicon::AcademicCap)
                    ->counts('courses')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon(Heroicon::Calendar)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->icon(Heroicon::PencilSquare)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Edit Learning Path')),
                DeleteAction::make()
                    ->label(__('Delete Learning Path')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Learning Paths')),
                    ForceDeleteBulkAction::make()
                        ->label(__('Force Delete Learning Paths')),
                    RestoreBulkAction::make()
                        ->label(__('Restore Learning Paths')),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc');
    }
}
