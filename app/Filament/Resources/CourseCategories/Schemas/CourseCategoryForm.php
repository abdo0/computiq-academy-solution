<?php

namespace App\Filament\Resources\CourseCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
                            ->inline(false)
                            ->columnSpan(1),
                        Toggle::make('show_on_home')
                            ->label(__('Show on Home'))
                            ->helperText(__('Enable this category to appear in the home page course sections and tabs.'))
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                    ]),

                Section::make(__('Images & Display'))
                    ->columns(1)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('category_image')
                            ->label(__('Category Image'))
                            ->helperText(__('This image is used in the courses navigation menu and home page category presentation.'))
                            ->collection('image')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
