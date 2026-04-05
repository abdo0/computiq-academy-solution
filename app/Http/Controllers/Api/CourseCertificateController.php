<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
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

    public function index(Request $request)
    {
        $user = $request->user();

        $certificates = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->with([
                'course.category',
                'course.certificateTemplate.media',
                'course.modules' => fn ($query) => $query->orderBy('sort_order'),
                'course.modules.lessons' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'course.modules.exam',
                'course.instructor',
            ])
            ->orderByDesc('enrolled_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (CourseEnrollment $enrollment) => $enrollment->course !== null && $enrollment->course->is_active)
            ->values()
            ->map(function (CourseEnrollment $enrollment) use ($user) {
                $course = $enrollment->course;
                $summary = $this->learningService->getCourseProgressSummary($course, $user);
                $certificate = $this->courseCertificateService->toPayload(
                    $user,
                    $course,
                    (bool) ($summary['completed'] ?? false),
                );

                $continueUrl = '/learn/' . $course->slug;

                if (($summary['resume_target']['type'] ?? null) === 'lesson') {
                    $continueUrl .= '?lesson=' . $summary['resume_target']['id'];
                } elseif (($summary['resume_target']['type'] ?? null) === 'exam') {
                    $continueUrl .= '?exam=' . $summary['resume_target']['id'];
                }

                return [
                    'id' => $course->id,
                    'course_id' => $course->id,
                    'course_slug' => $course->slug,
                    'course_title' => $course->getTranslations('title'),
                    'course_image' => $course->image,
                    'enrolled_at' => optional($enrollment->enrolled_at)->toIso8601String(),
                    'progress_percent' => $summary['percent'] ?? 0,
                    'continue_url' => $continueUrl,
                    'certificate' => $certificate,
                ];
            })
            ->all();

        return response()->success($certificates, __('Certificates retrieved successfully.'));
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
