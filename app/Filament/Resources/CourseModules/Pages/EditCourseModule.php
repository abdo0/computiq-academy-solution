<?php

namespace App\Filament\Resources\CourseModules\Pages;

use App\Filament\Resources\CourseExams\CourseExamResource;
use App\Filament\Resources\CourseModules\CourseModuleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCourseModule extends EditRecord
{
    protected static string $resource = CourseModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageExam')
                ->label($this->record->exam ? __('Manage Exam') : __('Create Exam'))
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->url(
                    $this->record->exam
                        ? CourseExamResource::getUrl('edit', ['record' => $this->record->exam])
                        : CourseExamResource::getUrl('create', ['courseModuleId' => $this->record->id])
                ),
            DeleteAction::make()
                ->label(__('Delete Module')),
        ];
    }

    public function getTitle(): string
    {
        return __('Manage Lessons');
    }

    public function getSubheading(): ?string
    {
        $moduleTitle = $this->record->getTranslation('title', app()->getLocale())
            ?: $this->record->getTranslation('title', 'en');

        $courseTitle = $this->record->course?->getTranslation('title', app()->getLocale())
            ?: $this->record->course?->getTranslation('title', 'en');

        return __('Add, edit, and reorder the lessons in :module for :course.', [
            'module' => $moduleTitle,
            'course' => $courseTitle,
        ]);
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
