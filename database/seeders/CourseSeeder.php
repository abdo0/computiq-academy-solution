<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Instructor;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ──
        $categories = [
            [
                'name' => ['ar' => 'تطوير الذات', 'en' => 'Self Development', 'ku' => 'گەشەپێدانی خود'],
                'slug' => 'self-development',
            ],
            [
                'name' => ['ar' => 'الأعمال والإدارة', 'en' => 'Business & Management', 'ku' => 'بازرگانی و بەڕێوەبردن'],
                'slug' => 'business-management',
            ],
            [
                'name' => ['ar' => 'التكنولوجيا والبرمجة', 'en' => 'Technology & Programming', 'ku' => 'تەکنەلۆجیا و پرۆگرامینگ'],
                'slug' => 'technology',
            ],
            [
                'name' => ['ar' => 'التصميم والفنون', 'en' => 'Design & Arts', 'ku' => 'دیزاین و هونەر'],
                'slug' => 'design-arts',
            ],
            [
                'name' => ['ar' => 'التسويق الرقمي', 'en' => 'Digital Marketing', 'ku' => 'مارکێتنگی دیجیتاڵ'],
                'slug' => 'digital-marketing',
            ],
            [
                'name' => ['ar' => 'الذكاء الاصطناعي', 'en' => 'Artificial Intelligence', 'ku' => 'زیرەکی دەستکرد'],
                'slug' => 'artificial-intelligence',
            ],
            [
                'name' => ['ar' => 'اللغات', 'en' => 'Languages', 'ku' => 'زمانەکان'],
                'slug' => 'languages',
            ],
        ];

        $catMap = [];
        foreach ($categories as $i => $cat) {
            $catMap[$cat['slug']] = CourseCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['sort_order' => $i, 'is_active' => true])
            );
        }

        // Map instructor slugs for lookup
        $instructorMap = Instructor::pluck('id', 'slug')->toArray();

        // ── Courses ──
        $courses = [
            // -- Technology --
            [
                'category' => 'technology',
                'instructor_slug' => 'john-daniel',
                'title' => ['ar' => 'البرمجة بلغة جافا، من الصفر للاحتراف', 'en' => 'Java Programming: From Zero to Pro', 'ku' => 'پرۆگرامینگی جاڤا: لە سفرەوە بۆ شارەزایی'],
                'slug' => 'java-zero-to-pro',
                'short_description' => ['ar' => 'تعلم جافا من البداية حتى بناء تطبيقات حقيقية', 'en' => 'Learn Java from scratch to building real apps', 'ku' => 'فێربوونی جاڤا لە سەرەتاوە بۆ دروستکردنی ئەپلیکەیشنی ڕاستەقینە'],
                'description' => ['ar' => 'دورة شاملة في برمجة جافا تغطي جميع المفاهيم الأساسية والمتقدمة. ستتعلم البرمجة الكائنية، هياكل البيانات، الخوارزميات، وبناء تطبيقات حقيقية. الدورة مناسبة للمبتدئين والمتوسطين.', 'en' => 'A comprehensive Java programming course covering all fundamental and advanced concepts. You will learn OOP, data structures, algorithms, and build real applications. Suitable for beginners and intermediate learners.', 'ku' => 'خوێندنێکی تەواو لە پرۆگرامینگی جاڤا'],
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.8,
                'review_count' => 1250,
                'duration_hours' => 42,
                'students_count' => 8500,
                'price' => 500,
                'old_price' => 750,
                'is_live' => false,
                'is_best_seller' => false,
                'sort_order' => 0,
            ],
            [
                'category' => 'technology',
                'instructor_slug' => 'samir-hassan',
                'title' => ['ar' => 'تطوير تطبيقات الويب باستخدام ريأكت', 'en' => 'Web Development with React', 'ku' => 'دروستکردنی وێبسایت بە ڕیئاکت'],
                'slug' => 'web-dev-react',
                'short_description' => ['ar' => 'أنشئ تطبيقات ويب حديثة بأدوات المستقبل', 'en' => 'Build modern web apps with future tools', 'ku' => 'ئەپی وێب بە ئامرازەکانی داهاتوو دروست بکە'],
                'description' => ['ar' => 'تعلم React من الصفر إلى الاحتراف. ستبني تطبيقات ويب تفاعلية باستخدام React Hooks, Context API, و Redux. تشمل الدورة مشاريع عملية حقيقية ونشر التطبيقات.', 'en' => 'Learn React from zero to hero. Build interactive web apps using React Hooks, Context API, and Redux. Includes real-world projects and deployment.', 'ku' => 'فێربوونی ڕیئاکت لە سفرەوە بۆ شارەزایی'],
                'image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.9,
                'review_count' => 3400,
                'duration_hours' => 64,
                'students_count' => 18000,
                'price' => 850,
                'old_price' => 1200,
                'is_live' => false,
                'is_best_seller' => true,
                'sort_order' => 1,
            ],

            // -- Data Science / AI --
            [
                'category' => 'artificial-intelligence',
                'instructor_slug' => 'sarah-khalid',
                'title' => ['ar' => 'أساسيات علم البيانات المتقدمة', 'en' => 'Advanced Data Science Fundamentals', 'ku' => 'بنەماکانی زانستی داتا'],
                'slug' => 'data-science-fundamentals',
                'short_description' => ['ar' => 'أتقن تحليل البيانات والتعلم الآلي', 'en' => 'Master data analysis & machine learning', 'ku' => 'شارەزایی شیکردنەوەی داتا و فێربوونی ماشینی'],
                'description' => ['ar' => 'دورة متكاملة في علم البيانات تشمل Python, Pandas, NumPy, و Scikit-learn. ستتعلم التحليل الإحصائي، التعلم الآلي، وبناء نماذج تنبؤية.', 'en' => 'A comprehensive data science course covering Python, Pandas, NumPy, and Scikit-learn. Learn statistical analysis, machine learning, and build predictive models.', 'ku' => 'خوێندنێکی تەواو لە زانستی داتا'],
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.9,
                'review_count' => 870,
                'duration_hours' => 56,
                'students_count' => 5200,
                'price' => 250,
                'old_price' => 300,
                'is_live' => false,
                'is_best_seller' => false,
                'sort_order' => 0,
            ],
            [
                'category' => 'artificial-intelligence',
                'instructor_slug' => 'sarah-khalid',
                'title' => ['ar' => 'الذكاء الاصطناعي التوليدي: ChatGPT ومنافسوه', 'en' => 'Generative AI: ChatGPT & Beyond', 'ku' => 'زیرەکی دەستکردی دروستکەر: ChatGPT'],
                'slug' => 'generative-ai-chatgpt',
                'short_description' => ['ar' => 'تعلم كيف تستفيد من الذكاء الاصطناعي في عملك', 'en' => 'Learn to leverage AI in your work', 'ku' => 'فێربە چۆن لە AI بۆ کارەکەت سوود وەربگریت'],
                'description' => ['ar' => 'اكتشف عالم الذكاء الاصطناعي التوليدي. تعلم استخدام ChatGPT, Claude, Gemini وأدوات AI الأخرى لتحسين إنتاجيتك في العمل والحياة اليومية.', 'en' => 'Explore the world of generative AI. Learn to use ChatGPT, Claude, Gemini and other AI tools to boost productivity in work and daily life.', 'ku' => 'جیهانی زیرەکی دەستکردی دروستکەر بناسە'],
                'image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.7,
                'review_count' => 2300,
                'duration_hours' => 20,
                'students_count' => 15000,
                'price' => 180,
                'old_price' => 350,
                'is_live' => true,
                'is_best_seller' => true,
                'sort_order' => 1,
            ],

            // -- Marketing --
            [
                'category' => 'digital-marketing',
                'instructor_slug' => 'ahmed-mahmoud',
                'title' => ['ar' => 'دليل التسويق الرقمي الشامل 2024', 'en' => 'Complete Digital Marketing Guide 2024', 'ku' => 'ڕێنمای تەواوی مارکێتنگی دیجیتاڵ ٢٠٢٤'],
                'slug' => 'digital-marketing-guide-2024',
                'short_description' => ['ar' => 'استراتيجيات تسويقية حديثة عبر جميع المنصات', 'en' => 'Modern marketing strategies across all platforms', 'ku' => 'ستراتیجیەتی مارکێتنگی مۆدێرن لە هەموو پلاتفۆرمەکان'],
                'description' => ['ar' => 'دورة شاملة في التسويق الرقمي تغطي SEO, إعلانات Google, إعلانات Facebook, التسويق بالمحتوى, والتسويق عبر البريد الإلكتروني. تعلم بناء استراتيجيات تسويقية ناجحة.', 'en' => 'Comprehensive digital marketing course covering SEO, Google Ads, Facebook Ads, content marketing, and email marketing. Learn to build successful marketing strategies.', 'ku' => 'خوێندنێکی تەواو لە مارکێتنگی دیجیتاڵ'],
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.7,
                'review_count' => 2100,
                'duration_hours' => 38,
                'students_count' => 12000,
                'price' => 200,
                'old_price' => 400,
                'is_live' => true,
                'is_best_seller' => false,
                'sort_order' => 0,
            ],
            [
                'category' => 'digital-marketing',
                'instructor_slug' => 'ahmed-mahmoud',
                'title' => ['ar' => 'إعلانات جوجل المتقدمة: من التحسين إلى التوسع', 'en' => 'Google Ads Advanced: Optimize & Scale', 'ku' => 'ڕیکلامەکانی گووگڵ: لە باشترکردنەوە بۆ فراوانکردن'],
                'slug' => 'google-ads-advanced',
                'short_description' => ['ar' => 'حقق عائد استثمار مرتفع من الإعلانات', 'en' => 'Achieve high ROI from ads', 'ku' => 'قازانجی بەرز لە ڕیکلام بەدەست بهێنە'],
                'description' => ['ar' => 'تعلم إدارة حملات Google Ads الاحترافية. من اختيار الكلمات المفتاحية إلى تحسين معدل التحويل والتوسع في الحملات الناجحة.', 'en' => 'Learn to manage professional Google Ads campaigns. From keyword selection to conversion rate optimization and scaling successful campaigns.', 'ku' => 'فێربوونی بەڕێوەبردنی کەمپەینی Google Ads'],
                'image' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.6,
                'review_count' => 900,
                'duration_hours' => 24,
                'students_count' => 4800,
                'price' => 320,
                'old_price' => 500,
                'is_live' => false,
                'is_best_seller' => false,
                'sort_order' => 1,
            ],

            // -- Business --
            [
                'category' => 'business-management',
                'instructor_slug' => 'mohammed-faris',
                'title' => ['ar' => 'إدارة المشاريع الاحترافية PMP', 'en' => 'Professional Project Management PMP', 'ku' => 'بەڕێوەبردنی پرۆژەی پیشەیی PMP'],
                'slug' => 'pmp-project-management',
                'short_description' => ['ar' => 'استعد لامتحان PMP وقُد مشاريعك باحترافية', 'en' => 'Prepare for PMP exam & lead projects professionally', 'ku' => 'ئامادەبە بۆ تاقیکردنەوەی PMP'],
                'description' => ['ar' => 'دورة تحضيرية شاملة لامتحان PMP. تغطي جميع مجالات المعرفة العشرة وعمليات إدارة المشاريع الخمس. تشمل أكثر من 500 سؤال تدريبي.', 'en' => 'Comprehensive PMP exam prep course. Covers all 10 knowledge areas and 5 process groups. Includes 500+ practice questions.', 'ku' => 'خوێندنێکی تەواو بۆ ئامادەبوونی تاقیکردنەوەی PMP'],
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.8,
                'review_count' => 1800,
                'duration_hours' => 50,
                'students_count' => 9200,
                'price' => 600,
                'old_price' => 900,
                'is_live' => false,
                'is_best_seller' => true,
                'sort_order' => 0,
            ],

            // -- Self Dev --
            [
                'category' => 'self-development',
                'instructor_slug' => 'mohammed-faris',
                'title' => ['ar' => 'فن التواصل والقيادة الفعّالة', 'en' => 'Art of Communication & Effective Leadership', 'ku' => 'هونەری پەیوەندی و سەرکردایەتی کاریگەر'],
                'slug' => 'communication-leadership',
                'short_description' => ['ar' => 'طوّر مهاراتك القيادية وأثّر في من حولك', 'en' => 'Develop leadership skills and influence others', 'ku' => 'شارەزایەکانی سەرکردایەتیت گەشە پێ بدە'],
                'description' => ['ar' => 'تعلم فنون التواصل الفعال والقيادة الملهمة. ستكتسب مهارات الإقناع، إدارة الفرق، وبناء العلاقات المهنية الناجحة.', 'en' => 'Learn effective communication and inspiring leadership. Gain persuasion skills, team management, and successful relationship building.', 'ku' => 'فێربوونی هونەری پەیوەندی کاریگەر و سەرکردایەتی'],
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop',
                'rating' => 4.5,
                'review_count' => 600,
                'duration_hours' => 16,
                'students_count' => 3500,
                'price' => 120,
                'old_price' => 200,
                'is_live' => true,
                'is_best_seller' => false,
                'sort_order' => 0,
            ],
        ];

        foreach ($courses as $courseData) {
            $categorySlug = $courseData['category'];
            $instructorSlug = $courseData['instructor_slug'] ?? null;
            unset($courseData['category'], $courseData['instructor_slug']);

            $courseData['course_category_id'] = $catMap[$categorySlug]->id;
            $courseData['instructor_id'] = $instructorSlug ? ($instructorMap[$instructorSlug] ?? null) : null;

            // Keep backward-compatible instructor_name/image from instructor
            if ($courseData['instructor_id']) {
                $instructor = Instructor::find($courseData['instructor_id']);
                if ($instructor) {
                    $courseData['instructor_name'] = $instructor->getTranslation('name', 'ar');
                    $courseData['instructor_image'] = $instructor->image;
                }
            }

            $courseData['is_active'] = true;

            Course::updateOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );
        }
    }
}
