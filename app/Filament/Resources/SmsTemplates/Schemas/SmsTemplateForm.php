<?php

namespace App\Filament\Resources\SmsTemplates\Schemas;

use App\Enums\SmsTemplatePurpose;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class SmsTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Template Information'))
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('Code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->helperText(__('Unique identifier for the template'))
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Select::make('purpose')
                                    ->label(__('Purpose'))
                                    ->options(SmsTemplatePurpose::class)
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('Select the purpose of this SMS template')),

                                Translate::make()
                                    ->schema([
                                        Textarea::make('content')
                                            ->label(__('Content'))
                                            ->required()
                                            ->rows(5)
                                            ->maxLength(500)
                                            ->helperText(__('SMS content (max 500 characters). Use {{variable_name}} for dynamic content'))
                                            ->columnSpanFull(),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Textarea::make('variables')
                                    ->label(__('Available Variables'))
                                    ->rows(3)
                                    ->helperText(__('List of available variables (comma-separated)'))
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Settings'))
                            ->schema([
                                Toggle::make('is_default')
                                    ->label(__('Set as Default'))
                                    ->helperText(__('Mark this template as default for its purpose. Other defaults for the same purpose will be unset automatically.')),

                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->helperText(__('Enable or disable this template')),

                                TextInput::make('sort_order')
                                    ->label(__('Sort Order'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('Order for display')),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
