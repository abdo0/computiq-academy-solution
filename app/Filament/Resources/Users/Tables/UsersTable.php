<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Exports\UserExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Full Name'))
                    ->icon(Heroicon::User)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employment_id')
                    ->label(__('Employment ID'))
                    ->icon(Heroicon::Identification)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->icon(Heroicon::Envelope)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->icon(Heroicon::ShieldCheck)
                    ->badge()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->icon(Heroicon::Plus)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->icon(Heroicon::Pencil)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->filtersFormColumns(4)
            ->filtersFormWidth('full')
            ->deferFilters(false)
            ->persistFiltersInSession()
            ->recordActions([
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
                    ExportBulkAction::make()
                        ->label(__('Export Selected'))
                        ->icon(Heroicon::ArrowDownTray)
                        ->color('primary')
                        ->outlined()
                        ->exporter(UserExporter::class)
                        ->fileName(fn () => 'selected-users-'.now()->format('Y-m-d-H-i-s'))
                        ->formats([
                            ExportFormat::Xlsx,
                            ExportFormat::Csv,
                        ])
                        ->columnMappingColumns(3),
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected'))
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
