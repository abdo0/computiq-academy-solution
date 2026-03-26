<?php

namespace App\Filament\Resources\LearningPaths\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningPathForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title.ar')
                            ->label('Title (Arabic)')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('title.en')
                            ->label('Title (English)')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('title.ku')
                            ->label('Title (Kurdish)')
                            ->columnSpan(2),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),
                    ]),

                Section::make('Descriptions')
                    ->columns(1)
                    ->schema([
                        Textarea::make('description.ar')->label('Description (Arabic)')->rows(2),
                        Textarea::make('description.en')->label('Description (English)')->rows(2),
                        Textarea::make('description.ku')->label('Description (Kurdish)')->rows(2),
                    ]),

                Section::make('Visuals & Settings')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label('Path Thumbnail')
                            ->image()
                            ->directory('learning-paths')
                            ->columnSpan(2),
                        TextInput::make('icon')
                            ->label('Lucide Icon Name')
                            ->helperText('E.g. Code, Database, Monitor')
                            ->columnSpan(1),
                        ColorPicker::make('color')
                            ->label('Accent Color')
                            ->columnSpan(1),
                        TextInput::make('estimated_hours')
                            ->label('Estimated Hours')
                            ->numeric()
                            ->columnSpan(1),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
