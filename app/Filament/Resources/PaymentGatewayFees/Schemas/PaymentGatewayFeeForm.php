<?php

namespace App\Filament\Resources\PaymentGatewayFees\Schemas;

use App\Enums\PaymentGatewayFeeType;
use App\Models\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentGatewayFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Fee Information'))
                            ->schema([
                                Select::make('fee_type')
                                    ->label(__('Fee Type'))
                                    ->options(PaymentGatewayFeeType::class)
                                    ->required()
                                    ->native(false),
                                Select::make('payment_gateway_id')
                                    ->label(__('Payment Gateway'))
                                    ->relationship('paymentGateway', 'code', fn ($query) => $query->orderBy('sort_order'))
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false),
                            ])
                            ->columns(2),
                        Section::make(__('Fee Structure'))
                            ->schema([
                                TextInput::make('percentage')
                                    ->label(__('Percentage'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('%'),
                                TextInput::make('fixed_amount')
                                    ->label(__('Fixed Amount'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix(Currency::getDefaultSymbol()),
                                $schema->translate([
                                    Textarea::make('description')
                                        ->label(__('Description'))
                                        ->rows(3),
                                ]),
                            ])
                            ->columns(2),
                    ]),
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Settings'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
