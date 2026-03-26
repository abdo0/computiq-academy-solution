<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * List the authenticated user's cart items with course details.
     */
    public function index(Request $request): JsonResponse
    {
        $items = CartItem::with('course')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function (CartItem $item) {
                return [
                    'id' => $item->id,
                    'course_id' => $item->course_id,
                    'price' => $item->price,
                    'added_at' => $item->created_at->toISOString(),
                    'course' => $item->course ? [
                        'id' => $item->course->id,
                        'title' => $item->course->title,
                        'slug' => $item->course->slug,
                        'image' => $item->course->image,
                        'price' => $item->course->price,
                        'old_price' => $item->course->old_price,
                        'instructor_name' => $item->course->instructor_name,
                        'duration_hours' => $item->course->duration_hours,
                    ] : null,
                ];
            });

        $total = $items->sum('price');

        return response()->json([
            'data' => [
                'items' => $items,
                'count' => $items->count(),
                'total' => number_format($total, 2, '.', ''),
            ],
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
        $user = $request->user();

        // Check if already in cart
        $existing = CartItem::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => __('This course is already in your cart.'),
                'data' => ['already_exists' => true],
            ], 409);
        }

        $item = CartItem::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'price' => $course->price,
        ]);

        $count = CartItem::where('user_id', $user->id)->count();

        return response()->json([
            'message' => __('Course added to cart successfully.'),
            'data' => [
                'item_id' => $item->id,
                'count' => $count,
            ],
        ], 201);
    }

    /**
     * Remove a specific course from the cart.
     */
    public function destroy(Request $request, int $courseId): JsonResponse
    {
        $deleted = CartItem::where('user_id', $request->user()->id)
            ->where('course_id', $courseId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => __('Item not found in your cart.'),
            ], 404);
        }

        $count = CartItem::where('user_id', $request->user()->id)->count();

        return response()->json([
            'message' => __('Course removed from cart.'),
            'data' => ['count' => $count],
        ]);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => __('Your cart has been cleared.'),
            'data' => ['count' => 0],
        ]);
    }
}
