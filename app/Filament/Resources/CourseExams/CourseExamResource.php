<?php

namespace App\Filament\Resources\CourseExams;

use App\Filament\Resources\CourseExams\Pages\CreateCourseExam;
use App\Filament\Resources\CourseExams\Pages\EditCourseExam;
use App\Filament\Resources\CourseExams\Pages\ListCourseExams;
use App\Filament\Resources\CourseExams\RelationManagers\QuestionsRelationManager;
use App\Filament\Resources\CourseExams\Schemas\CourseExamForm;
use App\Filament\Resources\CourseExams\Tables\CourseExamsTable;
use App\Models\CourseExam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseExamResource extends Resource
{
    protected static ?string $model = CourseExam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses & Learning');
    }

    public static function getNavigationLabel(): string
    {
        return __('Module Exams');
    }

    public static function getModelLabel(): string
    {
        return __('Module Exam');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Module Exams');
    }

    public static function form(Schema $schema): Schema
    {
        return CourseExamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseExamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseExams::route('/'),
            'create' => CreateCourseExam::route('/create'),
            'edit' => EditCourseExam::route('/{record}/edit'),
        ];
    }
}
