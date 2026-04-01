<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make(__('Course Information'))
                    ->compact()
                    ->columnSpan(8)
                    ->columns(2)
                    ->schema([
                        $schema->translate([
                            TextInput::make('title')
                                ->label(__('Title'))
                                ->required()
                                ->maxLength(255),
                            Textarea::make('short_description')
                                ->label(__('Short Description'))
                                ->rows(2),
                            Textarea::make('description')
                                ->label(__('Description'))
                                ->rows(5),
                        ]),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        Select::make('course_category_id')
                            ->label(__('Category'))
                            ->relationship('category', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->getTranslation('name', 'en'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),
                        Select::make('instructor_id')
                            ->label(__('Instructor'))
                            ->relationship('instructor', 'slug')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->getTranslation('name', 'en'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),
                    ]),
                Section::make(__('Instructor Fallback'))
                    ->compact()
                    ->columnSpan(4)
                    ->columns(1)
                    ->schema([
                        TextInput::make('instructor_name')
                            ->label(__('Instructor Name'))
                            ->helperText(__('Used only when no linked instructor is selected.'))
                            ->columnSpanFull(),
                        FileUpload::make('instructor_image')
                            ->label(__('Instructor Photo'))
                            ->image()
                            ->directory('instructors')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Pricing'))
                    ->compact()
                    ->columnSpan(6)
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')->label(__('Price'))->numeric()->default(0)->prefix('$'),
                        TextInput::make('old_price')->label(__('Old Price (before discount)'))->numeric()->nullable()->prefix('$'),
                    ]),
                Section::make(__('Stats'))
                    ->compact()
                    ->columnSpan(6)
                    ->columns(4)
                    ->schema([
                        TextInput::make('rating')->label(__('Rating'))->numeric()->default(0)->minValue(0)->maxValue(5)->step(0.1),
                        TextInput::make('review_count')->label(__('Reviews'))->numeric()->default(0),
                        TextInput::make('duration_hours')->label(__('Hours'))->numeric()->default(0),
                        TextInput::make('students_count')->label(__('Students'))->numeric()->default(0),
                    ]),
                Section::make(__('Badges & Status'))
                    ->compact()
                    ->columnSpan(6)
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')->label(__('Active'))->default(true),
                        Toggle::make('is_live')->label(__('Live Course (Instructor-Led)')),
                        Toggle::make('is_best_seller')->label(__('Best Seller')),
                    ]),
                Section::make(__('Image'))
                    ->compact()
                    ->columnSpan(6)
                    ->columns(1)
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('Course Thumbnail'))
                            ->image()
                            ->directory('courses')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
