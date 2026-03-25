<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => ['en' => 'Mohammed K.', 'ar' => 'محمد ك.', 'ku' => 'محەممەد ک.'],
                'comment' => [
                    'en' => 'Zero hidden fees meant all the money went straight to the orphanage we supported. Great Experience.',
                    'ar' => 'عدم وجود رسوم خفية يعني أن كل الأموال ذهبت مباشرة إلى دار الأيتام التي دعمناها. تجربة رائعة.',
                    'ku' => 'نەبوونی هیچ کرێیەکی شاراوە واتە هەموو پارەکە ڕاستەوخۆ چوو بۆ ئەو خانەی بێسەرپەرشتانەی پاڵپشتیمان کرد. ئەزموونێکی نایاب.'
                ],
                'rating' => 5,
                'sort_order' => 1,
            ],
            [
                'name' => ['en' => 'Sarah J.', 'ar' => 'سارة ج.', 'ku' => 'سارە ج.'],
                'comment' => [
                    'en' => 'The support team helped me set up my community fundraiser step-by-step. Highly recommend. Amazing Support Team.',
                    'ar' => 'فريق الدعم ساعدني في إعداد حملة التبرع المجتمعية الخاصة بي خطوة بخطوة. أوصي بهم بشدة. فريق دعم مذهل.',
                    'ku' => 'تیمی پاڵپشتی یارمەتیان دام هەنگاو بە هەنگاو کەمپەینی کۆکردنەوەی بەخشینی کۆمەڵگاکەم دابمەزرێنم. زۆر پێشنیاری دەکەم. تیمی پاڵپشتی سەرسوڕهێنەر.'
                ],
                'rating' => 5,
                'sort_order' => 2,
            ],
            [
                'name' => ['en' => 'Ahmed S.', 'ar' => 'أحمد س.', 'ku' => 'ئەحمەد س.'],
                'comment' => [
                    'en' => 'I managed to raise funds for my medical campaign very fast. The process was smooth. Fast & Reliable Platform.',
                    'ar' => 'تمكنت من جمع الأموال لحملتي الطبية بسرعة كبيرة. كانت العملية سلسة. منصة سريعة وموثوقة.',
                    'ku' => 'توانیم زۆر بە خێرایی پارە بۆ کەمپەینە پزیشکییەکەم کۆبکەمەوە. پرۆسەکە ئاسان بوو. پلاتفۆرمی خێرا و متمانەپێکراو.'
                ],
                'rating' => 5,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['sort_order' => $testimonial['sort_order']],
                $testimonial
            );
        }
    }
}
