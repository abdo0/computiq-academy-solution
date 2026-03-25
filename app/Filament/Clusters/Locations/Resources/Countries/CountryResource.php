<?php

namespace App\Filament\Clusters\Locations\Resources\Countries;

use App\Filament\Clusters\Entities\EntitiesCluster;
use App\Filament\Clusters\Locations\Resources\Countries\Pages\ManageCountries;
use App\Models\Country;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $cluster = EntitiesCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::GlobeAsiaAustralia;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Countries');
    }

    public static function getModelLabel(): string
    {
        return __('Country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Countries');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Country Information'))
                    ->schema([
                        Translate::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Unique internal reference (e.g. IQ).')),

                        TextInput::make('iso2')
                            ->label(__('ISO2'))
                            ->required()
                            ->maxLength(2)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Two-letter ISO code.')),

                        TextInput::make('phone_code')
                            ->label(__('Phone Code'))
                            ->prefix('+')
                            ->maxLength(10),

                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Name'))
                    ->icon(Heroicon::GlobeEuropeAfrica)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label(__('Code'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('iso2')
                    ->label(__('ISO2'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('phone_code')
                    ->label(__('Phone Code'))
                    ->sortable(),

                TextColumn::make('states_count')
                    ->label(__('States'))
                    ->counts('states')
                    ->badge()
                    ->color('info'),

                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? __('Active') : __('Inactive'))
                    ->color(fn (bool $state) => $state ? 'success' : 'secondary')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCountries::route('/'),
        ];
    }
}
