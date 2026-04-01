<?php

namespace App\Filament\Resources\CourseExams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class CourseExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['module.course'])->withCount('questions'))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Exam'))
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()) ?: $record->getTranslation('title', 'en'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('module.course.title')
                    ->label(__('Course'))
                    ->icon(Heroicon::AcademicCap)
                    ->formatStateUsing(fn ($record) => $record->module?->course?->getTranslation('title', app()->getLocale()) ?: $record->module?->course?->getTranslation('title', 'en') ?: __('Unknown course'))
                    ->searchable(),
                TextColumn::make('module.title')
                    ->label(__('Module'))
                    ->icon(Heroicon::BookOpen)
                    ->formatStateUsing(fn ($record) => $record->module?->getTranslation('title', app()->getLocale()) ?: $record->module?->getTranslation('title', 'en') ?: __('Unknown module'))
                    ->searchable(),
                TextColumn::make('questions_count')
                    ->label(__('Questions'))
                    ->icon(Heroicon::QuestionMarkCircle)
                    ->badge(),
                TextColumn::make('pass_mark')
                    ->label(__('Pass Mark'))
                    ->icon(Heroicon::CheckBadge)
                    ->numeric()
                    ->suffix('%'),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Manage Exam')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
