<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    /**
     * Get instructor profile by slug with their courses.
     */
    public function show(string $slug): JsonResponse
    {
        $instructor = Instructor::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $courses = $instructor->courses()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->getTranslations('title'),
                'slug' => $c->slug,
                'short_description' => $c->getTranslations('short_description'),
                'image' => $c->image,
                'rating' => (float) $c->rating,
                'review_count' => $c->review_count,
                'duration_hours' => $c->duration_hours,
                'students_count' => $c->students_count,
                'price' => (float) $c->price,
                'old_price' => $c->old_price ? (float) $c->old_price : null,
                'is_live' => $c->is_live,
                'is_best_seller' => $c->is_best_seller,
                'category_slug' => $c->category?->slug,
            ])
            ->toArray();

        $data = [
            'id' => $instructor->id,
            'name' => $instructor->getTranslations('name'),
            'slug' => $instructor->slug,
            'title' => $instructor->getTranslations('title'),
            'bio' => $instructor->getTranslations('bio'),
            'image' => $instructor->image,
            'social_links' => $instructor->social_links,
            'stats' => [
                'courses_count' => count($courses),
                'total_students' => array_sum(array_column($courses, 'students_count')),
                'total_reviews' => array_sum(array_column($courses, 'review_count')),
                'average_rating' => count($courses) > 0 ? round(array_sum(array_column($courses, 'rating')) / count($courses), 1) : 0,
            ],
            'courses' => $courses,
        ];

        return response()->success($data, 'Instructor profile retrieved successfully');
    }
}
