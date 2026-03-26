<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use Illuminate\Http\JsonResponse;

class LearningPathController extends Controller
{
    /**
     * Get paginated list of learning paths.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $paths = LearningPath::where('is_active', true)
            ->withCount('courses')
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 12));

        $paths->getCollection()->transform(function ($path) {
            $data = $path->toArray();
            
            // Generate full image URL if needed
            if ($path->image) {
                // If it's starting with http, leave it. Otherwise prepend storage/
                if (!str_starts_with($path->image, 'http') && !str_starts_with($path->image, '/')) {
                    $data['image'] = url('storage/' . $path->image);
                }
            }

            return $data;
        });

        return response()->json([
            'status' => 'success',
            'data' => $paths,
        ]);
    }

    /**
     * Get details of a single learning path with its ranked courses.
     */
    public function show(string $slug): JsonResponse
    {
        $path = LearningPath::where('slug', $slug)
            ->where('is_active', true)
            ->with(['courses' => function ($query) {
                $query->where('is_active', true)
                      ->with(['instructor', 'category']);
            }])
            ->first();

        if (!$path) {
            return response()->json([
                'status' => 'error',
                'message' => 'Path not found.',
            ], 404);
        }

        $data = $path->toArray();
        
        if ($path->image) {
            if (!str_starts_with($path->image, 'http') && !str_starts_with($path->image, '/')) {
                $data['image'] = url('storage/' . $path->image);
            }
        }

        // Standardize course images
        if (isset($data['courses']) && is_array($data['courses'])) {
            foreach ($data['courses'] as &$course) {
                if (isset($course['image']) && $course['image']) {
                    if (!str_starts_with($course['image'], 'http') && !str_starts_with($course['image'], '/')) {
                        $course['image'] = url('storage/' . $course['image']);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
