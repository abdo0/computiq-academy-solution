<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across courses and instructors.
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'courses' => ['data' => [], 'meta' => ['total' => 0]],
                    'instructors' => [],
                    'query' => $q,
                ],
            ]);
        }

        $searchTerm = '%' . $q . '%';

        // --- Courses ---
        $coursesQuery = Course::where('is_active', true)
            ->with(['category', 'instructor'])
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', $searchTerm)
                      ->orWhere('description', 'like', $searchTerm)
                      ->orWhere('short_description', 'like', $searchTerm)
                      ->orWhere('instructor_name', 'like', $searchTerm);
            });

        $courses = $coursesQuery->paginate($request->input('per_page', 12));

        $coursesData = $courses->map(function ($course) {
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

        // --- Instructors ---
        $instructors = Instructor::where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                      ->orWhere('title', 'like', $searchTerm);
            })
            ->withCount(['courses' => fn ($q) => $q->where('is_active', true)])
            ->limit(10)
            ->get()
            ->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->getTranslations('name'),
                    'slug' => $instructor->slug,
                    'title' => $instructor->getTranslations('title'),
                    'image' => $instructor->image,
                    'courses_count' => $instructor->courses_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => [
                    'data' => $coursesData,
                    'meta' => [
                        'current_page' => $courses->currentPage(),
                        'last_page' => $courses->lastPage(),
                        'per_page' => $courses->perPage(),
                        'total' => $courses->total(),
                    ],
                ],
                'instructors' => $instructors,
                'query' => $q,
            ],
        ]);
    }
}
