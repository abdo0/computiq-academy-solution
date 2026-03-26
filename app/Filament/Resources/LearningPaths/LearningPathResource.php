<?php

namespace App\Filament\Resources\LearningPaths;

use App\Filament\Resources\LearningPaths\Pages\CreateLearningPath;
use App\Filament\Resources\LearningPaths\Pages\EditLearningPath;
use App\Filament\Resources\LearningPaths\Pages\ListLearningPaths;
use App\Filament\Resources\LearningPaths\Schemas\LearningPathForm;
use App\Filament\Resources\LearningPaths\Tables\LearningPathsTable;
use App\Filament\Resources\LearningPaths\RelationManagers\CoursesRelationManager;
use App\Models\LearningPath;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LearningPathResource extends Resource
{
    protected static ?string $model = LearningPath::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return LearningPathForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LearningPathsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CoursesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLearningPaths::route('/'),
            'create' => CreateLearningPath::route('/create'),
            'edit' => EditLearningPath::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
