<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningPath;
use App\Models\Course;

class LearningPathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::take(5)->get();

        if ($courses->isEmpty()) {
            return;
        }

        $path1 = LearningPath::updateOrCreate(
            ['slug' => 'full-stack-web-development'],
            [
            'title' => [
                'en' => 'Full-Stack Web Development',
                'ar' => 'تطوير الويب المتكامل',
                'ku' => 'پەرەپێدانی وێب'
            ],
            'description' => [
                'en' => 'Master both frontend and backend development with this comprehensive path covering HTML, CSS, JavaScript, React, PHP, and Laravel.',
                'ar' => 'أتقن تطوير الواجهات الأمامية والخلفية مع هذا المسار الشامل الذي يغطي HTML و CSS و JavaScript و React و PHP و Laravel.',
                'ku' => 'فێربوونی پەرەپێدانی وێب بە شێوەیەکی تەواو بە فێربوونی HTML, CSS, JavaScript, React, PHP و Laravel.'
            ],
            'icon' => 'Code',
            'color' => '#3b82f6', // Blue
            'estimated_hours' => 120,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $path2 = LearningPath::updateOrCreate(
            ['slug' => 'data-science-machine-learning'],
            [
            'title' => [
                'en' => 'Data Science & Machine Learning',
                'ar' => 'علم البيانات وتعلم الآلة',
                'ku' => 'زانستی داتا و فێربوونی ئامێر'
            ],
            'description' => [
                'en' => 'Dive into data analysis, visualization, and building predictive models using Python, Pandas, and Scikit-Learn.',
                'ar' => 'تعمق في تحليل البيانات وتصورها وبناء النماذج التنبؤية باستخدام Python و Pandas و Scikit-Learn.',
                'ku' => 'فێربوونی شیکاری داتا و بینین و دروستکردنی مۆدێلی پێشبینیکردن بە بەکارهێنانی Python, Pandas و Scikit-Learn.'
            ],
            'icon' => 'Database',
            'color' => '#10b981', // Emerald
            'estimated_hours' => 90,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Attach courses to Path 1
        if ($courses->count() >= 3) {
            $path1->courses()->syncWithoutDetaching([
                $courses[0]->id => ['sort_order' => 1],
                $courses[1]->id => ['sort_order' => 2],
                $courses[2]->id => ['sort_order' => 3],
            ]);
        }

        // Attach courses to Path 2
        if ($courses->count() >= 5) {
            $path2->courses()->syncWithoutDetaching([
                $courses[3]->id => ['sort_order' => 1],
                $courses[4]->id => ['sort_order' => 2],
            ]);
        } elseif ($courses->count() > 0) {
            $path2->courses()->syncWithoutDetaching([
                $courses->last()->id => ['sort_order' => 1],
            ]);
        }
    }
}
