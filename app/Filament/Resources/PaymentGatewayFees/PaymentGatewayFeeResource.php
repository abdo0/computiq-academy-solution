<?php

namespace App\Filament\Resources\PaymentGatewayFees;

use App\Filament\Resources\PaymentGatewayFees\Pages\CreatePaymentGatewayFee;
use App\Filament\Resources\PaymentGatewayFees\Pages\EditPaymentGatewayFee;
use App\Filament\Resources\PaymentGatewayFees\Pages\ListPaymentGatewayFees;
use App\Filament\Resources\PaymentGatewayFees\Schemas\PaymentGatewayFeeForm;
use App\Filament\Resources\PaymentGatewayFees\Tables\PaymentGatewayFeesTable;
use App\Models\PaymentGatewayFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentGatewayFeeResource extends Resource
{
    protected static ?string $model = PaymentGatewayFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('Payment Gateway Fees');
    }

    public static function getModelLabel(): string
    {
        return __('Payment Gateway Fee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payment Gateway Fees');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Payments');
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewayFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewayFeesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGatewayFees::route('/'),
            'create' => CreatePaymentGatewayFee::route('/create'),
            'edit' => EditPaymentGatewayFee::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
