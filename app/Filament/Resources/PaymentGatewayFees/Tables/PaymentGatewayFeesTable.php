<?php

namespace App\Filament\Resources\PaymentGatewayFees\Tables;

use App\Enums\PaymentGatewayFeeType;
use App\Filament\Exports\PaymentGatewayFeeExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentGatewayFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fee_type')
                    ->label(__('Fee Type'))
                    ->badge(),
                TextColumn::make('paymentGateway.name')
                    ->label(__('Payment Gateway'))
                    ->formatStateUsing(fn ($record) => $record->paymentGateway?->getTranslation('name', app()->getLocale()) ?? __('No gateway'))
                    ->badge(),
                TextColumn::make('fee_amount')
                    ->label(__('Fee Amount')),
                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Active') : __('Inactive'))
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('fee_type')
                    ->label(__('Fee Type'))
                    ->options(PaymentGatewayFeeType::class)
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(PaymentGatewayFeeExporter::class)
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
