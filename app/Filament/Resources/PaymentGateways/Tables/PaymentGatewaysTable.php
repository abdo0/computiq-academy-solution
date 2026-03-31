<?php

namespace App\Filament\Resources\PaymentGateways\Tables;

use App\Enums\PaymentGatewayType;
use App\Filament\Exports\PaymentGatewayExporter;
use App\Services\Payment\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentGatewaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label(__('Logo'))
                    ->collection('logo')
                    ->conversion('thumb')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('processing_fee')
                    ->label(__('Processing Fee')),
                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Active') : __('Inactive'))
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(PaymentGatewayType::class)
                    ->native(false),
            ])
            ->recordActions([
                Action::make('health_check')
                    ->label(__('Health Check'))
                    ->icon(Heroicon::Heart)
                    ->iconButton()
                    ->action(function ($record) {
                        $result = app(PaymentService::class)->checkGatewayHealth($record);

                        Notification::make()
                            ->title($result['success'] ? __('Gateway Health Check') : __('Gateway Health Check Failed'))
                            ->body($result['message'])
                            ->color($result['success'] ? 'success' : 'danger')
                            ->send();
                    }),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(PaymentGatewayExporter::class)
                        ->formats([ExportFormat::Xlsx, ExportFormat::Csv]),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
