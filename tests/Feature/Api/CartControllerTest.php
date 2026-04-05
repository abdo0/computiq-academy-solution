<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_view_and_clear_cart_using_session(): void
    {
        $course = $this->createCourse('guest-course', 125);

        $this->postJson('/api/v1/cart', [
            'course_id' => $course->id,
        ])->assertCreated()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.cart.count', 1);

        $this->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.items.0.course_id', $course->id);

        $this->deleteJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 0)
            ->assertJsonPath('data.cart.count', 0);

        $this->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 0);
    }

    public function test_guest_cart_is_persisted_in_cookie_for_follow_up_requests(): void
    {
        $course = $this->createCourse('guest-cookie-course', 145);

        $response = $this->postJson('/api/v1/cart', [
            'course_id' => $course->id,
        ])->assertCreated();

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($queuedCookie) => $queuedCookie->getName() === 'guest_cart_course_ids');

        $this->assertNotNull($cookie);

        $this->withCookie($cookie->getName(), $cookie->getValue())
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.items.0.course_id', $course->id);
    }

    public function test_guest_cannot_add_same_course_to_cart_twice(): void
    {
        $course = $this->createCourse('duplicate-course', 100);

        $this->postJson('/api/v1/cart', [
            'course_id' => $course->id,
        ])->assertCreated();

        $cookieValue = json_encode([$course->id]);

        $this->withCookie('guest_cart_course_ids', $cookieValue)->postJson('/api/v1/cart', [
            'course_id' => $course->id,
        ])->assertStatus(409)
            ->assertJsonPath('data.already_exists', true);
    }

    public function test_login_merges_guest_cart_into_existing_user_cart_and_skips_duplicates_and_enrollments(): void
    {
        $newCourse = $this->createCourse('new-course', 120);
        $duplicateCourse = $this->createCourse('duplicate-login-course', 130);
        $enrolledCourse = $this->createCourse('enrolled-login-course', 140);

        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
            'locale' => 'en',
            'active_role' => 'student',
            'is_active' => true,
        ]);
        $user->ensureDefaultAppRole();

        CartItem::create([
            'user_id' => $user->id,
            'course_id' => $duplicateCourse->id,
            'price' => $duplicateCourse->price,
        ]);

        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $enrolledCourse->id,
            'enrolled_at' => now(),
        ]);

        $this->withSession([
            'guest_cart.course_ids' => [$newCourse->id, $duplicateCourse->id, $enrolledCourse->id],
        ])->postJson('/api/v1/user/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('data.cart_merge.merged_count', 1)
            ->assertJsonPath('data.cart_merge.skipped_duplicates', 1)
            ->assertJsonPath('data.cart_merge.skipped_enrolled', 1);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'course_id' => $newCourse->id,
        ]);
        $this->assertDatabaseCount('cart_items', 2);

        $this->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 2);
    }

    public function test_register_transfers_guest_cart_to_new_account(): void
    {
        $course = $this->createCourse('register-cart-course', 99);

        $this->withSession([
            'guest_cart.course_ids' => [$course->id],
        ])->postJson('/api/v1/user/register', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '9647700000000',
            'locale' => 'en',
        ])->assertCreated()
            ->assertJsonPath('data.cart_merge.merged_count', 1)
            ->assertJsonPath('data.user.email', 'newstudent@example.com');

        $user = User::where('email', 'newstudent@example.com')->firstOrFail();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    protected function createCourse(string $slug, float $price): Course
    {
        return Course::create([
            'title' => ['en' => ucfirst($slug), 'ar' => ucfirst($slug), 'ku' => ucfirst($slug)],
            'slug' => $slug,
            'short_description' => ['en' => 'Short', 'ar' => 'Short', 'ku' => 'Short'],
            'description' => ['en' => 'Description', 'ar' => 'Description', 'ku' => 'Description'],
            'price' => $price,
            'is_active' => true,
        ]);
    }
}
