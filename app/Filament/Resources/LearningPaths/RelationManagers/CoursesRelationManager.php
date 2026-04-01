<?php

namespace App\Filament\Resources\LearningPaths\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Path Courses');
    }

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->icon(Heroicon::AcademicCap)
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', 'ar') ?: $record->getTranslation('title', 'en')),
                TextColumn::make('instructor.name')
                    ->label(__('Instructor'))
                    ->icon(Heroicon::User),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('Add Course'))
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Hidden::make('sort_order')
                            ->default(fn () => ((DB::table('learning_path_course')
                                ->where('learning_path_id', $this->getOwnerRecord()->getKey())
                                ->max('sort_order') ?? -1) + 1)),
                    ]),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label(__('Detach Course')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('learning_path_course.sort_order');
    }
}
