<?php

namespace App\Filament\Resources\CourseExams\Pages;

use App\Filament\Resources\CourseExams\CourseExamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCourseExam extends EditRecord
{
    protected static string $resource = CourseExamResource::class;

    public function getTitle(): string
    {
        return __('Manage Exam');
    }

    public function getSubheading(): ?string
    {
        $moduleTitle = $this->record->module?->getTranslation('title', app()->getLocale())
            ?: $this->record->module?->getTranslation('title', 'en')
            ?: __('Untitled module');

        $courseTitle = $this->record->module?->course?->getTranslation('title', app()->getLocale())
            ?: $this->record->module?->course?->getTranslation('title', 'en')
            ?: __('Untitled course');

        return __('Manage the exam for :module in :course.', [
            'module' => $moduleTitle,
            'course' => $courseTitle,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('Delete Exam')),
        ];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
