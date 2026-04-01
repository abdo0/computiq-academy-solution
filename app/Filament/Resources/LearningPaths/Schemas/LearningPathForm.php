<?php

namespace App\Filament\Resources\LearningPaths\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningPathForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('General Information'))
                    ->columns(2)
                    ->schema([
                        $schema->translate([
                            TextInput::make('title')
                                ->label(__('Title'))
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label(__('Description'))
                                ->rows(2),
                        ]),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),
                    ]),

                Section::make(__('Visuals & Settings'))
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('Path Thumbnail'))
                            ->image()
                            ->directory('learning-paths')
                            ->columnSpan(2),
                        TextInput::make('icon')
                            ->label(__('Lucide Icon Name'))
                            ->helperText(__('E.g. Code, Database, Monitor'))
                            ->columnSpan(1),
                        ColorPicker::make('color')
                            ->label(__('Accent Color'))
                            ->columnSpan(1),
                        TextInput::make('estimated_hours')
                            ->label(__('Estimated Hours'))
                            ->numeric()
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
