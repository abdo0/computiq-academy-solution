<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseExam;
use App\Models\CourseLesson;
use App\Services\Learning\LearningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function __construct(
        protected LearningService $learningService,
    ) {
    }

    public function showCourse(Request $request, string $slug): JsonResponse
    {
        $payload = $this->learningService->getCourseForLearner($request->user(), $slug);

        return response()->success($payload, __('Learning course retrieved successfully.'));
    }

    public function openLesson(Request $request, CourseLesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'last_position_seconds' => 'nullable|integer|min:0',
        ]);

        $payload = $this->learningService->openLesson(
            $request->user(),
            $lesson,
            $validated['last_position_seconds'] ?? null,
        );

        return response()->success($payload, __('Lesson progress updated successfully.'));
    }

    public function completeLesson(Request $request, CourseLesson $lesson): JsonResponse
    {
        $payload = $this->learningService->completeLesson($request->user(), $lesson);

        return response()->success($payload, __('Lesson completed successfully.'));
    }

    public function startExam(Request $request, CourseExam $exam): JsonResponse
    {
        $payload = $this->learningService->startExam($request->user(), $exam);

        return response()->success($payload, __('Exam attempt started successfully.'));
    }

    public function submitExam(Request $request, CourseExam $exam): JsonResponse
    {
        $validated = $request->validate([
            'attempt_id' => 'required|integer',
            'answers' => 'required|array',
        ]);

        $payload = $this->learningService->submitExam(
            $request->user(),
            $exam,
            (int) $validated['attempt_id'],
            $validated['answers'],
        );

        return response()->success($payload, __('Exam submitted successfully.'));
    }

    public function resetExam(Request $request, CourseExam $exam): JsonResponse
    {
        $payload = $this->learningService->resetExamCycle($request->user(), $exam);

        return response()->success($payload, __('Exam reset successfully. Please review the module again.'));
    }
}
