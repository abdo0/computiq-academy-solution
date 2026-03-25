<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Enums\EmailTemplatePurpose;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class EmailTemplateForm
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
                                    ->options(EmailTemplatePurpose::class)
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('Select the purpose of this email template')),

                                Translate::make()
                                    ->schema([
                                        TextInput::make('subject')
                                            ->label(__('Subject'))
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->locales(appLocales())
                                    ->columnSpanFull(),

                                Translate::make()
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('body')
                                            ->label(__('Body'))
                                            ->required()
                                            ->columnSpanFull()
                                            ->helperText(__('Use {{variable_name}} for dynamic content')),
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
