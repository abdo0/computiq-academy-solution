<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * Get paginated list of courses with filters.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Course::where('is_active', true)
            ->with(['category', 'instructor']);

        // Filter by Category Slug
        if ($request->has('category') && $request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('Category'));
            });
        }

        // Search by Title/Description
        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = '%' . $request->input('Search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('short_description', 'like', $searchTerm);
            });
        }

        // Sorting
        $sort = $request->input('Sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('students_count', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $courses = $query->paginate($request->input('Per page', 12));

        $data = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->getTranslations('title'),
                'slug' => $course->slug,
                'image' => $course->image,
                'rating' => (float) $course->rating,
                'review_count' => $course->review_count,
                'duration_hours' => $course->duration_hours,
                'students_count' => $course->students_count,
                'price' => (float) $course->price,
                'old_price' => $course->old_price ? (float) $course->old_price : null,
                'is_live' => $course->is_live,
                'is_best_seller' => $course->is_best_seller,
                'category' => $course->category ? [
                    'id' => $course->category->id,
                    'name' => $course->category->getTranslations('name'),
                    'slug' => $course->category->slug,
                ] : null,
                'instructor' => $course->instructor ? [
                    'name' => $course->instructor->getTranslations('name'),
                    'slug' => $course->instructor->slug,
                    'image' => $course->instructor->image,
                ] : [
                    'name' => ['ar' => $course->instructor_name, 'en' => $course->instructor_name],
                    'image' => $course->instructor_image,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Courses retrieved successfully',
            'data' => [
                'data' => $data,
                'meta' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                ],
            ]
        ]);
    }

    /**
     * Get full course details by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $course = Course::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category',
                'instructor',
                'modules.lessons',
                'reviews',
            ])
            ->firstOrFail();

        $data = [
            'id' => $course->id,
            'title' => $course->getTranslations('title'),
            'slug' => $course->slug,
            'short_description' => $course->getTranslations('short_description'),
            'description' => $course->getTranslations('description'),
            'image' => $course->image,
            'rating' => (float) $course->rating,
            'review_count' => $course->review_count,
            'duration_hours' => $course->duration_hours,
            'students_count' => $course->students_count,
            'price' => (float) $course->price,
            'old_price' => $course->old_price ? (float) $course->old_price : null,
            'is_live' => $course->is_live,
            'is_best_seller' => $course->is_best_seller,
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->getTranslations('name'),
                'slug' => $course->category->slug,
            ] : null,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->getTranslations('name'),
                'slug' => $course->instructor->slug,
                'title' => $course->instructor->getTranslations('title'),
                'bio' => $course->instructor->getTranslations('bio'),
                'image' => $course->instructor->image,
                'social_links' => $course->instructor->social_links,
                'courses_count' => $course->instructor->courses()->where('is_active', true)->count(),
                'total_students' => $course->instructor->courses()->where('is_active', true)->sum('students_count'),
                'total_reviews' => $course->instructor->courses()->where('is_active', true)->sum('review_count'),
            ] : [
                'name' => ['ar' => $course->instructor_name, 'en' => $course->instructor_name],
                'image' => $course->instructor_image,
            ],
            'modules' => $course->modules->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->getTranslations('title'),
                'lessons_count' => $m->lessons->count(),
                'duration_minutes' => $m->lessons->sum('duration_minutes'),
                'lessons' => $m->lessons->map(fn ($l) => [
                    'id' => $l->id,
                    'title' => $l->getTranslations('title'),
                    'duration_minutes' => $l->duration_minutes,
                    'is_free' => $l->is_free,
                ])->toArray(),
            ])->toArray(),
            'reviews' => $course->reviews->take(20)->map(fn ($r) => [
                'id' => $r->id,
                'user_name' => $r->user_name,
                'user_image' => $r->user_image,
                'rating' => (float) $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at->diffForHumans(),
            ])->toArray(),
        ];

        return response()->success($data, 'Course details retrieved successfully');
    }
}
