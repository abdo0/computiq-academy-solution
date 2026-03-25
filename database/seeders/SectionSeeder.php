<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'key' => 'home_hero_extra',
                'title' => [
                    'en' => "Learn Your Way",
                    'ar' => 'اتعلم بطريقتك',
                    'ku' => 'بە شێوازی خۆت فێربە',
                ],
                'description' => [
                    'en' => 'The best educational platform offering diverse courses tailored to your needs.',
                    'ar' => 'أفضل منصة تعليمية توفر دورات متنوعة تناسب احتياجاتك',
                    'ku' => 'باشترین سەکۆی فێربوون کە کۆرسی جۆراوجۆر پێشکەش دەکات',
                ],
                'extra_data' => [
                    'cta_text' => [
                        'en' => 'Start Now',
                        'ar' => 'ابدأ الآن',
                        'ku' => 'ئێستا دەستپێبکە',
                    ],
                    'secondary_cta_text' => [
                        'en' => 'Explore Courses',
                        'ar' => 'تصفح الدورات التدريبية',
                        'ku' => 'کۆرسەکان بپشکنە',
                    ],
                    'background_image' => null,
                ],
            ],
            [
                'key' => 'home_main_courses',
                'title' => [
                    'en' => 'Explore the Courses',
                    'ar' => 'استكشف الدورات التدريبية',
                    'ku' => 'کۆرسەکان بپشکنە',
                ],
                'description' => null,
                'extra_data' => []
            ],
            [
                'key' => 'home_category_cards',
                'title' => [
                    'en' => 'Learn and Develop Top Soft Skills',
                    'ar' => 'تعلم وطور أهم المهارات الشخصية',
                    'ku' => 'فێربە و پەرە بە کارامەییە کەسییەکان بدە',
                ],
                'description' => null,
                'extra_data' => [
                	'categories' => [
		                [
		                	'title' => [
                                'en' => 'Engineering',
                                'ar' => 'هندسة',
                                'ku' => 'ئەندازیاری'
                            ],
		                	'count' => 54,
		                	'image' => '/assets/temp/cat1.jpg' // Assuming frontend handles absolute or relative paths
		                ],
		                [
		                	'title' => [
                                'en' => 'Self Development',
                                'ar' => 'التطوير الذاتي',
                                'ku' => 'پەرەپێدانی کەسی'
                            ],
		                	'count' => 120,
		                	'image' => '/assets/temp/cat2.jpg'
		                ],
		                [
		                	'title' => [
                                'en' => 'Business',
                                'ar' => 'تأسيس بزنس',
                                'ku' => 'بازرگانی'
                            ],
		                	'count' => 85,
		                	'image' => '/assets/temp/cat3.jpg'
		                ]
                	]
                ]
            ],
            [
                'key' => 'home_academic_partners',
                'title' => [
                    'en' => 'Academic Partners',
                    'ar' => 'الشركاء الأكاديميون',
                    'ku' => 'هاوبەشە ئەکادیمییەکان',
                ],
                'description' => null,
                'extra_data' => [
                	'logos' => [
                		'/assets/temp/partner1.png',
                		'/assets/temp/partner2.png',
                		'/assets/temp/partner3.png',
                		'/assets/temp/partner4.png',
                		'/assets/temp/partner5.png'
                	]
                ]
            ],
            [
                'key' => 'home_ai_courses',
                'title' => [
                    'en' => 'Develop Your AI Skills',
                    'ar' => 'طور مهاراتك في الذكاء الاصطناعي',
                    'ku' => 'پەرە بە کارامەییەکانی ژیری دەستکردت بدە',
                ],
                'description' => null,
                'extra_data' => []
            ],
            [
                'key' => 'home_marketing_courses',
                'title' => [
                    'en' => 'Develop Your Marketing Skills',
                    'ar' => 'طور مهاراتك في التسويق',
                    'ku' => 'پەرە بە کارامەییەکانی بەبازاڕکردنت بدە',
                ],
                'description' => null,
                'extra_data' => []
            ],
            [
                'key' => 'home_business_banner',
                'title' => [
                    'en' => 'Develop Your Team with Computiq Business',
                    'ar' => 'طور فريقك مع Computiq Business',
                    'ku' => 'پەرە بە تیمەکەت بدە لەگەڵ Computiq Business',
                ],
                'description' => [
                    'en' => 'Tailored learning paths mapped to your organization needs.',
                    'ar' => 'مسارات تعليمية مخصصة ومصممة خصيصاً لتلبية احتياجات فريقك',
                    'ku' => 'ڕێچکەی فێربوونی تایبەت بە پێداویستییەکانی ڕێکخراوەکەت',
                ],
                'extra_data' => [
                	'cta_text' => [
                        'en' => 'Contact Us',
                        'ar' => 'تواصل معنا',
                        'ku' => 'پەیوەندیمان پێوە بکە'
                    ]
                ]
            ],
            [
                'key' => 'home_instructor_banner',
                'title' => [
                    'en' => 'Share your expertise with thousands',
                    'ar' => 'شارك خبرتك مع آلاف المتعلمين...',
                    'ku' => 'ئەزموونەکەت لەگەڵ هەزاران کەسدا بەش بکە',
                ],
                'description' => [
                    'en' => 'Join our platform as an instructor and start teaching today.',
                    'ar' => 'انضم لمنصتنا كمدرب وابدأ بالتدريس اليوم',
                    'ku' => 'وەک ڕاهێنەر پەیوەندی بە پلاتفۆرمەکەمانەوە بکە'
                ],
                'extra_data' => [
                	'cta_text' => [
                        'en' => 'Join as Instructor',
                        'ar' => 'انضم كمدرب',
                        'ku' => 'وەک ڕاهێنەر بەشداربە'
                    ],
                    'image' => null
                ]
            ],
        ];

        foreach ($sections as $section) {
            \App\Models\Section::updateOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}
