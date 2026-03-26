<?php

namespace App\Filament\Resources\LearningPaths\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LearningPathsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Thumbnail'),
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', 'ar') ?: $record->getTranslation('title', 'en')),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('icon')
                    ->searchable(),
                ColorColumn::make('color')
                    ->label('Color')
                    ->searchable(),
                TextColumn::make('estimated_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('courses_count')
                    ->label('Courses')
                    ->counts('courses')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
