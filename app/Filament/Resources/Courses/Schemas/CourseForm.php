<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- Main Info ---
                Section::make('Course Information')
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
                            ->columnSpan(1),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        Select::make('course_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->getTranslation('name', 'en'))
                            ->searchable()
                            ->nullable()
                            ->columnSpan(2),
                    ]),

                // --- Descriptions ---
                Section::make('Descriptions')
                    ->columns(1)
                    ->schema([
                        Textarea::make('short_description.ar')->label('Short Description (Arabic)')->rows(2),
                        Textarea::make('short_description.en')->label('Short Description (English)')->rows(2),
                        Textarea::make('short_description.ku')->label('Short Description (Kurdish)')->rows(2),
                    ]),

                // --- Instructor ---
                Section::make('Instructor')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instructor_name')
                            ->label('Instructor Name')
                            ->columnSpan(1),
                        FileUpload::make('instructor_image')
                            ->label('Instructor Photo')
                            ->image()
                            ->directory('instructors')
                            ->avatar()
                            ->columnSpan(1),
                    ]),

                // --- Stats ---
                Section::make('Stats')
                    ->columns(4)
                    ->schema([
                        TextInput::make('rating')->label('Rating')->numeric()->default(0)->minValue(0)->maxValue(5)->step(0.1),
                        TextInput::make('review_count')->label('Reviews')->numeric()->default(0),
                        TextInput::make('duration_hours')->label('Hours')->numeric()->default(0),
                        TextInput::make('students_count')->label('Students')->numeric()->default(0),
                    ]),

                // --- Pricing ---
                Section::make('Pricing')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')->label('Price')->numeric()->default(0)->prefix('$'),
                        TextInput::make('old_price')->label('Old Price (before discount)')->numeric()->nullable()->prefix('$'),
                    ]),

                // --- Flags ---
                Section::make('Badges & Status')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')->label('Active')->default(true),
                        Toggle::make('is_live')->label('Live Course (Instructor-Led)'),
                        Toggle::make('is_best_seller')->label('Best Seller'),
                    ]),

                // --- Order & Image ---
                Section::make('Image & Ordering')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label('Course Thumbnail')
                            ->image()
                            ->directory('courses')
                            ->columnSpan(1),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
