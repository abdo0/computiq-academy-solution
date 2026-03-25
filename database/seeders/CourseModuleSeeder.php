<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;
use Illuminate\Database\Seeder;

class CourseModuleSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        // Module/lesson templates per course category type
        $moduleTemplates = [
            'technology' => [
                ['title' => ['ar' => 'مقدمة وأساسيات', 'en' => 'Introduction & Fundamentals', 'ku' => 'پێشەکی و بنەماکان'], 'lessons' => [
                    ['title' => ['ar' => 'نظرة عامة على الدورة', 'en' => 'Course Overview', 'ku' => 'تێڕوانینی گشتی'], 'duration' => 15, 'free' => true],
                    ['title' => ['ar' => 'إعداد بيئة التطوير', 'en' => 'Setting Up Development Environment', 'ku' => 'دامەزراندنی ژینگەی گەشەپێدان'], 'duration' => 25, 'free' => true],
                    ['title' => ['ar' => 'المفاهيم الأساسية', 'en' => 'Core Concepts', 'ku' => 'چەمکە سەرەکییەکان'], 'duration' => 30, 'free' => false],
                    ['title' => ['ar' => 'أول تطبيق عملي', 'en' => 'First Hands-on Exercise', 'ku' => 'یەکەم ڕاهێنانی کرداری'], 'duration' => 40, 'free' => false],
                ]],
                ['title' => ['ar' => 'المستوى المتوسط', 'en' => 'Intermediate Level', 'ku' => 'ئاستی ناوەند'], 'lessons' => [
                    ['title' => ['ar' => 'أنماط التصميم', 'en' => 'Design Patterns', 'ku' => 'شێوازەکانی دیزاین'], 'duration' => 35, 'free' => false],
                    ['title' => ['ar' => 'إدارة البيانات', 'en' => 'Data Management', 'ku' => 'بەڕێوەبردنی داتا'], 'duration' => 45, 'free' => false],
                    ['title' => ['ar' => 'التعامل مع الأخطاء', 'en' => 'Error Handling', 'ku' => 'مامەڵە لەگەڵ هەڵەکان'], 'duration' => 30, 'free' => false],
                ]],
                ['title' => ['ar' => 'المستوى المتقدم', 'en' => 'Advanced Level', 'ku' => 'ئاستی پێشکەوتوو'], 'lessons' => [
                    ['title' => ['ar' => 'تحسين الأداء', 'en' => 'Performance Optimization', 'ku' => 'باشترکردنی ئەدا'], 'duration' => 50, 'free' => false],
                    ['title' => ['ar' => 'الأمان وأفضل الممارسات', 'en' => 'Security & Best Practices', 'ku' => 'ئاسایش و باشترین ڕێوشوێنەکان'], 'duration' => 40, 'free' => false],
                    ['title' => ['ar' => 'النشر والتوزيع', 'en' => 'Deployment & Distribution', 'ku' => 'بڵاوکردنەوە و دابەشکردن'], 'duration' => 35, 'free' => false],
                ]],
                ['title' => ['ar' => 'مشروع التخرج', 'en' => 'Final Project', 'ku' => 'پرۆژەی کۆتایی'], 'lessons' => [
                    ['title' => ['ar' => 'تخطيط المشروع', 'en' => 'Project Planning', 'ku' => 'پلاندانانی پرۆژە'], 'duration' => 20, 'free' => false],
                    ['title' => ['ar' => 'بناء المشروع', 'en' => 'Building the Project', 'ku' => 'دروستکردنی پرۆژە'], 'duration' => 60, 'free' => false],
                    ['title' => ['ar' => 'مراجعة وتقييم', 'en' => 'Review & Assessment', 'ku' => 'پێداچوونەوە و هەڵسەنگاندن'], 'duration' => 30, 'free' => false],
                ]],
            ],
            'default' => [
                ['title' => ['ar' => 'المقدمة والتعريف', 'en' => 'Introduction & Overview', 'ku' => 'پێشەکی و ناساندن'], 'lessons' => [
                    ['title' => ['ar' => 'مرحباً بكم في الدورة', 'en' => 'Welcome to the Course', 'ku' => 'بەخێربێن بۆ خوێندنەکە'], 'duration' => 10, 'free' => true],
                    ['title' => ['ar' => 'ما الذي ستتعلمه', 'en' => 'What You Will Learn', 'ku' => 'چی فێردەبیت'], 'duration' => 15, 'free' => true],
                    ['title' => ['ar' => 'المتطلبات الأساسية', 'en' => 'Prerequisites', 'ku' => 'پێداویستییەکان'], 'duration' => 10, 'free' => false],
                ]],
                ['title' => ['ar' => 'الأساسيات', 'en' => 'Fundamentals', 'ku' => 'بنەماکان'], 'lessons' => [
                    ['title' => ['ar' => 'المبادئ الأساسية', 'en' => 'Core Principles', 'ku' => 'ڕەوشتەکانی بنەڕەتی'], 'duration' => 30, 'free' => false],
                    ['title' => ['ar' => 'الأدوات والتقنيات', 'en' => 'Tools & Techniques', 'ku' => 'ئامراز و تەکنیکەکان'], 'duration' => 35, 'free' => false],
                    ['title' => ['ar' => 'دراسة حالة عملية', 'en' => 'Practical Case Study', 'ku' => 'لێکۆڵینەوەی کرداری'], 'duration' => 45, 'free' => false],
                ]],
                ['title' => ['ar' => 'التطبيق العملي', 'en' => 'Practical Application', 'ku' => 'جێبەجێکردنی کرداری'], 'lessons' => [
                    ['title' => ['ar' => 'تمارين تفاعلية', 'en' => 'Interactive Exercises', 'ku' => 'ڕاهێنانی کاریگەر'], 'duration' => 40, 'free' => false],
                    ['title' => ['ar' => 'مشروع عملي', 'en' => 'Hands-on Project', 'ku' => 'پرۆژەی کرداری'], 'duration' => 50, 'free' => false],
                    ['title' => ['ar' => 'اختبار نهائي وشهادة', 'en' => 'Final Test & Certificate', 'ku' => 'تاقیکردنەوەی کۆتایی و بڕوانامە'], 'duration' => 30, 'free' => false],
                ]],
            ],
        ];

        foreach ($courses as $course) {
            $catSlug = $course->category?->slug ?? '';
            $isTech = in_array($catSlug, ['technology', 'artificial-intelligence']);
            $templates = $isTech ? $moduleTemplates['technology'] : $moduleTemplates['default'];

            foreach ($templates as $mIdx => $modData) {
                $module = CourseModule::updateOrCreate(
                    ['course_id' => $course->id, 'sort_order' => $mIdx],
                    ['title' => $modData['title'], 'sort_order' => $mIdx]
                );

                foreach ($modData['lessons'] as $lIdx => $lessonData) {
                    CourseLesson::updateOrCreate(
                        ['course_module_id' => $module->id, 'sort_order' => $lIdx],
                        [
                            'title' => $lessonData['title'],
                            'duration_minutes' => $lessonData['duration'],
                            'is_free' => $lessonData['free'],
                            'sort_order' => $lIdx,
                        ]
                    );
                }
            }
        }
    }
}
