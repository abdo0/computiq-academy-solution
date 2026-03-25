<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Database\Seeder;

class CourseReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviewerNames = [
            'محمد علي', 'فاطمة أحمد', 'خالد عمر', 'نورة سالم',
            'عبدالله حسين', 'هدى محمود', 'يوسف ابراهيم', 'سلمى كريم',
            'أمير حسن', 'ريم خالد', 'عمر فؤاد', 'لينا عباس',
        ];

        $reviewTemplates = [
            ['rating' => 5.0, 'comment' => 'دورة ممتازة! المحتوى واضح ومنظم والمدرب محترف جداً. أنصح بها بشدة لكل من يريد التعلم.'],
            ['rating' => 4.5, 'comment' => 'محتوى رائع وشرح مبسط. استفدت كثيراً من الأمثلة العملية والمشاريع التطبيقية.'],
            ['rating' => 5.0, 'comment' => 'أفضل دورة حصلت عليها! غيرت مساري المهني بالكامل. شكرًا للمدرب على الجهد الكبير.'],
            ['rating' => 4.0, 'comment' => 'دورة جيدة جداً. أتمنى لو كان هناك المزيد من التمارين العملية، لكن بشكل عام ممتازة.'],
            ['rating' => 4.5, 'comment' => 'شرح واضح ومنهجي. المدرب يجيب على كل الأسئلة بصبر. تجربة تعليمية مميزة.'],
            ['rating' => 5.0, 'comment' => 'من أفضل الدورات المتاحة! المحتوى محدث والأمثلة عملية. حصلت على ترقية بفضل هذه الدورة.'],
            ['rating' => 3.5, 'comment' => 'دورة جيدة ومفيدة. بعض المواضيع تحتاج تحديث لكن الأساسيات قوية جداً.'],
            ['rating' => 4.5, 'comment' => 'استثمار ممتاز في تطوير المهارات. المادة العلمية غنية والمدرب خبير حقيقي.'],
            ['rating' => 5.0, 'comment' => 'تجربة تعليمية لا مثيل لها! كل درس مبني على السابق بشكل منطقي. أنصح الجميع بالالتحاق.'],
            ['rating' => 4.0, 'comment' => 'دورة شاملة ومتكاملة. تغطي كل ما تحتاجه. فقط أتمنى لو كانت أطول قليلاً.'],
        ];

        $courses = Course::all();

        foreach ($courses as $course) {
            $numReviews = rand(5, 10);
            $usedNames = [];

            for ($i = 0; $i < $numReviews; $i++) {
                // Pick a unique reviewer name
                do {
                    $name = $reviewerNames[array_rand($reviewerNames)];
                } while (in_array($name, $usedNames) && count($usedNames) < count($reviewerNames));
                $usedNames[] = $name;

                $template = $reviewTemplates[$i % count($reviewTemplates)];

                CourseReview::updateOrCreate(
                    ['course_id' => $course->id, 'user_name' => $name],
                    [
                        'user_image' => 'https://i.pravatar.cc/150?img=' . rand(1, 70),
                        'rating' => $template['rating'],
                        'comment' => $template['comment'],
                    ]
                );
            }
        }
    }
}
