<?php

namespace App\Filament\Resources\FAQS\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class FAQForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('FAQ Information'))
                            ->schema([
                                Translate::make()
                                    ->schema([
                                        TextInput::make('question')
                                            ->label(__('Question'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('answer')
                                            ->label(__('Answer'))
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                TextInput::make('category')
                                    ->label(__('Category'))
                                    ->maxLength(100)
                                    ->helperText(__('Optional category for grouping FAQs')),
                            ])
                            ->columns(1),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Settings'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->helperText(__('Enable or disable this FAQ')),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
