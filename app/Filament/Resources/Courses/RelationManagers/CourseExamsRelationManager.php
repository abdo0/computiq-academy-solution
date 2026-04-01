<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Filament\Resources\CourseExams\CourseExamResource;
use App\Models\CourseExam;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CourseExamsRelationManager extends RelationManager
{
    protected static string $relationship = 'exams';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
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

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query->with(['module'])->withCount('questions'))
            ->columns([
                TextColumn::make('title')
                    ->label(__('Exam'))
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->formatStateUsing(fn (CourseExam $record) => $record->getTranslation('title', app()->getLocale()) ?: $record->getTranslation('title', 'en'))
                    ->wrap(),
                TextColumn::make('module.title')
                    ->label(__('Module'))
                    ->icon(Heroicon::BookOpen)
                    ->formatStateUsing(fn (CourseExam $record) => $record->module?->getTranslation('title', app()->getLocale()) ?: $record->module?->getTranslation('title', 'en') ?: __('Unknown module'))
                    ->wrap(),
                TextColumn::make('questions_count')
                    ->label(__('Questions'))
                    ->icon(Heroicon::QuestionMarkCircle)
                    ->badge(),
                TextColumn::make('pass_mark')
                    ->label(__('Pass Mark'))
                    ->icon(Heroicon::CheckBadge)
                    ->suffix('%'),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->headerActions([
                Action::make('createExam')
                    ->label(__('Create Exam'))
                    ->icon(Heroicon::Plus)
                    ->url(fn (): string => CourseExamResource::getUrl('create', ['courseId' => $this->getOwnerRecord()->getKey()])),
            ])
            ->recordActions([
                Action::make('manageExam')
                    ->label(__('Manage Exam'))
                    ->icon(Heroicon::PencilSquare)
                    ->url(fn (CourseExam $record): string => CourseExamResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('id', 'desc');
    }
}
