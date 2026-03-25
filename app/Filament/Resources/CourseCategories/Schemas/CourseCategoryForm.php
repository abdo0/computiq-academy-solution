<?php

namespace App\Filament\Resources\CourseCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Info')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name.ar')
                            ->label('Name (Arabic)')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('name.en')
                            ->label('Name (English)')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('name.ku')
                            ->label('Name (Kurdish)')
                            ->columnSpan(1),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                    ]),

                Section::make('Parent & Options')
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->getTranslation('name', 'en'))
                            ->nullable()
                            ->searchable()
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

                Section::make('Image')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Category Image')
                            ->image()
                            ->directory('course-categories')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->nullable(),
                    ]),
            ]);
    }
}
