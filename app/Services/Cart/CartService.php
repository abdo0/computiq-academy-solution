<?php

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * CartService — manages shopping cart for both guests and authenticated users.
 *
 * ## Storage Strategy
 *
 * - **Guests**: Laravel session only (`guest_cart.course_ids` key).
 *   The session is shared between web and API routes because Sanctum's
 *   `statefulApi()` is enabled in bootstrap/app.php.
 *
 * - **Authenticated users**: Database table `cart_items`.
 *
 * ## Merge on Login
 *
 * When a guest logs in or registers, `mergeGuestCartIntoUser()` moves
 * session-stored course IDs into `cart_items`, skipping duplicates and
 * courses the user is already enrolled in.  This is called from:
 *   - UserAuthController::login()
 *   - UserAuthController::register()
 *   - SocialAuthController::callback()
 *
 * ## ⚠️  Past bugs — DO NOT reintroduce
 *
 * 1. **Do NOT use cookies for guest cart storage.**
 *    Laravel encrypts all cookies by default (EncryptCookies middleware).
 *    If you store a JSON array in a cookie and read it back with
 *    `json_decode()`, you'll get `null` because the value is encrypted.
 *    Excluding cookies from encryption is fragile.  Stick to sessions.
 *
 * 2. **Do NOT add `->middleware('web')` to cart API routes.**
 *    The API routes already participate in the web session via Sanctum's
 *    `statefulApi()` (which uses `EnsureFrontendRequestsAreStateful`).
 *    Adding `->middleware('web')` manually creates duplicate session
 *    handling and causes the session to be different between the web
 *    page load and API calls — resulting in an empty cart on full refresh.
 */
class CartService
{
    protected string $guestCartSessionKey = 'guest_cart.course_ids';

    public function payload(Request $request): array
    {
        $items = $this->items($request);

        return [
            'items' => $items->values()->all(),
            'count' => $items->count(),
            'total' => number_format((float) $items->sum('price'), 2, '.', ''),
        ];
    }

    public function items(Request $request): Collection
    {
        $user = $request->user();

        return $user
            ? $this->authenticatedItems($user)
            : $this->guestItems($request);
    }

    public function checkoutItems(Request $request): Collection
    {
        $user = $request->user();

        if ($user) {
            return CartItem::with('course')
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->filter(fn (CartItem $item) => $item->course !== null)
                ->map(fn (CartItem $item) => [
                    'id' => (string) $item->id,
                    'course_id' => (int) $item->course_id,
                    'price' => (float) $item->price,
                    'course' => $item->course,
                ])
                ->values();
        }

        return $this->guestCheckoutItems($request);
    }

    public function addCourse(Request $request, Course $course): array
    {
        $user = $request->user();

        if ($user) {
            if ($user->courseEnrollments()->where('course_id', $course->id)->exists()) {
                return [
                    'success' => false,
                    'already_enrolled' => true,
                    'message' => __('You are already enrolled in this course.'),
                    'status' => 409,
                ];
            }

            $existing = CartItem::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                return [
                    'success' => false,
                    'already_exists' => true,
                    'message' => __('This course is already in your cart.'),
                    'status' => 409,
                ];
            }

            $item = CartItem::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'price' => $course->price,
            ]);

            return [
                'success' => true,
                'message' => __('Course added to cart successfully.'),
                'item_id' => $item->id,
                'count' => CartItem::where('user_id', $user->id)->count(),
                'status' => 201,
            ];
        }

        $guestCourseIds = $this->guestCourseIds($request);

        if (in_array($course->id, $guestCourseIds, true)) {
            return [
                'success' => false,
                'already_exists' => true,
                'message' => __('This course is already in your cart.'),
                'status' => 409,
            ];
        }

        $guestCourseIds[] = $course->id;
        $this->storeGuestCourseIds($request, $guestCourseIds);

        return [
            'success' => true,
            'message' => __('Course added to cart successfully.'),
            'item_id' => $course->id,
            'count' => count($guestCourseIds),
            'status' => 201,
        ];
    }

    public function removeCourse(Request $request, int $courseId): bool
    {
        $user = $request->user();

        if ($user) {
            return (bool) CartItem::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->delete();
        }

        $guestCourseIds = $this->guestCourseIds($request);
        $nextCourseIds = array_values(array_filter(
            $guestCourseIds,
            fn (int $id) => $id !== $courseId
        ));

        if (count($nextCourseIds) === count($guestCourseIds)) {
            return false;
        }

        $this->storeGuestCourseIds($request, $nextCourseIds);

        return true;
    }

    public function clear(Request $request): void
    {
        $user = $request->user();

        if ($user) {
            CartItem::where('user_id', $user->id)->delete();

            return;
        }

        $request->session()->forget($this->guestCartSessionKey);
    }

    public function count(Request $request): int
    {
        $user = $request->user();

        if ($user) {
            return CartItem::where('user_id', $user->id)->count();
        }

        return count($this->guestCourseIds($request));
    }

    public function mergeGuestCartIntoUser(Request $request, User $user): array
    {
        $guestCourseIds = $this->guestCourseIds($request);

        if ($guestCourseIds === []) {
            return [
                'merged_count' => 0,
                'skipped_duplicates' => 0,
                'skipped_enrolled' => 0,
            ];
        }

        $existingCourseIds = CartItem::where('user_id', $user->id)
            ->whereIn('course_id', $guestCourseIds)
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $enrolledCourseIds = $user->courseEnrollments()
            ->whereIn('course_id', $guestCourseIds)
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $duplicateCount = count(array_intersect($guestCourseIds, $existingCourseIds));
        $enrolledCount = count(array_intersect($guestCourseIds, $enrolledCourseIds));
        $blockedCourseIds = array_values(array_unique(array_merge($existingCourseIds, $enrolledCourseIds)));
        $courseIdsToAdd = array_values(array_diff($guestCourseIds, $blockedCourseIds));

        $courses = Course::query()
            ->whereIn('id', $courseIdsToAdd)
            ->get()
            ->keyBy('id');

        $mergedCount = 0;

        foreach ($courseIdsToAdd as $courseId) {
            $course = $courses->get($courseId);

            if (! $course) {
                continue;
            }

            CartItem::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'price' => $course->price,
            ]);

            $mergedCount++;
        }

        $request->session()->forget($this->guestCartSessionKey);

        return [
            'merged_count' => $mergedCount,
            'skipped_duplicates' => $duplicateCount,
            'skipped_enrolled' => $enrolledCount,
        ];
    }

    protected function authenticatedItems(User $user): Collection
    {
        return CartItem::with('course')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->filter(fn (CartItem $item) => $item->course !== null)
            ->map(function (CartItem $item) {
                return [
                    'id' => $item->id,
                    'course_id' => $item->course_id,
                    'price' => $item->price,
                    'added_at' => $item->created_at->toISOString(),
                    'course' => $this->coursePayload($item->course),
                ];
            })
            ->values();
    }

    protected function guestItems(Request $request): Collection
    {
        $courses = $this->guestCourses($request);
        $timestamp = now()->toISOString();

        return collect($this->guestCourseIds($request))
            ->map(function (int $courseId) use ($courses, $timestamp) {
                /** @var Course|null $course */
                $course = $courses->get($courseId);

                if (! $course) {
                    return null;
                }

                return [
                    'id' => $courseId,
                    'course_id' => $courseId,
                    'price' => number_format((float) $course->price, 2, '.', ''),
                    'added_at' => $timestamp,
                    'course' => $this->coursePayload($course),
                ];
            })
            ->filter()
            ->values();
    }

    protected function guestCheckoutItems(Request $request): Collection
    {
        $courses = $this->guestCourses($request);

        return collect($this->guestCourseIds($request))
            ->map(function (int $courseId) use ($courses) {
                /** @var Course|null $course */
                $course = $courses->get($courseId);

                if (! $course) {
                    return null;
                }

                return [
                    'id' => (string) $courseId,
                    'course_id' => $courseId,
                    'price' => (float) $course->price,
                    'course' => $course,
                ];
            })
            ->filter()
            ->values();
    }

    protected function guestCourses(Request $request): Collection
    {
        $courseIds = $this->guestCourseIds($request);

        if ($courseIds === []) {
            return collect();
        }

        return Course::query()
            ->whereIn('id', $courseIds)
            ->get()
            ->keyBy('id');
    }

    protected function guestCourseIds(Request $request): array
    {
        return collect($request->session()->get($this->guestCartSessionKey, []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }

    protected function storeGuestCourseIds(Request $request, array $courseIds): void
    {
        $request->session()->put(
            $this->guestCartSessionKey,
            array_values(array_unique(array_map('intval', $courseIds)))
        );
    }

    protected function coursePayload(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'image' => $course->image,
            'price' => number_format((float) $course->price, 2, '.', ''),
            'old_price' => $course->old_price,
            'instructor_name' => $course->instructor_name,
            'duration_hours' => $course->duration_hours,
        ];
    }
}
