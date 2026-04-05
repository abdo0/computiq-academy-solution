<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LearningAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $email = 'learner@example.com'): User
    {
        return User::create([
            'name' => 'Learner User',
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
            'title' => ['en' => 'Secure Course', 'ar' => 'دورة مؤمنة'],
            'slug' => 'secure-course',
            'short_description' => ['en' => 'Short description', 'ar' => 'وصف قصير'],
            'description' => ['en' => 'Detailed course description', 'ar' => 'وصف الدورة'],
            'instructor_name' => 'Instructor Name',
            'rating' => 4.5,
            'review_count' => 0,
            'duration_hours' => 10,
            'students_count' => 0,
            'price' => 120,
            'is_active' => true,
            'is_live' => false,
            'delivery_type' => 'online',
            'is_best_seller' => false,
        ], $overrides));
    }

    protected function createModule(Course $course): CourseModule
    {
        return CourseModule::create([
            'course_id' => $course->id,
            'title' => ['en' => 'Module One', 'ar' => 'الوحدة الأولى'],
            'sort_order' => 1,
        ]);
    }

    protected function createLesson(CourseModule $module, array $overrides = []): CourseLesson
    {
        return CourseLesson::create(array_merge([
            'course_module_id' => $module->id,
            'title' => ['en' => 'Protected Lesson', 'ar' => 'درس محمي'],
            'description' => ['en' => 'Lesson description', 'ar' => 'وصف الدرس'],
            'duration_minutes' => 24,
            'content_type' => 'video',
            'video_source_type' => 'embed',
            'video_provider' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=protected-video',
            'embed_url' => 'https://www.youtube.com/embed/protected-video',
            'is_free' => false,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    public function test_non_enrolled_user_cannot_access_learning_course_payload(): void
    {
        $user = $this->createUser();
        $course = $this->createCourse();
        $module = $this->createModule($course);
        $this->createLesson($module);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/learning/courses/{$course->slug}")
            ->assertForbidden();
    }

    public function test_enrolled_user_can_access_learning_course_payload(): void
    {
        $user = $this->createUser('enrolled@example.com');
        $course = $this->createCourse(['slug' => 'enrolled-course']);
        $module = $this->createModule($course);
        $lesson = $this->createLesson($module, ['is_free' => false]);

        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/learning/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('data.modules.0.lessons.0.id', $lesson->id)
            ->assertJsonPath('data.modules.0.lessons.0.video.source_type', 'embed');
    }
}
