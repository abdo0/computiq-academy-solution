<?php

namespace App\Filament\Clusters\Locations\Resources\States;

use App\Filament\Clusters\Entities\EntitiesCluster;
use App\Filament\Clusters\Locations\Resources\States\Pages\ManageStates;
use App\Models\Country;
use App\Models\State;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static ?string $cluster = EntitiesCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Map;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('States');
    }

    public static function getModelLabel(): string
    {
        return __('State');
    }

    public static function getPluralModelLabel(): string
    {
        return __('States');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('State Information'))
                    ->schema([
                        Select::make('country_id')
                            ->label(__('Country'))
                            ->relationship('country', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Country $record) => $record->title)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Translate::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label(__('Code'))
                            ->maxLength(10)
                            ->helperText(__('Internal reference or ISO subdivision code.')),

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
                    ->icon(Heroicon::MapPin)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label(__('Code'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('country.title')
                    ->label(__('Country'))
                    ->icon(Heroicon::GlobeEuropeAfrica)
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? __('Active') : __('Inactive'))
                    ->color(fn (bool $state) => $state ? 'success' : 'secondary')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label(__('Country'))
                    ->relationship('country', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Country $record) => $record->title)
                    ->searchable()
                    ->preload(),

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
            'index' => ManageStates::route('/'),
        ];
    }
}
