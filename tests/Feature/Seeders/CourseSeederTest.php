<?php

namespace Tests\Feature\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use Database\Seeders\CourseSeeder;
use Database\Seeders\InstructorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_seeder_always_attaches_a_valid_promo_video_configuration(): void
    {
        Storage::fake('public');
        config()->set('media-library.disk_name', 'public');

        $this->seed([
            InstructorSeeder::class,
            CourseSeeder::class,
        ]);

        $courses = Course::query()->get();

        $this->assertNotEmpty($courses);

        foreach ($courses as $course) {
            $this->assertContains($course->promo_video_source_type, ['embed', 'upload']);

            if ($course->promo_video_source_type === 'embed') {
                $this->assertNotNull($course->promo_video_url);
                $this->assertNotNull($course->promo_embed_url);
                $this->assertSame(0, $course->getMedia('promo_video')->count());
                continue;
            }

            $this->assertSame('upload', $course->promo_video_source_type);
            $this->assertNotNull($course->getFirstMedia('promo_video'));
        }
    }

    public function test_course_seeder_attaches_spatie_images_for_course_categories(): void
    {
        Storage::fake('public');
        config()->set('media-library.disk_name', 'public');

        $this->seed([
            InstructorSeeder::class,
            CourseSeeder::class,
        ]);

        $categories = CourseCategory::query()->get();

        $this->assertNotEmpty($categories);

        foreach ($categories as $category) {
            $this->assertTrue($category->show_on_home);
            $this->assertNotNull($category->getFirstMedia('image'));
        }
    }
}
