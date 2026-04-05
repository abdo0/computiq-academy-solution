<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label(__('Thumb'))
                    ->square()
                    ->size(50),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->icon(Heroicon::AcademicCap)
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()) ?: $record->getTranslation('title', 'en'))
                    ->sortable()
                    ->limit(40),
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->icon(Heroicon::Tag)
                    ->formatStateUsing(fn ($record) => $record->category?->getTranslation('name', app()->getLocale()) ?: $record->category?->getTranslation('name', 'en') ?: '-')
                    ->sortable(),
                TextColumn::make('instructor_name')
                    ->label(__('Instructor'))
                    ->icon(Heroicon::User)
                    ->searchable(),
                TextColumn::make('rating')
                    ->label(__('Rating'))
                    ->icon(Heroicon::Star)
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->icon(Heroicon::Banknotes)
                    ->formatStateUsing(fn ($state) => money((float) $state))
                    ->sortable(),
                TextColumn::make('students_count')
                    ->label(__('Students'))
                    ->icon(Heroicon::Users)
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                IconColumn::make('is_live')
                    ->label(__('Live'))
                    ->boolean(),
                TextColumn::make('delivery_type')
                    ->label(__('Delivery'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'onsite' => __('On-site'),
                        'hybrid' => __('Hybrid'),
                        default => __('Online'),
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'onsite' => 'warning',
                        'hybrid' => 'info',
                        default => 'success',
                    }),
                IconColumn::make('is_best_seller')
                    ->label(__('Best'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->icon(Heroicon::Clock)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('course_category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name'),
                SelectFilter::make('delivery_type')
                    ->label(__('Delivery Type'))
                    ->options([
                        'online' => __('Online'),
                        'onsite' => __('On-site'),
                        'hybrid' => __('Hybrid'),
                    ]),
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
            ->reorderable('sort_order');
    }
}
