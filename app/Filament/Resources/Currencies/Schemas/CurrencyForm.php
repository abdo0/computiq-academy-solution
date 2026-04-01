<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Currency Details'))
                    ->description(__('Choose the code, symbol, and whether this currency is the project default.'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Currency Code'))
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),

                        TextInput::make('name')
                            ->label(__('Currency Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('symbol')
                            ->label(__('Currency Symbol'))
                            ->required()
                            ->maxLength(10),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->helperText(__('Inactive currencies cannot be selected as the project default.'))
                            ->disabled(fn (Get $get) => (bool) $get('is_default'))
                            ->inline(false),

                        Toggle::make('is_default')
                            ->label(__('Default Currency'))
                            ->default(false)
                            ->helperText(__('The default currency is used across checkout, prices, and payment flows.'))
                            ->live()
                            ->afterStateUpdated(function (Set $set, bool $state): void {
                                if ($state) {
                                    $set('is_active', true);
                                }
                            })
                            ->inline(false),
                    ]),
            ]);
    }
}
