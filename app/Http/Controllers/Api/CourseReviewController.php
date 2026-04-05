<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $reviews = CourseReview::query()
            ->where('course_id', $course->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => [
                'data' => $reviews->getCollection()->map(fn (CourseReview $review) => $this->formatReview($review))->all(),
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ],
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = $request->user();
        $isEnrolled = $user->courseEnrollments()
            ->where('course_id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            return response()->json([
                'message' => __('You must be enrolled to review this course.'),
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = CourseReview::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $user->id,
            ],
            [
                'user_name' => $user->real_name ?: $user->name,
                'user_image' => null,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        $stats = $this->syncCourseReviewStats($course);

        return response()->json([
            'data' => [
                'review' => $this->formatReview($review->fresh()),
                'course_summary' => $stats,
            ],
            'message' => __('Review submitted successfully.'),
        ], 201);
    }

    protected function syncCourseReviewStats(Course $course): array
    {
        $reviewQuery = CourseReview::query()->where('course_id', $course->id);
        $reviewCount = (int) $reviewQuery->count();
        $rating = (float) number_format((float) ($reviewQuery->avg('rating') ?? 0), 1, '.', '');

        $course->forceFill([
            'review_count' => $reviewCount,
            'rating' => $rating,
        ])->save();

        return [
            'rating' => $rating,
            'review_count' => $reviewCount,
        ];
    }

    protected function formatReview(CourseReview $review): array
    {
        return [
            'id' => $review->id,
            'user_name' => $review->user_name,
            'user_image' => $review->user_image,
            'rating' => (float) $review->rating,
            'comment' => $review->comment,
            'created_at' => optional($review->created_at)->diffForHumans(),
        ];
    }
}
