<?php

namespace App\Filament\Resources\CourseModules;

use App\Filament\Resources\CourseModules\Pages\EditCourseModule;
use App\Filament\Resources\CourseModules\RelationManagers\LessonsRelationManager;
use App\Filament\Resources\CourseModules\Schemas\CourseModuleForm;
use App\Models\CourseModule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseModuleResource extends Resource
{
    protected static ?string $model = CourseModule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Courses & Learning');
    }

    public static function getNavigationLabel(): string
    {
        return __('Course Modules');
    }

    public static function getModelLabel(): string
    {
        return __('Course Module');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Course Modules');
    }

    public static function form(Schema $schema): Schema
    {
        return CourseModuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditCourseModule::route('/{record}/edit'),
        ];
    }
}
