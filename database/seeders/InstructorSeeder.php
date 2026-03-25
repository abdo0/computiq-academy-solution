<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $instructors = [
            [
                'name'  => ['ar' => 'جون دانيال', 'en' => 'John Daniel', 'ku' => 'جۆن دانیەل'],
                'slug'  => 'john-daniel',
                'title' => ['ar' => 'خبير برمجة ومطور ويب بخبرة 15 عاماً', 'en' => 'Senior Software Engineer & Web Developer with 15+ years experience', 'ku' => 'پسپۆڕی پرۆگرامینگ و گەشەپێدەری وێب'],
                'bio'   => [
                    'ar' => "مهندس برمجيات أول بخبرة تزيد عن 15 عامًا في تطوير تطبيقات الويب والموبايل. عمل مع شركات تقنية كبرى مثل Google وMicrosoft.\n\nحاصل على درجة الماجستير في علوم الحاسوب من جامعة MIT. قام بتدريب أكثر من 20,000 طالب عبر منصات التعليم المختلفة.",
                    'en' => "Senior software engineer with 15+ years in web and mobile application development. Worked with major tech companies including Google and Microsoft.\n\nHolds a Master's degree in Computer Science from MIT. Has trained over 20,000 students across various learning platforms.",
                    'ku' => 'ئەندازیاری نەرمەکاڵای سەرەکی بە زیاتر لە ١٥ ساڵ ئەزموون لە گەشەپێدانی ئەپی وێب و مۆبایل.',
                ],
                'image' => 'https://i.pravatar.cc/300?img=11',
                'social_links' => ['twitter' => '#', 'linkedin' => '#', 'website' => '#'],
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name'  => ['ar' => 'سمير حسن', 'en' => 'Samir Hassan', 'ku' => 'سەمیر حەسەن'],
                'slug'  => 'samir-hassan',
                'title' => ['ar' => 'مطور واجهات أمامية ومدرب React معتمد', 'en' => 'Frontend Developer & Certified React Trainer', 'ku' => 'گەشەپێدەری فرۆنتئێند و ڕاهێنەری React'],
                'bio'   => [
                    'ar' => "مطور واجهات أمامية متقدم متخصص في React و Next.js. ساهم في مشاريع مفتوحة المصدر عديدة وقدم محاضرات في مؤتمرات تقنية دولية.\n\nيتميز بأسلوب تعليمي عملي يركز على بناء مشاريع حقيقية.",
                    'en' => "Advanced frontend developer specializing in React and Next.js. Contributed to numerous open source projects and presented at international tech conferences.\n\nKnown for a hands-on teaching style focused on building real-world projects.",
                    'ku' => 'گەشەپێدەری پێشەوەی پێشکەوتوو تایبەتمەند لە React و Next.js.',
                ],
                'image' => 'https://i.pravatar.cc/300?img=60',
                'social_links' => ['twitter' => '#', 'linkedin' => '#', 'website' => '#'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name'  => ['ar' => 'سارة خالد', 'en' => 'Sarah Khalid', 'ku' => 'سارا خالید'],
                'slug'  => 'sarah-khalid',
                'title' => ['ar' => 'عالمة بيانات وباحثة في الذكاء الاصطناعي', 'en' => 'Data Scientist & AI Researcher', 'ku' => 'زانای داتا و توێژەری زیرەکی دەستکرد'],
                'bio'   => [
                    'ar' => "حاصلة على درجة الدكتوراه في الذكاء الاصطناعي من جامعة كامبريدج. عملت كباحثة في DeepMind ولديها أكثر من 10 أبحاث منشورة.\n\nمتخصصة في التعلم العميق ومعالجة اللغات الطبيعية.",
                    'en' => "PhD in Artificial Intelligence from Cambridge University. Worked as a researcher at DeepMind with 10+ published papers.\n\nSpecializes in deep learning and natural language processing.",
                    'ku' => 'دکتۆرا لە زیرەکی دەستکرد لە زانکۆی کامبریج.',
                ],
                'image' => 'https://i.pravatar.cc/300?img=5',
                'social_links' => ['twitter' => '#', 'linkedin' => '#', 'website' => '#'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name'  => ['ar' => 'أحمد محمود', 'en' => 'Ahmed Mahmoud', 'ku' => 'ئەحمەد مەحموود'],
                'slug'  => 'ahmed-mahmoud',
                'title' => ['ar' => 'خبير تسويق رقمي وإعلانات مدفوعة', 'en' => 'Digital Marketing & Paid Ads Expert', 'ku' => 'پسپۆڕی مارکێتنگی دیجیتاڵ'],
                'bio'   => [
                    'ar' => "خبير تسويق رقمي بخبرة 12 عامًا مع وكالات إعلانات رائدة. أدار حملات بميزانيات تتجاوز 10 ملايين دولار.\n\nمعتمد من Google وMeta في إدارة الإعلانات.",
                    'en' => "Digital marketing expert with 12 years' experience at leading ad agencies. Managed campaigns with budgets exceeding $10M.\n\nGoogle and Meta certified in ad management.",
                    'ku' => 'پسپۆڕی مارکێتنگی دیجیتاڵ بە ١٢ ساڵ ئەزموون.',
                ],
                'image' => 'https://i.pravatar.cc/300?img=33',
                'social_links' => ['twitter' => '#', 'linkedin' => '#', 'website' => '#'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name'  => ['ar' => 'محمد فارس', 'en' => 'Mohammed Faris', 'ku' => 'محەممەد فارس'],
                'slug'  => 'mohammed-faris',
                'title' => ['ar' => 'مدير مشاريع معتمد PMP ومستشار إداري', 'en' => 'Certified PMP Project Manager & Management Consultant', 'ku' => 'بەڕێوەبەری پرۆژە PMP و ڕاوێژکاری بەڕێوەبردن'],
                'bio'   => [
                    'ar' => "مدير مشاريع معتمد PMP بخبرة 18 عامًا في إدارة مشاريع ضخمة في قطاعات النفط والتكنولوجيا والبناء.\n\nقدم استشارات إدارية لأكثر من 50 شركة إقليمية ودولية.",
                    'en' => "PMP-certified project manager with 18 years managing large-scale projects in oil, tech, and construction.\n\nProvided management consulting to 50+ regional and international companies.",
                    'ku' => 'بەڕێوەبەری پرۆژەی PMP بە ١٨ ساڵ ئەزموون.',
                ],
                'image' => 'https://i.pravatar.cc/300?img=7',
                'social_links' => ['twitter' => '#', 'linkedin' => '#', 'website' => '#'],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($instructors as $data) {
            Instructor::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
