<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Filament\Resources\CourseExams\CourseExamResource;
use App\Filament\Resources\CourseModules\CourseModuleResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedBookOpen;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Modules');
    }

    public static function getModelLabel(): string
    {
        return __('Module');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Modules');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $schema->translate([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Module'))
                    ->icon(Heroicon::BookOpen)
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()) ?: $record->getTranslation('title', 'en')),
                TextColumn::make('lessons_count')
                    ->label(__('Lessons'))
                    ->icon(Heroicon::PlayCircle)
                    ->counts('lessons'),
                TextColumn::make('exam.title')
                    ->label(__('Exam'))
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->formatStateUsing(fn ($record) => $record->exam?->getTranslation('title', app()->getLocale()) ?: $record->exam?->getTranslation('title', 'en') ?: __('No exam yet'))
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Module'))
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('manageLessons')
                        ->label(__('Manage Lessons'))
                        ->icon('heroicon-o-play-circle')
                        ->url(fn ($record) => CourseModuleResource::getUrl('edit', ['record' => $record])),
                    Action::make('manageExam')
                        ->label(fn ($record) => $record->exam ? __('Manage Exam') : __('Create Exam'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->url(fn ($record) => $record->exam
                            ? CourseExamResource::getUrl('edit', ['record' => $record->exam])
                            : CourseExamResource::getUrl('create', ['courseModuleId' => $record->id])),
                    EditAction::make()
                        ->label(__('Edit Module')),
                    DeleteAction::make()
                        ->label(__('Delete Module')),
                ])
                    ->label(__('Module Actions'))
                    ->tooltip(__('Module Actions'))
                    ->icon(Heroicon::EllipsisVertical)
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
