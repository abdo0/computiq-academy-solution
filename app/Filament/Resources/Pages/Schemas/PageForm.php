<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Page Information'))
                            ->schema([
                                Translate::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('Title'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText(__('URL-friendly identifier'))
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('content')
                                            ->label(__('Content'))
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Settings'))
                            ->schema([
                                Toggle::make('is_published')
                                    ->label(__('Published'))
                                    ->default(false)
                                    ->helperText(__('Make this page visible to the public')),
                                Toggle::make('show_in_header')
                                    ->label(__('Show in Header'))
                                    ->default(false)
                                    ->helperText(__('Display this page link in the site header navigation')),
                                Toggle::make('show_in_footer')
                                    ->label(__('Show in Footer'))
                                    ->default(false)
                                    ->helperText(__('Display this page link in the site footer')),
                            ])
                            ->columns(1),

                        Section::make(__('SEO'))
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label(__('Meta Title'))
                                    ->maxLength(255)
                                    ->helperText(__('SEO meta title')),

                                \Filament\Forms\Components\Textarea::make('meta_description')
                                    ->label(__('Meta Description'))
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText(__('SEO meta description')),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
