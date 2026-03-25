<?php

namespace App\Filament\Resources\ArticleCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Category Information'))
                            ->schema([
                                Translate::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                TextInput::make('code')
                                    ->label(__('Code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->helperText(__('Unique identifier for the category')),

                                Translate::make()
                                    ->schema([
                                        \Filament\Forms\Components\Textarea::make('description')
                                            ->label(__('Description'))
                                            ->rows(3)
                                            ->maxLength(65535),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),
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
                                    ->default(true)
                                    ->helperText(__('Enable or disable this category')),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
