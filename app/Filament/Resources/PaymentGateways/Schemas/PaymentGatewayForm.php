<?php

namespace App\Filament\Resources\PaymentGateways\Schemas;

use App\Enums\PaymentGatewayType;
use App\Models\Currency;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PaymentGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Gateway Information'))
                            ->schema([
                                Translate::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label(__('Description'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),
                                TextInput::make('code')
                                    ->label(__('Code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                Select::make('type')
                                    ->label(__('Type'))
                                    ->options(PaymentGatewayType::class)
                                    ->required()
                                    ->native(false),
                            ])
                            ->columns(2),
                        Section::make(__('Processing Fees'))
                            ->schema([
                                TextInput::make('processing_fee_percentage')
                                    ->label(__('Processing Fee Percentage'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('%'),
                                TextInput::make('processing_fee_fixed')
                                    ->label(__('Fixed Processing Fee'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix(Currency::getDefaultSymbol()),
                            ])
                            ->columns(2),
                        Section::make(__('Configuration'))
                            ->schema([
                                KeyValue::make('configuration')
                                    ->label(__('Gateway Configuration'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Logo'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('logo')
                                    ->collection('logo')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(5120),
                            ]),
                        Section::make(__('Settings'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }
}
