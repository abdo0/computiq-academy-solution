<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\Learning\LessonMediaPayloadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseLessonPreviewController extends Controller
{
    public function __construct(
        protected LessonMediaPayloadService $lessonMediaPayloadService,
    ) {
    }

    public function show(string $slug, CourseLesson $lesson): JsonResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $course) {
            throw new NotFoundHttpException();
        }

        $lesson->loadMissing('module.course');

        if (
            ! $lesson->is_active
            || ! $lesson->module
            || (int) $lesson->module->course_id !== (int) $course->id
        ) {
            throw new NotFoundHttpException();
        }

        if (! $lesson->is_free) {
            throw new AuthorizationException(__('Preview unavailable for this lesson.'));
        }

        return response()->success(
            $this->lessonMediaPayloadService->buildPublicPreviewPayload($lesson),
            __('Course lesson preview retrieved successfully.')
        );
    }
}
