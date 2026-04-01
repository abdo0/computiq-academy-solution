<?php

namespace App\Filament\Resources\CourseExams\Pages;

use App\Filament\Resources\CourseExams\CourseExamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseExams extends ListRecords
{
    protected static string $resource = CourseExamResource::class;

    public function getTitle(): string
    {
        return __('Module Exams');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Create Exam')),
        ];
    }
}
