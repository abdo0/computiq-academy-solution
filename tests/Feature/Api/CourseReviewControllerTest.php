<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $email = 'student@example.com'): User
    {
        return User::create([
            'name' => 'Student User',
            'email' => $email,
            'password' => bcrypt('password'),
            'is_active' => true,
            'locale' => 'en',
            'active_role' => 'student',
        ]);
    }

    protected function createCourse(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => ['en' => 'Frontend Foundations', 'ar' => 'اساسيات الواجهة'],
            'slug' => 'frontend-foundations',
            'short_description' => ['en' => 'Build user interfaces', 'ar' => 'بناء الواجهات'],
            'description' => ['en' => 'Detailed course description', 'ar' => 'وصف الدورة'],
            'instructor_name' => 'Instructor Name',
            'rating' => 0,
            'review_count' => 0,
            'duration_hours' => 12,
            'students_count' => 10,
            'price' => 99,
            'is_active' => true,
            'is_live' => false,
            'delivery_type' => 'online',
            'is_best_seller' => false,
        ], $overrides));
    }

    public function test_public_reviews_endpoint_returns_paginated_reviews(): void
    {
        $course = $this->createCourse();

        CourseReview::create([
            'course_id' => $course->id,
            'user_name' => 'First Student',
            'rating' => 5,
            'comment' => 'Excellent course',
        ]);

        CourseReview::create([
            'course_id' => $course->id,
            'user_name' => 'Second Student',
            'rating' => 4,
            'comment' => 'Very useful',
        ]);

        $this->getJson("/api/v1/courses/{$course->slug}/reviews")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_enrolled_user_can_submit_and_update_a_review(): void
    {
        $user = $this->createUser();
        $course = $this->createCourse();

        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/courses/{$course->slug}/reviews", [
            'rating' => 5,
            'comment' => 'Loved the practical lessons.',
        ])->assertCreated()
            ->assertJsonPath('data.review.user_name', 'Student User')
            ->assertJsonPath('data.course_summary.review_count', 1)
            ->assertJsonPath('data.course_summary.rating', 5);

        $this->postJson("/api/v1/courses/{$course->slug}/reviews", [
            'rating' => 4,
            'comment' => 'Updated after finishing the course.',
        ])->assertCreated()
            ->assertJsonPath('data.course_summary.review_count', 1)
            ->assertJsonPath('data.course_summary.rating', 4);

        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $course->id,
            'user_id' => $user->id,
            'comment' => 'Updated after finishing the course.',
        ]);

        $this->assertDatabaseCount('course_reviews', 1);
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'review_count' => 1,
            'rating' => 4.0,
        ]);
    }

    public function test_non_enrolled_user_cannot_submit_review(): void
    {
        $user = $this->createUser('notenrolled@example.com');
        $course = $this->createCourse(['slug' => 'backend-foundations']);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/courses/{$course->slug}/reviews", [
            'rating' => 5,
            'comment' => 'Trying to review without enrollment.',
        ])->assertForbidden()
            ->assertJsonPath('message', 'You must be enrolled to review this course.');
    }
}
