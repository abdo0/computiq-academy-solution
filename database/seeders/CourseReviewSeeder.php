<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviewTemplates = [
            ['rating' => 5.0, 'comment' => 'دورة ممتازة! المحتوى واضح ومنظم، وكنت أطبّق كل جزء مباشرة بعد الشراء.'],
            ['rating' => 4.5, 'comment' => 'استفدت كثيرًا من الأمثلة العملية. بعد إنهاء عدة دروس شعرت أن قيمة الدورة كانت فعلًا مستحقة.'],
            ['rating' => 5.0, 'comment' => 'اشتريت الدورة لأطور مستواي، وكانت من أفضل القرارات. الشرح مرتب والتدرج ممتاز.'],
            ['rating' => 4.0, 'comment' => 'الدورة قوية جدًا وتستحق الشراء. كنت أتمنى فقط وجود تمارين إضافية في بعض الأجزاء.'],
            ['rating' => 4.5, 'comment' => 'محتوى احترافي ومنهجي، والمدرب يشرح النقاط المهمة بوضوح. تجربة تعليمية موفقة جدًا.'],
            ['rating' => 5.0, 'comment' => 'بعد شراء الدورة وإنهائها حصلت على فائدة عملية مباشرة. الأمثلة والمحتوى محدثان جدًا.'],
            ['rating' => 4.0, 'comment' => 'الدورة مفيدة والشراء كان موفقًا. أكثر ما أعجبني هو ترتيب الدروس وربطها بالمشاريع.'],
        ];

        $seededStudentIds = User::query()
            ->where('email', 'like', 'student-demo-%@computiq.test')
            ->pluck('id');

        DB::table('course_reviews')->whereNull('user_id')->delete();
        DB::table('course_reviews')->whereIn('user_id', $seededStudentIds)->delete();

        $courses = Course::query()
            ->with([
                'enrollments' => fn ($query) => $query
                    ->whereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', 'student-demo-%@computiq.test'))
                    ->with('user')
                    ->orderBy('enrolled_at'),
            ])
            ->get();

        foreach ($courses as $courseIndex => $course) {
            $eligibleEnrollments = $course->enrollments
                ->filter(fn ($enrollment) => $enrollment->user)
                ->unique('user_id')
                ->values();

            if ($eligibleEnrollments->isEmpty()) {
                $course->forceFill([
                    'review_count' => 0,
                    'rating' => 0,
                ])->save();
                continue;
            }

            $reviewsToCreate = min($eligibleEnrollments->count(), 3 + ($courseIndex % 3));

            foreach ($eligibleEnrollments->take($reviewsToCreate)->values() as $reviewIndex => $enrollment) {
                $user = $enrollment->user;
                $template = $reviewTemplates[($courseIndex + $reviewIndex) % count($reviewTemplates)];
                $reviewedAt = ($enrollment->enrolled_at?->copy() ?? now()->subDays(10 + $reviewIndex))
                    ->addDays(min(6, $reviewIndex + 2));

                if ($reviewedAt->greaterThan(now())) {
                    $reviewedAt = now()->subHours(($reviewIndex + 1) * 6);
                }

                CourseReview::create([
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'user_name' => $user->real_name ?: $user->name,
                    'user_image' => 'https://i.pravatar.cc/150?img=' . (($user->id % 70) + 1),
                    'rating' => $template['rating'],
                    'comment' => $template['comment'],
                    'created_at' => $reviewedAt,
                    'updated_at' => $reviewedAt,
                ]);
            }

            $courseReviews = CourseReview::query()->where('course_id', $course->id);

            $course->forceFill([
                'review_count' => $courseReviews->count(),
                'rating' => (float) number_format((float) ($courseReviews->avg('rating') ?? 0), 1, '.', ''),
            ])->save();
        }
    }
}
