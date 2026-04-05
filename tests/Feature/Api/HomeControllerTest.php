<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createCategory(array $overrides = []): CourseCategory
    {
        return CourseCategory::create(array_merge([
            'name' => ['en' => 'Technology', 'ar' => 'التكنولوجيا'],
            'slug' => 'technology-' . uniqid(),
            'is_active' => true,
            'show_on_home' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    protected function createCourse(CourseCategory $category, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'course_category_id' => $category->id,
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

    public function test_home_endpoint_returns_only_home_categories_with_active_course_counts(): void
    {
        Cache::flush();

        $visibleCategory = $this->createCategory([
            'slug' => 'visible-category',
            'name' => ['en' => 'Visible Category', 'ar' => 'تصنيف ظاهر'],
            'sort_order' => 1,
        ]);
        $hiddenCategory = $this->createCategory([
            'slug' => 'hidden-category',
            'name' => ['en' => 'Hidden Category', 'ar' => 'تصنيف مخفي'],
            'show_on_home' => false,
            'sort_order' => 2,
        ]);
        $emptyCategory = $this->createCategory([
            'slug' => 'empty-category',
            'name' => ['en' => 'Empty Category', 'ar' => 'تصنيف فارغ'],
            'sort_order' => 3,
        ]);

        $this->createCourse($visibleCategory, ['slug' => 'visible-course-1']);
        $this->createCourse($visibleCategory, ['slug' => 'visible-course-2']);
        $this->createCourse($visibleCategory, ['slug' => 'inactive-visible-course', 'is_active' => false]);
        $this->createCourse($hiddenCategory, ['slug' => 'hidden-course']);

        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonCount(1, 'data.course_categories')
            ->assertJsonPath('data.course_categories.0.slug', 'visible-category')
            ->assertJsonPath('data.course_categories.0.courses_count', 2)
            ->assertJsonMissing(['slug' => 'hidden-category'])
            ->assertJsonMissing(['slug' => 'empty-category']);
    }
}
