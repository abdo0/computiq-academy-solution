<?php

namespace App\Filament\Resources\CourseCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Category Info'))
                    ->columns(2)
                    ->schema([
                        $schema->translate([
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),
                        ]),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                    ]),

                Section::make(__('Parent & Options'))
                    ->columns(2)
                    ->schema([
                        Select::make('parent_id')
                            ->label(__('Parent Category'))
                            ->relationship('parent', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->getTranslation('name', 'en'))
                            ->nullable()
                            ->searchable()
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->columnSpan(2),
                    ]),

                Section::make(__('Image'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('Category Image'))
                            ->image()
                            ->directory('course-categories')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->nullable(),
                    ]),
            ]);
    }
}
