<?php

namespace App\Filament\Resources\CourseExams\Pages;

use App\Filament\Resources\CourseExams\CourseExamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseExam extends CreateRecord
{
    protected static string $resource = CourseExamResource::class;

    public function getTitle(): string
    {
        return __('Create Exam');
    }
}
