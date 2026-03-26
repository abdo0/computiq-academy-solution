<?php

namespace App\Filament\Resources\LearningPaths\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('title')
                    ->label('Title')
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', 'ar') ?: $record->getTranslation('title', 'en')),
                TextColumn::make('instructor.name')
                    ->label('Instructor'),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
