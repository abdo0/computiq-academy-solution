<?php

namespace App\Services\Learning;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseExam;
use App\Models\CourseExamAttempt;
use App\Models\CourseLesson;
use App\Models\CourseLessonProgress;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LearningService
{
    protected const EXAM_TIME_GRACE_SECONDS = 30;

    public function __construct(
        protected CourseCertificateService $courseCertificateService,
        protected LessonMediaPayloadService $lessonMediaPayloadService,
    ) {
    }

    public function getCourseForLearner(User $user, string $slug): array
    {
        $course = $this->loadCourseBySlug($slug);
        $this->assertEnrollment($user, $course);

        return $this->buildCoursePayload($course, $user);
    }

    public function openLesson(User $user, CourseLesson $lesson, ?int $lastPositionSeconds = null): array
    {
        $course = $this->getCourseForLesson($user, $lesson);
        $payload = $this->buildCoursePayload($course, $user);
        $lessonState = $this->findLessonState($payload, $lesson->id);

        if (! $lessonState || ! ($lessonState['is_unlocked'] ?? false)) {
            throw new AuthorizationException(__('This lesson is still locked.'));
        }

        $progress = CourseLessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_lesson_id' => $lesson->id,
        ]);

        $progress->fill([
            'course_id' => $lesson->module->course_id,
            'course_module_id' => $lesson->course_module_id,
            'status' => $progress->completed_at ? 'completed' : 'in_progress',
            'last_position_seconds' => $lastPositionSeconds ?? $progress->last_position_seconds,
            'opened_at' => $progress->opened_at ?? now(),
        ]);
        $progress->save();

        return $this->buildCoursePayload($this->reloadCourse($course), $user);
    }

    public function completeLesson(User $user, CourseLesson $lesson): array
    {
        $course = $this->getCourseForLesson($user, $lesson);
        $payload = $this->buildCoursePayload($course, $user);
        $lessonState = $this->findLessonState($payload, $lesson->id);

        if (! $lessonState || ! ($lessonState['is_unlocked'] ?? false)) {
            throw new AuthorizationException(__('This lesson is still locked.'));
        }

        CourseLessonProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'course_lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $lesson->module->course_id,
                'course_module_id' => $lesson->course_module_id,
                'status' => 'completed',
                'opened_at' => now(),
                'completed_at' => now(),
            ],
        );

        return $this->buildCoursePayload($this->reloadCourse($course), $user);
    }

    public function startExam(User $user, CourseExam $exam): array
    {
        $course = $this->getCourseForExam($user, $exam);

        if (! $this->isExamUnlocked($user, $course, $exam)) {
            throw new AuthorizationException(__('This exam is still locked.'));
        }

        $payload = $this->buildCoursePayload($course, $user);
        $examState = $this->findExamState($payload, $exam->id);

        if (($examState['attempts_remaining'] ?? null) === 0) {
            throw new AuthorizationException(__('You have used all available exam attempts.'));
        }

        $openAttempt = CourseExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_exam_id', $exam->id)
            ->whereNull('submitted_at')
            ->latest('id')
            ->first();

        if ($openAttempt) {
            return $this->buildExamAttemptResponse($exam, $openAttempt);
        }

        $latestAttemptNumber = (int) CourseExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_exam_id', $exam->id)
            ->max('attempt_number');

        $attempt = CourseExamAttempt::query()->create([
            'user_id' => $user->id,
            'course_id' => $exam->module->course_id,
            'course_module_id' => $exam->course_module_id,
            'course_exam_id' => $exam->id,
            'attempt_number' => $latestAttemptNumber + 1,
            'started_at' => now(),
        ]);

        return [
            'attempt' => $this->buildExamAttemptResponse($exam, $attempt)['attempt'],
            'course' => $this->buildCoursePayload($this->reloadCourse($course), $user),
        ];
    }

    public function submitExam(User $user, CourseExam $exam, int $attemptId, array $answers): array
    {
        $course = $this->getCourseForExam($user, $exam);

        if (! $this->isExamUnlocked($user, $course, $exam)) {
            throw new AuthorizationException(__('This exam is still locked.'));
        }

        $payload = $this->buildCoursePayload($course, $user);
        $examState = $this->findExamState($payload, $exam->id);

        $attempt = CourseExamAttempt::query()
            ->where('id', $attemptId)
            ->where('user_id', $user->id)
            ->where('course_exam_id', $exam->id)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $questionIds = $exam->questions->pluck('id')->all();
        $normalizedAnswers = collect($answers)
            ->mapWithKeys(fn ($optionId, $questionId) => [(int) $questionId => (int) $optionId])
            ->only($questionIds)
            ->all();
        $submittedAt = now();
        $evaluation = $this->evaluateAttempt(
            $exam,
            $normalizedAnswers,
            $attempt->started_at,
            $submittedAt,
        );

        $attempt->update([
            'score' => $evaluation['score'],
            'passed' => $evaluation['passed'],
            'answers_json' => $normalizedAnswers,
            'submitted_at' => $submittedAt,
        ]);

        $coursePayload = $this->buildCoursePayload($this->reloadCourse($course), $user);

        return [
            'attempt' => [
                'id' => $attempt->id,
                'score' => $evaluation['score'],
                'passed' => $evaluation['passed'],
                'submitted_at' => optional($submittedAt)->toIso8601String(),
                'time_limit_exceeded' => $evaluation['time_limit_exceeded'],
            ],
            'course' => $coursePayload,
        ];
    }

    public function resetExamCycle(User $user, CourseExam $exam): array
    {
        $course = $this->getCourseForExam($user, $exam);
        $payload = $this->buildCoursePayload($course, $user);
        $examState = $this->findExamState($payload, $exam->id);

        if (! $examState || ! ($examState['attempts_exhausted'] ?? false)) {
            throw new AuthorizationException(__('This exam does not need a reset right now.'));
        }

        $this->resetModuleLearningCycle($user, $exam);

        return $this->buildCoursePayload($this->reloadCourse($course), $user);
    }

    public function buildCourseCard(CourseEnrollment $enrollment, User $user): array
    {
        $course = $this->reloadCourse($enrollment->course);
        $payload = $this->buildCoursePayload($course, $user);
        $continueUrl = '/learn/' . $course->slug;

        if (($payload['resume_target']['type'] ?? null) === 'lesson') {
            $continueUrl .= '?lesson=' . $payload['resume_target']['id'];
        } elseif (($payload['resume_target']['type'] ?? null) === 'exam') {
            $continueUrl .= '?exam=' . $payload['resume_target']['id'];
        }

        return [
            'id' => $course->id,
            'title' => $course->getTranslations('title'),
            'slug' => $course->slug,
            'image' => $course->image,
            'duration_hours' => $course->duration_hours,
            'status' => 'enrolled',
            'enrolled_at' => optional($enrollment->enrolled_at)->toIso8601String(),
            'progress_percent' => $payload['progress']['percent'],
            'completed_at' => $payload['certificate']['issued_at'] ?? null,
            'resume_target' => $payload['resume_target'],
            'continue_url' => $continueUrl,
            'certificate_available' => $payload['certificate']['available'] ?? false,
            'certificate_status' => $payload['certificate']['status'] ?? null,
            'certificate_url' => $payload['certificate']['download_url'] ?? null,
            'certificate' => $payload['certificate'] ?? null,
            'instructor' => $course->instructor ? [
                'name' => $course->instructor->getTranslations('name'),
                'image' => $course->instructor->image,
            ] : [
                'name' => [
                    'ar' => $course->instructor_name,
                    'en' => $course->instructor_name,
                    'ku' => $course->instructor_name,
                ],
                'image' => $course->instructor_image,
            ],
        ];
    }

    public function getCourseProgressSummary(Course $course, User $user): array
    {
        $payload = $this->buildCoursePayload($this->reloadCourse($course), $user);

        return [
            'percent' => $payload['progress']['percent'],
            'completed' => $payload['progress']['percent'] === 100,
            'resume_target' => $payload['resume_target'],
        ];
    }

    public function buildCoursePayload(Course $course, User $user): array
    {
        $lessonProgressEntries = CourseLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get();

        $lessonProgress = $lessonProgressEntries->groupBy('course_lesson_id');

        $examAttempts = CourseExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->orderByDesc('id')
            ->get()
            ->groupBy('course_exam_id');

        $totalItems = 0;
        $completedItems = 0;
        $modulesPayload = [];
        $nextTarget = null;
        $resumeTarget = null;

        $latestOpenedLessonProgress = $lessonProgressEntries
            ->filter(fn (CourseLessonProgress $progress) => $progress->opened_at)
            ->sortByDesc(fn (CourseLessonProgress $progress) => optional($progress->opened_at)->timestamp ?? 0)
            ->first();

        $previousModuleSatisfied = true;

        foreach ($course->modules as $moduleIndex => $module) {
            $moduleUnlocked = $moduleIndex === 0 ? true : $previousModuleSatisfied;
            $moduleLessons = $module->lessons->sortBy('sort_order')->values();
            $lessonsPayload = [];
            $allLessonsCompleted = $moduleLessons->isEmpty();
            $nextLessonUnlocked = $moduleUnlocked;

            foreach ($moduleLessons as $lesson) {
                $totalItems++;
                $progressEntries = $lessonProgress->get($lesson->id, collect());
                $latestProgress = $progressEntries->sortByDesc('id')->first();
                $isCompleted = $this->isLessonCompleted($progressEntries);
                $lessonUnlocked = $moduleUnlocked && $nextLessonUnlocked;

                if ($isCompleted) {
                    $completedItems++;
                } else {
                    $allLessonsCompleted = false;
                }

                if (! $nextTarget && $lessonUnlocked && ! $isCompleted) {
                    $nextTarget = ['type' => 'lesson', 'id' => $lesson->id];
                }

                if (
                    ! $resumeTarget
                    && $latestOpenedLessonProgress
                    && (int) $latestOpenedLessonProgress->course_lesson_id === (int) $lesson->id
                    && $lessonUnlocked
                    && ! $isCompleted
                ) {
                    $resumeTarget = ['type' => 'lesson', 'id' => $lesson->id];
                }

                $lessonsPayload[] = [
                    'id' => $lesson->id,
                    'title' => $lesson->getTranslations('title'),
                    'description' => $lesson->getTranslations('description'),
                    'duration_minutes' => $lesson->duration_minutes,
                    'is_free' => $lesson->is_free,
                    'is_active' => $lesson->is_active,
                    'is_unlocked' => $lessonUnlocked,
                    'is_completed' => $isCompleted,
                    'last_position_seconds' => $latestProgress?->last_position_seconds,
                    'video' => $this->lessonMediaPayloadService->buildVideoPayload($lesson),
                    'documents' => $lesson->getMedia('documents')->map(fn ($media) => [
                        'id' => $media->id,
                        'name' => $media->name,
                        'file_name' => $media->file_name,
                        'url' => $media->getFullUrl(),
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                    ])->values()->all(),
                ];

                $nextLessonUnlocked = $nextLessonUnlocked && $isCompleted;
            }

            $examPayload = null;
            $examPassed = $allLessonsCompleted;

            if ($module->exam) {
                $totalItems++;
                $attempts = $examAttempts->get($module->exam->id, collect());
                $submittedAttempts = $attempts
                    ->filter(fn (CourseExamAttempt $attempt) => $attempt->submitted_at !== null)
                    ->map(function (CourseExamAttempt $attempt) use ($module) {
                        $evaluation = $this->evaluateAttempt(
                            $module->exam,
                            is_array($attempt->answers_json) ? $attempt->answers_json : [],
                            $attempt->started_at,
                            $attempt->submitted_at,
                        );

                        if (
                            (float) $attempt->score !== (float) $evaluation['score']
                            || (bool) $attempt->passed !== (bool) $evaluation['passed']
                        ) {
                            $attempt->forceFill([
                                'score' => $evaluation['score'],
                                'passed' => $evaluation['passed'],
                            ])->save();
                        }

                        return [
                            'attempt' => $attempt,
                            'score' => $evaluation['score'],
                            'passed' => $evaluation['passed'],
                            'time_limit_exceeded' => $evaluation['time_limit_exceeded'],
                        ];
                    })
                    ->values();

                $latestSubmittedAttempt = $submittedAttempts->first();
                $openAttempt = $attempts->first(fn (CourseExamAttempt $attempt) => $attempt->submitted_at === null);
                $attemptsUsed = $submittedAttempts->count();
                $attemptsRemaining = $module->exam->max_attempts ? max($module->exam->max_attempts - $attemptsUsed, 0) : null;
                $examPassed = (bool) $submittedAttempts->first(fn (array $attempt) => $attempt['passed']);
                $examUnlocked = $moduleUnlocked && ($allLessonsCompleted || $examPassed || $openAttempt !== null);
                $attemptsExhausted = ! $examPassed && $openAttempt === null && $attemptsRemaining === 0;
                $bestScore = $submittedAttempts->isNotEmpty()
                    ? $submittedAttempts->max(fn (array $attempt) => (float) $attempt['score'])
                    : null;
                $latestResultStatus = $latestSubmittedAttempt
                    ? ($latestSubmittedAttempt['time_limit_exceeded']
                        ? 'timed_out'
                        : ($latestSubmittedAttempt['passed'] ? 'passed' : 'failed'))
                    : null;

                if ($examPassed) {
                    $completedItems++;
                } elseif (! $nextTarget && $examUnlocked) {
                    $nextTarget = ['type' => 'exam', 'id' => $module->exam->id];
                }

                if (! $resumeTarget && $openAttempt && $examUnlocked) {
                    $resumeTarget = ['type' => 'exam', 'id' => $module->exam->id];
                }

                $examPayload = [
                    'id' => $module->exam->id,
                    'title' => $module->exam->getTranslations('title'),
                    'pass_mark' => $module->exam->pass_mark,
                    'max_attempts' => $module->exam->max_attempts,
                    'time_limit_minutes' => $module->exam->time_limit_minutes,
                    'question_count' => $module->exam->questions->count(),
                    'is_unlocked' => $examUnlocked,
                    'is_passed' => $examPassed,
                    'latest_score' => $latestSubmittedAttempt['score'] ?? null,
                    'best_score' => $bestScore,
                    'latest_result_status' => $latestResultStatus,
                    'attempts_used' => $attemptsUsed,
                    'attempts_remaining' => $attemptsRemaining,
                    'attempts_exhausted' => $attemptsExhausted,
                    'active_attempt_id' => $openAttempt?->id,
                    'started_at' => optional($openAttempt?->started_at)->toIso8601String(),
                    'questions' => $examUnlocked ? $module->exam->questions->map(fn ($question) => [
                        'id' => $question->id,
                        'question' => $question->getTranslations('question'),
                        'options' => $question->options->map(fn ($option) => [
                            'id' => $option->id,
                            'option_text' => $option->getTranslations('option_text'),
                        ])->values()->all(),
                    ])->values()->all() : [],
                ];
            }

            $modulesPayload[] = [
                'id' => $module->id,
                'title' => $module->getTranslations('title'),
                'is_unlocked' => $moduleUnlocked,
                'is_completed' => $allLessonsCompleted && $examPassed,
                'lessons_count' => $moduleLessons->count(),
                'duration_minutes' => $moduleLessons->sum('duration_minutes'),
                'lessons' => $lessonsPayload,
                'exam' => $examPayload,
            ];

            $previousModuleSatisfied = $module->exam ? $examPassed : $allLessonsCompleted;
        }

        $percent = $totalItems > 0 ? (int) round(($completedItems / $totalItems) * 100) : 0;
        $resumeTarget ??= $nextTarget;
        return [
            'id' => $course->id,
            'title' => $course->getTranslations('title'),
            'slug' => $course->slug,
            'short_description' => $course->getTranslations('short_description'),
            'description' => $course->getTranslations('description'),
            'image' => $course->image,
            'price' => (float) $course->price,
            'duration_hours' => $course->duration_hours,
            'modules' => $modulesPayload,
            'progress' => [
                'completed_items' => $completedItems,
                'total_items' => $totalItems,
                'percent' => $percent,
            ],
            'resume_target' => $resumeTarget,
            'certificate' => $this->courseCertificateService->toPayload($user, $course, $percent === 100),
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->getTranslations('name'),
                'slug' => $course->category->slug,
            ] : null,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->getTranslations('name'),
                'slug' => $course->instructor->slug,
                'image' => $course->instructor->image,
            ] : [
                'name' => [
                    'ar' => $course->instructor_name,
                    'en' => $course->instructor_name,
                    'ku' => $course->instructor_name,
                ],
                'image' => $course->instructor_image,
            ],
        ];
    }

    protected function buildExamAttemptResponse(CourseExam $exam, CourseExamAttempt $attempt): array
    {
        $timeRemainingSeconds = null;

        if ($exam->time_limit_minutes && $attempt->started_at) {
            $expiresAt = $attempt->started_at->copy()->addMinutes($exam->time_limit_minutes)->addSeconds(self::EXAM_TIME_GRACE_SECONDS);
            $timeRemainingSeconds = max(0, Carbon::now()->diffInSeconds($expiresAt, false));
        }

        return [
            'attempt' => [
                'id' => $attempt->id,
                'attempt_number' => $attempt->attempt_number,
                'started_at' => optional($attempt->started_at)->toIso8601String(),
                'time_remaining_seconds' => $timeRemainingSeconds,
            ],
        ];
    }

    protected function reloadCourse(Course $course): Course
    {
        return $course->fresh([
            'category',
            'certificateTemplate.media',
            'instructor',
            'modules' => fn ($query) => $query->orderBy('sort_order'),
            'modules.lessons' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            'modules.exam' => fn ($query) => $query->where('is_active', true),
            'modules.exam.questions' => fn ($query) => $query->orderBy('sort_order'),
            'modules.exam.questions.options' => fn ($query) => $query->orderBy('sort_order'),
        ]);
    }

    protected function loadCourseBySlug(string $slug): Course
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category',
                'certificateTemplate.media',
                'instructor',
                'modules' => fn ($query) => $query->orderBy('sort_order'),
                'modules.lessons' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'modules.exam' => fn ($query) => $query->where('is_active', true),
                'modules.exam.questions' => fn ($query) => $query->orderBy('sort_order'),
                'modules.exam.questions.options' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->first();

        if (! $course) {
            throw new NotFoundHttpException();
        }

        return $course;
    }

    protected function getCourseForLesson(User $user, CourseLesson $lesson): Course
    {
        $lesson->loadMissing('module.course');
        $course = $lesson->module?->course;

        if (! $course || ! $course->is_active) {
            throw new NotFoundHttpException();
        }

        $this->assertEnrollment($user, $course);

        return $this->reloadCourse($course);
    }

    protected function getCourseForExam(User $user, CourseExam $exam): Course
    {
        $exam->loadMissing('module.course', 'questions.options');
        $course = $exam->module?->course;

        if (! $course || ! $course->is_active) {
            throw new NotFoundHttpException();
        }

        $this->assertEnrollment($user, $course);

        return $this->reloadCourse($course);
    }

    protected function assertEnrollment(User $user, Course $course): void
    {
        $isEnrolled = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            throw new AuthorizationException(__('You are not enrolled in this course.'));
        }
    }

    protected function findLessonState(array $payload, int $lessonId): ?array
    {
        foreach ($payload['modules'] as $module) {
            foreach ($module['lessons'] as $lesson) {
                if ((int) $lesson['id'] === $lessonId) {
                    return $lesson;
                }
            }
        }

        return null;
    }

    protected function findExamState(array $payload, int $examId): ?array
    {
        foreach ($payload['modules'] as $module) {
            if (($module['exam']['id'] ?? null) && (int) $module['exam']['id'] === $examId) {
                return $module['exam'];
            }
        }

        return null;
    }

    protected function isLessonCompleted(CourseLessonProgress|Collection|null $progress): bool
    {
        if ($progress instanceof CourseLessonProgress) {
            return (bool) ($progress->completed_at || $progress->status === 'completed');
        }

        if ($progress instanceof Collection) {
            return $progress->contains(
                fn (CourseLessonProgress $entry) => (bool) ($entry->completed_at || $entry->status === 'completed')
            );
        }

        return false;
    }

    protected function isExamUnlocked(User $user, Course $course, CourseExam $targetExam): bool
    {
        $lessonProgressEntries = CourseLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->groupBy('course_lesson_id');

        $examAttempts = CourseExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->groupBy('course_exam_id');

        $previousModuleSatisfied = true;

        foreach ($course->modules as $moduleIndex => $module) {
            $moduleUnlocked = $moduleIndex === 0 ? true : $previousModuleSatisfied;
            $moduleLessons = $module->lessons->sortBy('sort_order')->values();
            $allLessonsCompleted = $moduleLessons->every(
                fn (CourseLesson $lesson) => $this->isLessonCompleted($lessonProgressEntries->get($lesson->id, collect()))
            );

            $moduleExam = $module->exam;

            if ($moduleExam && (int) $moduleExam->id === (int) $targetExam->id) {
                return (bool) ($moduleUnlocked && $allLessonsCompleted);
            }

            $previousModuleSatisfied = $moduleExam
                ? $examAttempts->get($moduleExam->id, collect())->contains(function (CourseExamAttempt $attempt) use ($moduleExam) {
                    $evaluation = $this->evaluateAttempt(
                        $moduleExam,
                        is_array($attempt->answers_json) ? $attempt->answers_json : [],
                        $attempt->started_at,
                        $attempt->submitted_at,
                    );

                    return $evaluation['passed'];
                })
                : $allLessonsCompleted;
        }

        return false;
    }

    protected function evaluateAttempt(
        CourseExam $exam,
        array $answers,
        ?Carbon $startedAt,
        ?Carbon $submittedAt,
    ): array {
        $normalizedAnswers = collect($answers)
            ->mapWithKeys(fn ($optionId, $questionId) => [(int) $questionId => (int) $optionId])
            ->all();

        $correctCount = 0;
        $totalQuestions = max($exam->questions->count(), 1);

        foreach ($exam->questions as $question) {
            $selectedOptionId = $normalizedAnswers[$question->id] ?? null;
            $correctOption = $question->options->firstWhere('is_correct', true);

            if ($selectedOptionId && $correctOption && (int) $correctOption->id === (int) $selectedOptionId) {
                $correctCount++;
            }
        }

        $timeLimitExceeded = false;

        if ($exam->time_limit_minutes && $startedAt && $submittedAt) {
            $expiresAt = $startedAt->copy()
                ->addMinutes($exam->time_limit_minutes)
                ->addSeconds(self::EXAM_TIME_GRACE_SECONDS);

            $timeLimitExceeded = $submittedAt->greaterThan($expiresAt);
        }

        $score = $timeLimitExceeded ? 0 : round(($correctCount / $totalQuestions) * 100, 2);
        $passed = ! $timeLimitExceeded && $score >= $exam->pass_mark;

        return [
            'score' => $score,
            'passed' => $passed,
            'time_limit_exceeded' => $timeLimitExceeded,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
        ];
    }

    protected function resetModuleLearningCycle(User $user, CourseExam $exam): void
    {
        CourseLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_module_id', $exam->course_module_id)
            ->delete();

        CourseExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('course_exam_id', $exam->id)
            ->delete();
    }
}
