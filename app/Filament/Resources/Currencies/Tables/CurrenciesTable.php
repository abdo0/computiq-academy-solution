<?php

namespace App\Filament\Resources\Currencies\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('symbol')
                    ->label(__('Symbol'))
                    ->searchable(),
                IconColumn::make('is_default')
                    ->label(__('Default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('set_default')
                    ->label(__('Set Default'))
                    ->icon(Heroicon::Star)
                    ->color('primary')
                    ->visible(fn ($record) => ! $record->is_default)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'is_default' => true,
                        'is_active' => true,
                    ])),
                EditAction::make()
                    ->label(__('Edit'))
                    ->icon(Heroicon::Pencil)
                    ->iconButton()
                    ->color('warning'),
                DeleteAction::make()
                    ->label(__('Delete'))
                    ->icon(Heroicon::Trash)
                    ->iconButton()
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected'))
                        ->color('danger'),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated([10, 25, 50, 100]);
    }
}
