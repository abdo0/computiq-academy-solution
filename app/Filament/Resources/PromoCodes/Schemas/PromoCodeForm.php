<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Enums\PromoCodeDiscountType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Promo Code Details'))
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('Code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),
                                Select::make('discount_type')
                                    ->label(__('Discount Type'))
                                    ->options(PromoCodeDiscountType::class)
                                    ->required()
                                    ->native(false),
                                TextInput::make('discount_value')
                                    ->label(__('Discount Value'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01),
                                TextInput::make('usage_limit')
                                    ->label(__('Usage Limit'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable(),
                                DateTimePicker::make('starts_at')
                                    ->label(__('Starts At'))
                                    ->seconds(false),
                                DateTimePicker::make('expires_at')
                                    ->label(__('Expires At'))
                                    ->seconds(false),
                            ])
                            ->columns(2),
                    ]),
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Status'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                                TextInput::make('used_count')
                                    ->label(__('Used Count'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }
}
