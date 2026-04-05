<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createCourse(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => ['en' => 'Course Title', 'ar' => 'عنوان الدورة'],
            'slug' => 'course-title-' . uniqid(),
            'short_description' => ['en' => 'Short description', 'ar' => 'وصف قصير'],
            'description' => ['en' => 'Detailed course description', 'ar' => 'وصف الدورة'],
            'instructor_name' => 'Instructor Name',
            'rating' => 4.5,
            'review_count' => 3,
            'duration_hours' => 20,
            'students_count' => 120,
            'price' => 150,
            'is_active' => true,
            'is_live' => false,
            'delivery_type' => 'online',
            'is_best_seller' => false,
        ], $overrides));
    }

    protected function createModule(Course $course, array $overrides = []): CourseModule
    {
        return CourseModule::create(array_merge([
            'course_id' => $course->id,
            'title' => ['en' => 'Module Title', 'ar' => 'عنوان الوحدة'],
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createLesson(CourseModule $module, array $overrides = []): CourseLesson
    {
        return CourseLesson::create(array_merge([
            'course_module_id' => $module->id,
            'title' => ['en' => 'Lesson Title', 'ar' => 'عنوان الدرس'],
            'description' => ['en' => 'Lesson description', 'ar' => 'وصف الدرس'],
            'duration_minutes' => 18,
            'content_type' => 'video',
            'video_source_type' => 'embed',
            'video_provider' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=preview123',
            'embed_url' => 'https://www.youtube.com/embed/preview123',
            'is_free' => true,
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    public function test_courses_index_can_filter_by_delivery_type(): void
    {
        $onsiteCourse = $this->createCourse([
            'slug' => 'onsite-course',
            'delivery_type' => 'onsite',
        ]);

        $this->createCourse([
            'slug' => 'online-course',
            'delivery_type' => 'online',
        ]);

        $this->getJson('/api/v1/courses?delivery_type=onsite')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.slug', $onsiteCourse->slug)
            ->assertJsonPath('data.data.0.delivery_type', 'onsite');
    }

    public function test_course_details_include_delivery_type(): void
    {
        $course = $this->createCourse([
            'slug' => 'hybrid-course',
            'delivery_type' => 'hybrid',
        ]);

        $this->getJson("/api/v1/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('data.delivery_type', 'hybrid');
    }

    public function test_course_details_include_preview_metadata_for_lessons(): void
    {
        $course = $this->createCourse(['slug' => 'preview-metadata-course']);
        $module = $this->createModule($course);
        $lesson = $this->createLesson($module, ['is_free' => true]);

        $this->getJson("/api/v1/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('data.modules.0.lessons.0.id', $lesson->id)
            ->assertJsonPath('data.modules.0.lessons.0.is_preview_available', true);
    }

    public function test_course_details_include_embed_promo_video_payload(): void
    {
        $course = $this->createCourse([
            'slug' => 'promo-embed-course',
            'promo_video_source_type' => 'embed',
            'promo_video_provider' => 'youtube',
            'promo_video_url' => 'https://www.youtube.com/watch?v=promo123',
            'promo_embed_url' => 'https://www.youtube.com/embed/promo123',
        ]);

        $this->getJson("/api/v1/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('data.has_promo_video', true)
            ->assertJsonPath('data.promo_video.source_type', 'embed')
            ->assertJsonPath('data.promo_video.embed_url', 'https://www.youtube.com/embed/promo123');
    }

    public function test_course_details_include_upload_promo_video_payload(): void
    {
        Storage::fake('public');
        config()->set('media-library.disk_name', 'public');

        $course = $this->createCourse([
            'slug' => 'promo-upload-course',
            'promo_video_source_type' => 'upload',
        ]);

        $tempVideoPath = tempnam(sys_get_temp_dir(), 'course-promo-video-');
        file_put_contents($tempVideoPath, hex2bin('00000018667479706d703432000000006d70343269736f6d'));

        $course
            ->addMedia($tempVideoPath)
            ->usingFileName('course-promo.mp4')
            ->toMediaCollection('promo_video');

        $this->getJson("/api/v1/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('data.has_promo_video', true)
            ->assertJsonPath('data.promo_video.source_type', 'upload')
            ->assertJsonPath('data.promo_video.mime_type', 'video/mp4');
    }

    public function test_course_details_return_null_when_promo_video_is_missing(): void
    {
        $course = $this->createCourse([
            'slug' => 'promo-missing-course',
            'promo_video_source_type' => null,
        ]);

        $this->getJson("/api/v1/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('data.has_promo_video', false)
            ->assertJsonPath('data.promo_video', null);
    }

    public function test_course_details_do_not_expose_lesson_video_payloads(): void
    {
        $course = $this->createCourse(['slug' => 'public-course-security']);
        $module = $this->createModule($course);
        $freeLesson = $this->createLesson($module, ['is_free' => true, 'sort_order' => 1]);
        $paidLesson = $this->createLesson($module, ['is_free' => false, 'sort_order' => 2]);

        $response = $this->getJson("/api/v1/courses/{$course->slug}")
            ->assertOk();

        $response->assertJsonPath('data.modules.0.lessons.0.id', $freeLesson->id);
        $response->assertJsonPath('data.modules.0.lessons.1.id', $paidLesson->id);
        $response->assertJsonMissingPath('data.modules.0.lessons.0.video');
        $response->assertJsonMissingPath('data.modules.0.lessons.1.video');
    }

    public function test_free_lesson_preview_endpoint_returns_embed_video_payload(): void
    {
        $course = $this->createCourse(['slug' => 'free-preview-course']);
        $module = $this->createModule($course);
        $lesson = $this->createLesson($module, [
            'title' => ['en' => 'Preview Lesson', 'ar' => 'درس المعاينة'],
            'video_url' => 'https://www.youtube.com/watch?v=free-preview-video',
        ]);

        $this->getJson("/api/v1/courses/{$course->slug}/lessons/{$lesson->id}/preview")
            ->assertOk()
            ->assertJsonPath('data.lesson.id', $lesson->id)
            ->assertJsonPath('data.lesson.video.source_type', 'embed')
            ->assertJsonPath('data.lesson.video.embed_url', 'https://www.youtube.com/embed/free-preview-video')
            ->assertJsonPath('data.module.id', $module->id);
    }

    public function test_free_lesson_preview_endpoint_returns_upload_video_payload(): void
    {
        Storage::fake('public');
        config()->set('media-library.disk_name', 'public');

        $course = $this->createCourse(['slug' => 'upload-preview-course']);
        $module = $this->createModule($course);
        $lesson = $this->createLesson($module, [
            'video_source_type' => 'upload',
            'video_provider' => null,
            'video_url' => null,
            'embed_url' => null,
        ]);

        $tempVideoPath = tempnam(sys_get_temp_dir(), 'preview-video-');
        file_put_contents($tempVideoPath, hex2bin('00000018667479706d703432000000006d70343269736f6d'));

        $lesson
            ->addMedia($tempVideoPath)
            ->usingFileName('lesson-preview.mp4')
            ->toMediaCollection('video');

        $this->getJson("/api/v1/courses/{$course->slug}/lessons/{$lesson->id}/preview")
            ->assertOk()
            ->assertJsonPath('data.lesson.video.source_type', 'upload')
            ->assertJsonPath('data.lesson.video.mime_type', 'video/mp4');
    }

    public function test_paid_lesson_preview_endpoint_is_forbidden(): void
    {
        $course = $this->createCourse(['slug' => 'paid-preview-course']);
        $module = $this->createModule($course);
        $lesson = $this->createLesson($module, ['is_free' => false]);

        $this->getJson("/api/v1/courses/{$course->slug}/lessons/{$lesson->id}/preview")
            ->assertForbidden();
    }

    public function test_lesson_preview_endpoint_returns_not_found_for_lesson_from_different_course(): void
    {
        $course = $this->createCourse(['slug' => 'visible-course']);
        $otherCourse = $this->createCourse(['slug' => 'other-course']);
        $module = $this->createModule($otherCourse);
        $lesson = $this->createLesson($module);

        $this->getJson("/api/v1/courses/{$course->slug}/lessons/{$lesson->id}/preview")
            ->assertNotFound();
    }

    public function test_lesson_preview_endpoint_returns_not_found_for_inactive_lessons_or_courses(): void
    {
        $inactiveCourse = $this->createCourse([
            'slug' => 'inactive-course',
            'is_active' => false,
        ]);
        $inactiveCourseModule = $this->createModule($inactiveCourse);
        $inactiveCourseLesson = $this->createLesson($inactiveCourseModule);

        $activeCourse = $this->createCourse(['slug' => 'active-course']);
        $activeModule = $this->createModule($activeCourse);
        $inactiveLesson = $this->createLesson($activeModule, [
            'is_active' => false,
        ]);

        $this->getJson("/api/v1/courses/{$inactiveCourse->slug}/lessons/{$inactiveCourseLesson->id}/preview")
            ->assertNotFound();

        $this->getJson("/api/v1/courses/{$activeCourse->slug}/lessons/{$inactiveLesson->id}/preview")
            ->assertNotFound();
    }
}
