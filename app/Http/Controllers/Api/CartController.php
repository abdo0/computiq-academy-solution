<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    /**
     * List the current cart items with course details.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->cartService->payload($request),
        ]);
    }

    /**
     * Add a course to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $result = $this->cartService->addCourse($request, $course);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'message' => $result['message'] ?? __('Unable to add course to cart.'),
                'data' => [
                    'already_exists' => (bool) ($result['already_exists'] ?? false),
                    'already_enrolled' => (bool) ($result['already_enrolled'] ?? false),
                ],
            ], $result['status'] ?? 409);
        }

        return response()->json([
            'message' => $result['message'] ?? __('Course added to cart successfully.'),
            'data' => [
                'item_id' => $result['item_id'] ?? null,
                'count' => $result['count'] ?? $this->cartService->count($request),
                'cart' => $this->cartService->payload($request),
            ],
        ], $result['status'] ?? 201);
    }

    /**
     * Remove a specific course from the cart.
     */
    public function destroy(Request $request, int $courseId): JsonResponse
    {
        $deleted = $this->cartService->removeCourse($request, $courseId);

        if (!$deleted) {
            return response()->json([
                'message' => __('Item not found in your cart.'),
            ], 404);
        }

        return response()->json([
            'message' => __('Course removed from cart.'),
            'data' => [
                'count' => $this->cartService->count($request),
                'cart' => $this->cartService->payload($request),
            ],
        ]);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request);

        return response()->json([
            'message' => __('Your cart has been cleared.'),
            'data' => [
                'count' => 0,
                'cart' => $this->cartService->payload($request),
            ],
        ]);
    }
}
