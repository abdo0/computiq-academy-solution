<?php

namespace App\Filament\Resources\CourseExams\Schemas;

use App\Models\CourseModule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CourseExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
            Section::make(__('Exam Information'))
                ->compact()
                ->columnSpanFull()
                ->columns(12)
                ->schema([
                    Select::make('course_module_id')
                        ->label(__('Module'))
                        ->relationship('module', 'id', fn (Builder $query) => $query
                            ->when(
                                request()->integer('courseId'),
                                fn (Builder $query, int $courseId) => $query->where('course_id', $courseId)
                            )
                            ->orderBy('sort_order'))
                        ->getOptionLabelFromRecordUsing(fn (CourseModule $record): string => self::moduleOptionLabel($record))
                        ->default(fn (): ?int => request()->integer('courseModuleId') ?: null)
                        ->disabled(fn (string $operation): bool => $operation === 'create' && filled(request('courseModuleId')))
                        ->dehydrated()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),
                    $schema->translate([
                        TextInput::make('title')
                            ->label(__('Exam Title'))
                            ->required()
                            ->maxLength(255),
                    ]),
                    TextInput::make('pass_mark')
                        ->label(__('Pass Mark'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(70)
                        ->required()
                        ->columnSpan(4),
                    TextInput::make('max_attempts')
                        ->label(__('Max Attempts'))
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->columnSpan(4),
                    TextInput::make('time_limit_minutes')
                        ->label(__('Time Limit (Minutes)'))
                        ->numeric()
                        ->minValue(1)
                        ->nullable()
                        ->columnSpan(4),
                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    protected static function moduleOptionLabel(CourseModule $module): string
    {
        $courseTitle = $module->course?->getTranslation('title', app()->getLocale())
            ?: $module->course?->getTranslation('title', 'en')
            ?: __('Untitled course');

        $moduleTitle = $module->getTranslation('title', app()->getLocale())
            ?: $module->getTranslation('title', 'en')
            ?: __('Untitled module');

        return "{$courseTitle} / {$moduleTitle}";
    }
}
