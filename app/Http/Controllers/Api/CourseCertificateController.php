<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Learning\CourseCertificateService;
use App\Services\Learning\LearningService;
use Illuminate\Http\Request;

class CourseCertificateController extends Controller
{
    public function __construct(
        protected CourseCertificateService $courseCertificateService,
        protected LearningService $learningService,
    ) {
    }

    public function download(Request $request, Course $course)
    {
        abort_unless(
            $request->user()->courseEnrollments()->where('course_id', $course->id)->exists(),
            403,
            __('You are not enrolled in this course.')
        );

        $summary = $this->learningService->getCourseProgressSummary($course, $request->user());

        return $this->courseCertificateService->download(
            $request->user(),
            $course,
            (bool) ($summary['completed'] ?? false),
        );
    }
}
