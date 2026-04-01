<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\CourseExamQuestion;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use Illuminate\Database\Seeder;

class CourseCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $blueprints = $this->curriculumBlueprints();

        Course::query()
            ->with('category')
            ->get()
            ->each(function (Course $course) use ($blueprints): void {
                $categorySlug = $course->category?->slug;
                $modules = $blueprints[$categorySlug] ?? $blueprints['default'];

                $this->syncCourseCurriculum($course, $modules);
            });
    }

    protected function syncCourseCurriculum(Course $course, array $modules): void
    {
        $moduleIds = [];

        foreach ($modules as $moduleIndex => $moduleData) {
            $module = CourseModule::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'sort_order' => $moduleIndex,
                ],
                [
                    'title' => $moduleData['title'],
                    'sort_order' => $moduleIndex,
                ],
            );

            $moduleIds[] = $module->id;

            $this->syncLessons($module, $moduleData['lessons']);
            $this->syncExam($module, $moduleData['exam'] ?? null);
        }

        if ($moduleIds !== []) {
            $course->modules()->whereNotIn('id', $moduleIds)->delete();
        }
    }

    protected function syncLessons(CourseModule $module, array $lessons): void
    {
        $lessonIds = [];

        foreach ($lessons as $lessonIndex => $lessonData) {
            $lesson = CourseLesson::query()->updateOrCreate(
                [
                    'course_module_id' => $module->id,
                    'sort_order' => $lessonIndex,
                ],
                [
                    'title' => $lessonData['title'],
                    'description' => $lessonData['description'],
                    'duration_minutes' => $lessonData['duration_minutes'],
                    'content_type' => 'video',
                    'video_source_type' => 'embed',
                    'video_url' => $lessonData['video_url'],
                    'is_free' => $lessonData['is_free'] ?? false,
                    'is_active' => true,
                    'sort_order' => $lessonIndex,
                ],
            );

            $lessonIds[] = $lesson->id;
        }

        if ($lessonIds !== []) {
            $module->lessons()->whereNotIn('id', $lessonIds)->delete();
        }
    }

    protected function syncExam(CourseModule $module, ?array $examData): void
    {
        if ($examData === null) {
            $module->exam()->delete();

            return;
        }

        $exam = CourseExam::query()->updateOrCreate(
            [
                'course_module_id' => $module->id,
            ],
            [
                'title' => $examData['title'],
                'pass_mark' => $examData['pass_mark'] ?? 70,
                'max_attempts' => $examData['max_attempts'] ?? 3,
                'time_limit_minutes' => $examData['time_limit_minutes'] ?? 20,
                'is_active' => true,
            ],
        );

        $questionIds = [];

        foreach ($examData['questions'] as $questionIndex => $questionData) {
            $question = $exam->questions()->updateOrCreate(
                [
                    'sort_order' => $questionIndex,
                ],
                [
                    'question' => $questionData['question'],
                    'sort_order' => $questionIndex,
                ],
            );

            $questionIds[] = $question->id;

            $this->syncQuestionOptions($question, $questionData['options'], $questionData['correct_index']);
        }

        if ($questionIds !== []) {
            $exam->questions()->whereNotIn('id', $questionIds)->delete();
        }
    }

    protected function syncQuestionOptions(CourseExamQuestion $question, array $options, int $correctIndex): void
    {
        $optionIds = [];

        foreach ($options as $optionIndex => $optionText) {
            $option = $question->options()->updateOrCreate(
                [
                    'sort_order' => $optionIndex,
                ],
                [
                    'option_text' => $optionText,
                    'is_correct' => $optionIndex === $correctIndex,
                    'sort_order' => $optionIndex,
                ],
            );

            $optionIds[] = $option->id;
        }

        if ($optionIds !== []) {
            $question->options()->whereNotIn('id', $optionIds)->delete();
        }
    }

    protected function curriculumBlueprints(): array
    {
        return [
            'technology' => $this->technologyBlueprint(),
            'artificial-intelligence' => $this->artificialIntelligenceBlueprint(),
            'digital-marketing' => $this->digitalMarketingBlueprint(),
            'business-management' => $this->businessManagementBlueprint(),
            'self-development' => $this->selfDevelopmentBlueprint(),
            'default' => $this->defaultBlueprint(),
        ];
    }

    protected function technologyBlueprint(): array
    {
        return [
            [
                'title' => ['ar' => 'أساسيات التطوير العملي', 'en' => 'Practical Development Foundations', 'ku' => 'بنەماکانی گەشەپێدانی کرداری'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'خطة التعلم ومسار الدورة', 'en' => 'Learning Roadmap and Course Path', 'ku' => 'ڕێبازی فێربوون و ڕێڕەوی کورسەکە'],
                        'description' => ['ar' => 'مقدمة عملية لما ستبنيه خلال الدورة وكيف توزّع وقتك بين الشرح والتطبيق.', 'en' => 'A practical introduction to what you will build and how to split your time between theory and implementation.', 'ku' => 'پێشەکییەکی کرداری بۆ ئەوەی دروستی دەکەیت و چۆن کاتەکەت دابەش دەکەیت.'],
                        'duration_minutes' => 18,
                        'video_url' => 'https://www.youtube.com/watch?v=eIrMbAQSU34',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'إعداد بيئة العمل وأدوات المطور', 'en' => 'Setting Up the Developer Workspace', 'ku' => 'ئامادەکردنی ژینگەی کار و ئامرازەکانی گەشەپێدەر'],
                        'description' => ['ar' => 'نضبط الأدوات الأساسية ونبني بيئة عمل مستقرة لتطبيق الأمثلة والمشاريع.', 'en' => 'Set up the core tools and build a stable environment for examples and projects.', 'ku' => 'ئامرازە سەرەکییەکان ئامادە دەکەین و ژینگەیەکی جێگیر بۆ پڕۆژەکان دروست دەکەین.'],
                        'duration_minutes' => 26,
                        'video_url' => 'https://www.youtube.com/watch?v=PkZNo7MFNFg',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'من الفكرة إلى أول تطبيق قابل للتشغيل', 'en' => 'From Idea to First Working App', 'ku' => 'لە بیرۆکەوە بۆ یەکەم ئەپی کارا'],
                        'description' => ['ar' => 'نحوّل المفاهيم الأولى إلى تطبيق صغير يرسخ الأساس قبل الانتقال للمستوى التالي.', 'en' => 'Turn the first concepts into a small working application before moving to the next level.', 'ku' => 'چەمکە سەرەتاییەکان دەگۆڕین بۆ ئەپێکی بچوکی کارا پێش گواستنەوە بۆ ئاستی داهاتوو.'],
                        'duration_minutes' => 39,
                        'video_url' => 'https://www.youtube.com/watch?v=bMknfKXIFA8',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار أساسيات التطوير', 'en' => 'Development Foundations Quiz', 'ku' => 'تاقیکردنەوەی بنەماکانی گەشەپێدان'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'ما الهدف من تجهيز بيئة تطوير مستقرة في بداية التعلم؟', 'en' => 'Why do we set up a stable development environment early?', 'ku' => 'بۆچی لە سەرەتا ژینگەیەکی جێگیری گەشەپێدان ئامادە دەکەین؟'],
                            'options' => [
                                ['ar' => 'لتقليل المشاكل أثناء التطبيق', 'en' => 'To reduce issues during implementation', 'ku' => 'بۆ کەمکردنەوەی کێشەکان لە کاتی جێبەجێکردن'],
                                ['ar' => 'لزيادة حجم الملفات فقط', 'en' => 'To increase file size only', 'ku' => 'تەنها بۆ گەورەکردنی قەبارەی فایلەکان'],
                                ['ar' => 'لإلغاء الحاجة للتدريب', 'en' => 'To eliminate the need for practice', 'ku' => 'بۆ لابردنی پێویستی ڕاهێنان'],
                                ['ar' => 'لمنع استخدام أدوات التصحيح', 'en' => 'To prevent using debugging tools', 'ku' => 'بۆ ڕێگری لە بەکارهێنانی ئامرازەکانی debug'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما أفضل طريقة لتثبيت المفاهيم الأولى؟', 'en' => 'What best reinforces early concepts?', 'ku' => 'باشترین شێواز بۆ پتەوکردنی چەمکە سەرەتاییەکان چییە؟'],
                            'options' => [
                                ['ar' => 'بناء مثال صغير قابل للتشغيل', 'en' => 'Building a small working example', 'ku' => 'دروستکردنی نموونەیەکی بچوکی کارا'],
                                ['ar' => 'حفظ العناوين فقط', 'en' => 'Memorizing headings only', 'ku' => 'تەنها لەبەرکردنی ناونیشانەکان'],
                                ['ar' => 'تجاوز التمارين العملية', 'en' => 'Skipping hands-on exercises', 'ku' => 'بازدان لە ڕاهێنانە کردارییەکان'],
                                ['ar' => 'تأجيل التجربة إلى نهاية الدورة', 'en' => 'Postponing practice until the end', 'ku' => 'دواخستنی تاقیکردنەوە بۆ کۆتایی کورسەکە'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما الذي يربط بين الشرح النظري والتطبيق العملي؟', 'en' => 'What connects theory to hands-on work?', 'ku' => 'چی پەیوەندی نێوان تیۆری و کاری کرداری دروست دەکات؟'],
                            'options' => [
                                ['ar' => 'تجربة المفهوم في مشروع أو تمرين', 'en' => 'Applying the concept in a project or exercise', 'ku' => 'جێبەجێکردنی چەمکەکە لە پڕۆژە یان ڕاهێنانێکدا'],
                                ['ar' => 'إخفاء الأخطاء وعدم مراجعتها', 'en' => 'Hiding errors and never reviewing them', 'ku' => 'شاردنەوەی هەڵەکان و نەخستنە سەر پێداچوونەوە'],
                                ['ar' => 'الاعتماد على الفيديو دون كتابة كود', 'en' => 'Relying on video without writing code', 'ku' => 'پشتبەستن بە ڤیدیۆ بەبێ نووسینی کۆد'],
                                ['ar' => 'تكرار الدرس نفسه فقط', 'en' => 'Repeating only the same lesson', 'ku' => 'تەنها دووبارەکردنەوەی هەمان وانە'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
            [
                'title' => ['ar' => 'التطوير والتسليم', 'en' => 'Build, Debug, and Ship', 'ku' => 'دروستکردن، چاکسازی و بڵاوکردنەوە'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'تنظيم الكود والعمل بشكل قابل للتوسع', 'en' => 'Organizing Code for Growth', 'ku' => 'ڕێکخستنی کۆد بۆ گەشەکردن'],
                        'description' => ['ar' => 'نتعلّم كيف نجعل المشروع أوضح وأسهل في الصيانة مع نمو الميزات.', 'en' => 'Learn how to keep the project clear and maintainable as features grow.', 'ku' => 'فێردەبین چۆن پڕۆژەکە ڕوون و ئاسان بۆ چاودێری بهێڵین لەگەڵ گەشەکردنی تایبەتمەندییەکان.'],
                        'duration_minutes' => 32,
                        'video_url' => 'https://www.youtube.com/watch?v=zOjov-2OZ0E',
                    ],
                    [
                        'title' => ['ar' => 'تصحيح الأخطاء واختبار الفرضيات', 'en' => 'Debugging and Validating Assumptions', 'ku' => 'چاککردنەوەی هەڵە و دڵنیابوون لە گریمانەکان'],
                        'description' => ['ar' => 'نستخدم أدوات المراقبة والتجربة لفهم الخطأ قبل إصلاحه بسرعة وأمان.', 'en' => 'Use debugging tools and experiments to understand failures before fixing them safely.', 'ku' => 'ئامرازەکانی debug و تاقیکردنەوە بەکار دەهێنین بۆ تێگەیشتن لە هەڵەکە پێش چاککردنەوەی.'],
                        'duration_minutes' => 28,
                        'video_url' => 'https://www.youtube.com/watch?v=kqtD5dpn9C8',
                    ],
                    [
                        'title' => ['ar' => 'إطلاق النسخة الأولى ومراجعة النتائج', 'en' => 'Shipping v1 and Reviewing Outcomes', 'ku' => 'بڵاوکردنەوەی v1 و پێداچوونەوەی ئەنجامەکان'],
                        'description' => ['ar' => 'نجهز النسخة الأولى، نراجع المخرجات، ونحدد أولويات التحسين التالية.', 'en' => 'Prepare the first release, review outcomes, and define the next improvement priorities.', 'ku' => 'یەکەم وەشانی بڵاوکراوە ئامادە دەکەین و ئەنجامەکان هەڵدەسەنگێنین.'],
                        'duration_minutes' => 34,
                        'video_url' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار البناء والإطلاق', 'en' => 'Build and Launch Quiz', 'ku' => 'تاقیکردنەوەی دروستکردن و بڵاوکردنەوە'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'لماذا نعيد تنظيم الكود أثناء نمو المشروع؟', 'en' => 'Why reorganize code as the project grows?', 'ku' => 'بۆچی کۆدەکە دووبارە ڕێکدەخەینەوە کاتێک پڕۆژەکە گەشە دەکات؟'],
                            'options' => [
                                ['ar' => 'لتسهيل الصيانة وإضافة الميزات', 'en' => 'To ease maintenance and feature growth', 'ku' => 'بۆ ئاسانکردنی چاودێری و زیادکردنی تایبەتمەندییەکان'],
                                ['ar' => 'لإخفاء بنية المشروع', 'en' => 'To hide the project structure', 'ku' => 'بۆ شاردنەوەی ڕێکخستنی پڕۆژەکە'],
                                ['ar' => 'لمنع أي تعاون', 'en' => 'To prevent collaboration', 'ku' => 'بۆ ڕێگری لە هاوکاری'],
                                ['ar' => 'لتقليل القراءة فقط', 'en' => 'To reduce reading only', 'ku' => 'تەنها بۆ کەمکردنەوەی خوێندنەوە'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما الخطوة الأولى الصحيحة عند ظهور خطأ؟', 'en' => 'What is the right first step when an error appears?', 'ku' => 'یەکەم هەنگاوی دروست لە کاتی دەرکەوتنی هەڵە چییە؟'],
                            'options' => [
                                ['ar' => 'فهم سبب الخطأ قبل التعديل', 'en' => 'Understand the cause before editing', 'ku' => 'تێگەیشتن لە هۆکاری هەڵەکە پێش دەستکاری'],
                                ['ar' => 'تغيير كل شيء دفعة واحدة', 'en' => 'Change everything at once', 'ku' => 'هەموو شتێک یەکجار بگۆڕە'],
                                ['ar' => 'تجاهل السجلات', 'en' => 'Ignore logs', 'ku' => 'لۆگەکان پشتگوێ بخە'],
                                ['ar' => 'حذف الكود مباشرة', 'en' => 'Delete code immediately', 'ku' => 'کۆدەکە یەکسەر بسڕەوە'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما فائدة مراجعة نتائج الإصدار الأول؟', 'en' => 'Why review the first release outcomes?', 'ku' => 'بۆچی ئەنجامەکانی یەکەم وەشان هەڵدەسەنگێنین؟'],
                            'options' => [
                                ['ar' => 'لتحديد التحسينات التالية بناءً على الواقع', 'en' => 'To plan next improvements based on reality', 'ku' => 'بۆ دیاریکردنی باشترکردنەوەکانی داهاتوو بە پشتبەستن بە ڕاستی'],
                                ['ar' => 'لإيقاف التطوير نهائياً', 'en' => 'To stop development permanently', 'ku' => 'بۆ وەستاندنی گەشەپێدان بۆ هەمیشە'],
                                ['ar' => 'لتجاهل ملاحظات المستخدمين', 'en' => 'To ignore user feedback', 'ku' => 'بۆ پشتگوێخستنی تێبینییەکانی بەکارهێنەران'],
                                ['ar' => 'لإزالة النسخة المنشورة', 'en' => 'To remove the released version', 'ku' => 'بۆ سڕینەوەی وەشانی بڵاوکراوە'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function artificialIntelligenceBlueprint(): array
    {
        return [
            [
                'title' => ['ar' => 'فهم البيانات والنماذج', 'en' => 'Understanding Data and Models', 'ku' => 'تێگەیشتن لە داتا و مۆدێلەکان'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'كيف تتحول البيانات إلى قرارات عملية', 'en' => 'Turning Data into Practical Decisions', 'ku' => 'چۆن داتا دەگۆڕدرێت بۆ بڕیاری کرداری'],
                        'description' => ['ar' => 'نبدأ من قراءة البيانات وفهم الأسئلة الصحيحة قبل أي نموذج أو أداة.', 'en' => 'Start with reading data and asking the right questions before any model or tool.', 'ku' => 'لە خوێندنەوەی داتا و پرسیارە دروستەکان دەست پێ دەکەین پێش هەر مۆدێلێک.'],
                        'duration_minutes' => 20,
                        'video_url' => 'https://www.youtube.com/watch?v=aircAruvnKk',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'تنظيف البيانات وبناء أول خط تحليل', 'en' => 'Cleaning Data and Building a First Pipeline', 'ku' => 'پاککردنەوەی داتا و دروستکردنی یەکەم هێڵی شیکردنەوە'],
                        'description' => ['ar' => 'نرتب البيانات الخام ونحولها إلى مدخلات جاهزة للتحليل أو التعلّم.', 'en' => 'Prepare raw datasets into reliable inputs for analysis or learning.', 'ku' => 'داتای خام ڕێکدەخەین و دەیکەین بە هاتنەژوورەی باوەڕپێکراو.'],
                        'duration_minutes' => 29,
                        'video_url' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'قراءة نتائج النموذج وتجنّب التفسير الخاطئ', 'en' => 'Reading Model Outputs without Misinterpretation', 'ku' => 'خوێندنەوەی دەرئەنجامەکانی مۆدێل بەبێ لێکدانەوەی هەڵە'],
                        'description' => ['ar' => 'نتدرّب على تفسير المخرجات وربطها بهدف العمل أو المنتج لا بالأرقام فقط.', 'en' => 'Practice interpreting outputs in the context of the business or product, not only the numbers.', 'ku' => 'ڕاهێنان دەکەین لەسەر لێکدانەوەی دەرئەنجامەکان بە پەیوەندی بە ئامانجی کار یان بەرهەم.'],
                        'duration_minutes' => 31,
                        'video_url' => 'https://www.youtube.com/watch?v=outcGtbnMuQ',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار فهم البيانات', 'en' => 'Data Understanding Quiz', 'ku' => 'تاقیکردنەوەی تێگەیشتن لە داتا'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'ما الخطوة الأولى قبل اختيار نموذج تعلم آلي؟', 'en' => 'What comes first before choosing a machine learning model?', 'ku' => 'پێش هەڵبژاردنی مۆدێلێکی فێربوونی ماشینی چی یەکەم هەنگاوە؟'],
                            'options' => [
                                ['ar' => 'فهم البيانات والسؤال المطلوب', 'en' => 'Understand the data and the target question', 'ku' => 'تێگەیشتن لە داتا و پرسیاری ئامانج'],
                                ['ar' => 'اختيار الرسم البياني فقط', 'en' => 'Choose a chart only', 'ku' => 'تەنها هەڵبژاردنی گراف'],
                                ['ar' => 'تجاهل القيم المفقودة', 'en' => 'Ignore missing values', 'ku' => 'پشتگوێخستنی نرخە ونبووەکان'],
                                ['ar' => 'نشر النموذج فوراً', 'en' => 'Deploy the model immediately', 'ku' => 'یەکسەر مۆدێلەکە بڵاو بکەرەوە'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'لماذا نحتاج إلى تنظيف البيانات؟', 'en' => 'Why do we clean data?', 'ku' => 'بۆچی پێویستە داتا پاک بکەینەوە؟'],
                            'options' => [
                                ['ar' => 'لتحسين جودة التحليل والنموذج', 'en' => 'To improve analysis and model quality', 'ku' => 'بۆ باشترکردنی کوالێتی شیکردنەوە و مۆدێل'],
                                ['ar' => 'لزيادة الضجيج', 'en' => 'To increase noise', 'ku' => 'بۆ زیادکردنی شڵەژان'],
                                ['ar' => 'لإلغاء الحاجة للتقييم', 'en' => 'To remove evaluation needs', 'ku' => 'بۆ لابردنی پێویستی هەڵسەنگاندن'],
                                ['ar' => 'لإخفاء الأعمدة المهمة', 'en' => 'To hide important columns', 'ku' => 'بۆ شاردنەوەی ستوونە گرنگەکان'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما الخطر في قراءة نتائج النموذج دون سياق؟', 'en' => 'What is the risk of reading model outputs without context?', 'ku' => 'مەترسیی خوێندنەوەی دەرئەنجامی مۆدێل بەبێ سیاق چییە؟'],
                            'options' => [
                                ['ar' => 'اتخاذ قرارات خاطئة', 'en' => 'Making wrong decisions', 'ku' => 'وەرگرتنی بڕیارە هەڵەکان'],
                                ['ar' => 'زيادة سرعة المعالجة', 'en' => 'Increasing processing speed', 'ku' => 'زیادکردنی خێرایی پرۆسێس'],
                                ['ar' => 'تقليل الحاجة للبيانات', 'en' => 'Reducing the need for data', 'ku' => 'کەمکردنەوەی پێویستی داتا'],
                                ['ar' => 'تحويل النموذج إلى ثابت', 'en' => 'Turning the model into a constant', 'ku' => 'گۆڕینی مۆدێل بۆ شتێکی جێگیر'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
            [
                'title' => ['ar' => 'استخدام الذكاء الاصطناعي في العمل اليومي', 'en' => 'Using AI in Everyday Work', 'ku' => 'بەکارهێنانی AI لە کاری ڕۆژانەدا'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'صياغة الطلبات بوضوح وفعالية', 'en' => 'Writing Clear and Effective Prompts', 'ku' => 'نووسینی داواکارییە ڕوون و کاریگەرەکان'],
                        'description' => ['ar' => 'نكتب تعليمات أوضح تساعد الأدوات الذكية على إنتاج مخرجات أدق وأكثر فائدة.', 'en' => 'Write clearer prompts that lead AI tools to more accurate and useful outputs.', 'ku' => 'فێردەبین چۆن داواکاریی ڕوون بنووسین بۆ دەرئەنجامی وردتر و بەکەڵک.'],
                        'duration_minutes' => 24,
                        'video_url' => 'https://www.youtube.com/watch?v=outcGtbnMuQ',
                    ],
                    [
                        'title' => ['ar' => 'مراجعة المخرجات والتحقق من الجودة', 'en' => 'Reviewing Outputs and Checking Quality', 'ku' => 'پشکنینی دەرئەنجامەکان و دڵنیابوون لە کوالێتی'],
                        'description' => ['ar' => 'نطوّر طريقة عملية لمراجعة النصوص والأفكار والنتائج قبل اعتمادها.', 'en' => 'Build a practical review loop for text, ideas, and generated outputs before using them.', 'ku' => 'بازنەیەکی کرداری بۆ پشکنینی نووسین و بیرۆکە و دەرئەنجامەکان دروست دەکەین.'],
                        'duration_minutes' => 22,
                        'video_url' => 'https://www.youtube.com/watch?v=aircAruvnKk',
                    ],
                    [
                        'title' => ['ar' => 'دمج أدوات الذكاء الاصطناعي في سير العمل', 'en' => 'Integrating AI into Team Workflows', 'ku' => 'تێکەڵکردنی AI لە workflow ی تیمدا'],
                        'description' => ['ar' => 'نحدد أين تضيف الأدوات الذكية قيمة حقيقية في الكتابة، البحث، والتحضير السريع.', 'en' => 'Identify where AI adds real value in writing, research, and rapid preparation.', 'ku' => 'دیاری دەکەین لە کوێ AI بەهای ڕاستەقینە زیاد دەکات لە نووسین و توێژینەوە و ئامادەکاری.'],
                        'duration_minutes' => 27,
                        'video_url' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار تطبيقات الذكاء الاصطناعي', 'en' => 'AI Workflow Quiz', 'ku' => 'تاقیکردنەوەی workflow ی AI'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'ما الذي يجعل الطلب المرسل لأداة الذكاء الاصطناعي أفضل؟', 'en' => 'What makes an AI prompt better?', 'ku' => 'چی داواکاریی AI باشتر دەکات؟'],
                            'options' => [
                                ['ar' => 'أن يكون واضحاً ومحدداً', 'en' => 'Being clear and specific', 'ku' => 'ڕوون و دیاریکراو بێت'],
                                ['ar' => 'أن يكون غامضاً جداً', 'en' => 'Being very vague', 'ku' => 'زۆر ناڕوون بێت'],
                                ['ar' => 'أن يُرسل دون هدف', 'en' => 'Being sent without a goal', 'ku' => 'بەبێ ئامانج بنێردرێت'],
                                ['ar' => 'أن يمنع أي مراجعة', 'en' => 'Preventing any review', 'ku' => 'ڕێگری لە هەر پێداچوونەوەیەک بکات'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'لماذا نراجع مخرجات الأداة الذكية قبل استخدامها؟', 'en' => 'Why review AI outputs before using them?', 'ku' => 'بۆچی پێویستە دەرئەنجامی AI پشکنین پێش بەکارهێنان؟'],
                            'options' => [
                                ['ar' => 'للتأكد من الدقة والملاءمة', 'en' => 'To verify accuracy and fit', 'ku' => 'بۆ دڵنیابوون لە دروستی و گونجاوی'],
                                ['ar' => 'لإضاعة الوقت فقط', 'en' => 'To waste time only', 'ku' => 'تەنها بۆ بەفیڕۆدانی کات'],
                                ['ar' => 'لمنع مشاركة العمل', 'en' => 'To block collaboration', 'ku' => 'بۆ ڕێگری لە هاوبەشکردنی کار'],
                                ['ar' => 'لإيقاف الأداة دائماً', 'en' => 'To always stop using AI', 'ku' => 'بۆ وەستاندنی AI هەمیشە'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'أين تضيف أدوات AI قيمة كبيرة عادة؟', 'en' => 'Where does AI often add strong value?', 'ku' => 'AI زۆرجار لە کوێ بەهای گرنگ زیاد دەکات؟'],
                            'options' => [
                                ['ar' => 'في التسريع والتحضير والبحث الأولي', 'en' => 'In acceleration, preparation, and early research', 'ku' => 'لە خێراکردن و ئامادەکاری و توێژینەوەی سەرەتاییدا'],
                                ['ar' => 'في تجاهل التحقق', 'en' => 'In ignoring verification', 'ku' => 'لە پشتگوێخستنی پشکنین'],
                                ['ar' => 'في حذف الخبرة البشرية تماماً', 'en' => 'In removing human judgment entirely', 'ku' => 'لە لابردنی هەموو هەڵسەنگاندنی مرۆیی'],
                                ['ar' => 'في منع العمل الجماعي', 'en' => 'In preventing teamwork', 'ku' => 'لە ڕێگری لە کاری تیمی'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function digitalMarketingBlueprint(): array
    {
        return [
            [
                'title' => ['ar' => 'أساسيات الرسالة والقناة', 'en' => 'Message, Audience, and Channel', 'ku' => 'بنەماکانی پەیام، ئامانج و کەناڵ'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'تحديد الجمهور وفهم العرض التسويقي', 'en' => 'Defining Audience and Offer', 'ku' => 'دیاریکردنی ئامانج و تێگەیشتن لە ئافر'],
                        'description' => ['ar' => 'نحدد لمن نتحدث وما القيمة التي نقدمها قبل أي إعلان أو حملة.', 'en' => 'Define who we speak to and what value we offer before launching any campaign.', 'ku' => 'پێش هەر کەمپەینێک دیاری دەکەین بۆ کێ قسە دەکەین و چ بەهایەک پێشکەش دەکەین.'],
                        'duration_minutes' => 18,
                        'video_url' => 'https://www.youtube.com/watch?v=V74AxCqOTvg',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'صياغة المحتوى الذي يدفع إلى الإجراء', 'en' => 'Crafting Content that Drives Action', 'ku' => 'دروستکردنی ناوەڕۆکی هاندهر بۆ کردار'],
                        'description' => ['ar' => 'نتعلم كيف نكتب رسالة واضحة تقود إلى خطوة تالية قابلة للقياس.', 'en' => 'Learn how to write a clear message that leads to a measurable next step.', 'ku' => 'فێردەبین چۆن پەیامێکی ڕوون بنووسین کە بگات بە هەنگاوی داهاتوی پێوانەکراو.'],
                        'duration_minutes' => 21,
                        'video_url' => 'https://www.youtube.com/watch?v=qp0HIF3SfI4',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'اختيار القناة المناسبة للحملة', 'en' => 'Choosing the Right Channel for the Campaign', 'ku' => 'هەڵبژاردنی کەناڵی دروست بۆ کەمپەین'],
                        'description' => ['ar' => 'نوازن بين البحث، المحتوى، الشبكات الاجتماعية، والبريد بحسب هدف الحملة.', 'en' => 'Balance search, content, social, and email based on campaign goals.', 'ku' => 'هاوسەنگی نێوان search و content و social و email دەکەین بە پێی ئامانجی کەمپەین.'],
                        'duration_minutes' => 25,
                        'video_url' => 'https://www.youtube.com/watch?v=H14bBuluwB8',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار أساسيات التسويق', 'en' => 'Marketing Foundations Quiz', 'ku' => 'تاقیکردنەوەی بنەماکانی مارکێتینگ'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'ما الذي يأتي أولاً قبل بناء الحملة؟', 'en' => 'What comes first before building a campaign?', 'ku' => 'پێش دروستکردنی کەمپەین چی یەکەم دێت؟'],
                            'options' => [
                                ['ar' => 'فهم الجمهور والعرض', 'en' => 'Understanding audience and offer', 'ku' => 'تێگەیشتن لە ئامانج و ئافر'],
                                ['ar' => 'تشغيل الإعلان مباشرة', 'en' => 'Launching ads immediately', 'ku' => 'یەکسەر ڕیکلامەکە بخەرە کار'],
                                ['ar' => 'اختيار اللون فقط', 'en' => 'Choosing a color only', 'ku' => 'تەنها هەڵبژاردنی ڕەنگ'],
                                ['ar' => 'إلغاء القياس', 'en' => 'Removing measurement', 'ku' => 'لابردنی پێوانەکردن'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما الذي يميز الرسالة التسويقية الجيدة؟', 'en' => 'What characterizes a good marketing message?', 'ku' => 'چی پەیامی باشی مارکێتینگ دیاری دەکات؟'],
                            'options' => [
                                ['ar' => 'الوضوح وربطها بالفعل المطلوب', 'en' => 'Clarity and connection to the desired action', 'ku' => 'ڕوونی و پەیوەستکردنی بە کردارە خوازراوەکە'],
                                ['ar' => 'الإبهام التام', 'en' => 'Total ambiguity', 'ku' => 'تەواو ناڕوونی'],
                                ['ar' => 'عدم وجود قيمة', 'en' => 'No clear value', 'ku' => 'بێ بەهای ڕوون'],
                                ['ar' => 'إهمال الجمهور', 'en' => 'Ignoring the audience', 'ku' => 'پشتگوێخستنی ئامانج'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'كيف نختار القناة التسويقية المناسبة؟', 'en' => 'How do we choose the right marketing channel?', 'ku' => 'چۆن کەناڵی دروستی مارکێتینگ هەڵدەبژێرین؟'],
                            'options' => [
                                ['ar' => 'بحسب الهدف والجمهور والسياق', 'en' => 'Based on goal, audience, and context', 'ku' => 'بەپێی ئامانج و ئامانجدار و سیاق'],
                                ['ar' => 'عشوائياً دائماً', 'en' => 'Always randomly', 'ku' => 'هەمیشە بە هەڕەمەکی'],
                                ['ar' => 'بحسب المنصة الأشهر فقط', 'en' => 'Only by the most popular platform', 'ku' => 'تەنها بەپێی ناودارترین پلاتفۆرم'],
                                ['ar' => 'من دون مراجعة النتائج', 'en' => 'Without reviewing results', 'ku' => 'بەبێ پێداچوونەوەی ئەنجامەکان'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function businessManagementBlueprint(): array
    {
        return [
            [
                'title' => ['ar' => 'تأسيس المشروع والخطة', 'en' => 'Project Setup and Planning', 'ku' => 'دامەزراندنی پرۆژە و پلاندانان'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'تحديد الهدف والنطاق بوضوح', 'en' => 'Defining Goal and Scope Clearly', 'ku' => 'دیاریکردنی ئامانج و سنوور بە ڕوونی'],
                        'description' => ['ar' => 'نحدد ما الذي سنديره بالضبط وما الذي يبقى خارج نطاق التنفيذ.', 'en' => 'Clarify exactly what the initiative covers and what stays out of scope.', 'ku' => 'دیاری دەکەین چی بەڕێوە دەبەین و چی لە سنووری جێبەجێکردن دەرەوەیە.'],
                        'duration_minutes' => 18,
                        'video_url' => 'https://www.youtube.com/watch?v=qp0HIF3SfI4',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'تقسيم العمل وتوزيع المسؤوليات', 'en' => 'Breaking Work Down and Assigning Ownership', 'ku' => 'دابەشکردنی کار و دیاریکردنی بەرپرسیارێتی'],
                        'description' => ['ar' => 'نبني خطة عملية تقسم العمل إلى أجزاء واضحة قابلة للمتابعة.', 'en' => 'Build an actionable plan that splits work into clear, trackable pieces.', 'ku' => 'پلانی کرداری دروست دەکەین کە کارەکە دابەش بکات بۆ بەشە ڕوونەکان.'],
                        'duration_minutes' => 22,
                        'video_url' => 'https://www.youtube.com/watch?v=H14bBuluwB8',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'إدارة المخاطر قبل أن تصبح أزمات', 'en' => 'Managing Risks before They Escalate', 'ku' => 'بەڕێوەبردنی مەترسییەکان پێش ئەوەی ببن بە قەیران'],
                        'description' => ['ar' => 'نتعلم كيف نكتشف المؤشرات المبكرة ونجهّز بدائل عملية قبل التأخير.', 'en' => 'Learn to spot early signals and prepare practical alternatives before delays hit.', 'ku' => 'فێردەبین چۆن ئاماژە سەرەتاییەکان بناسین و هەڵبژاردەی کرداری ئامادە بکەین.'],
                        'duration_minutes' => 27,
                        'video_url' => 'https://www.youtube.com/watch?v=V74AxCqOTvg',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار التخطيط وإدارة النطاق', 'en' => 'Planning and Scope Quiz', 'ku' => 'تاقیکردنەوەی پلاندانان و سنووری کار'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'لماذا نحدد النطاق من البداية؟', 'en' => 'Why define scope from the start?', 'ku' => 'بۆچی لە سەرەتا سنوور دیاری دەکەین؟'],
                            'options' => [
                                ['ar' => 'لتوضيح ما سيدخل وما لن يدخل في التنفيذ', 'en' => 'To clarify what is and is not included', 'ku' => 'بۆ ڕوونکردنەوەی ئەوەی چی دەچێتە ناو جێبەجێکردن و چی نا'],
                                ['ar' => 'لإضافة التباس أكبر', 'en' => 'To add confusion', 'ku' => 'بۆ زیادکردنی ئاڵۆزی'],
                                ['ar' => 'لمنع وجود أولويات', 'en' => 'To prevent prioritization', 'ku' => 'بۆ ڕێگری لە هەبوونی پێشەنگکاری'],
                                ['ar' => 'لإلغاء المتابعة', 'en' => 'To cancel tracking', 'ku' => 'بۆ هەڵوەشاندنەوەی شوێنکەوتن'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما فائدة توزيع المسؤوليات بوضوح؟', 'en' => 'What is the benefit of clear ownership?', 'ku' => 'سوودی دیاریکردنی بەرپرسیارێتی بە ڕوونی چییە؟'],
                            'options' => [
                                ['ar' => 'تسهيل التنفيذ والمساءلة', 'en' => 'Easier execution and accountability', 'ku' => 'ئاسانکردنی جێبەجێکردن و بەرپرسیارێتی'],
                                ['ar' => 'زيادة التضارب', 'en' => 'More conflict', 'ku' => 'زیادکردنی ناکۆکی'],
                                ['ar' => 'إخفاء العمل', 'en' => 'Hiding work', 'ku' => 'شاردنەوەی کار'],
                                ['ar' => 'تعطيل التسليم', 'en' => 'Blocking delivery', 'ku' => 'ڕاگرتنی تەسلیمکردن'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'كيف نتعامل مع المخاطر بفعالية؟', 'en' => 'How do we handle risks effectively?', 'ku' => 'چۆن بە شێوەیەکی کاریگەر لەگەڵ مەترسییەکان مامەڵە دەکەین؟'],
                            'options' => [
                                ['ar' => 'برصدها مبكراً ووضع بدائل', 'en' => 'By spotting them early and preparing alternatives', 'ku' => 'بە ناسینیان زوو و ئامادەکردنی هەڵبژاردە'],
                                ['ar' => 'بتجاهلها حتى تتفاقم', 'en' => 'By ignoring them until they grow', 'ku' => 'بە پشتگوێخستنیان تا گەورە ببن'],
                                ['ar' => 'بحذف الخطة', 'en' => 'By deleting the plan', 'ku' => 'بە سڕینەوەی پلان'],
                                ['ar' => 'برفع التكاليف دائماً', 'en' => 'By always increasing costs', 'ku' => 'بە زیادکردنی تێچووەکان هەمیشە'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function selfDevelopmentBlueprint(): array
    {
        return [
            [
                'title' => ['ar' => 'الوعي الذاتي والتواصل', 'en' => 'Self-awareness and Communication', 'ku' => 'خۆئاگایی و پەیوەندی'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'فهم أسلوبك في التواصل', 'en' => 'Understanding Your Communication Style', 'ku' => 'تێگەیشتن لە شێوازی پەیوەندیکردنی خۆت'],
                        'description' => ['ar' => 'نتعرف على نقاط القوة والعوائق في طريقة التعبير والاستماع لديك.', 'en' => 'Identify strengths and blind spots in how you express and listen.', 'ku' => 'خاڵە هێزدار و کۆسپەکانی شێوازی دەربڕین و گوێگرتن بناسە.'],
                        'duration_minutes' => 17,
                        'video_url' => 'https://www.youtube.com/watch?v=Ks-_Mh1QhMc',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'الإنصات الفعّال وبناء الثقة', 'en' => 'Active Listening and Building Trust', 'ku' => 'گوێگرتنی کاریگەر و دروستکردنی متمانە'],
                        'description' => ['ar' => 'نتدرّب على الإصغاء الذي يفتح الحوار ويقوي الفهم بين الأطراف.', 'en' => 'Practice listening habits that open dialogue and strengthen understanding.', 'ku' => 'لەسەر گوێگرتنیەک ڕاهێنان دەکەین کە گفتوگۆ بکاتەوە و تێگەیشتن پتەو بکات.'],
                        'duration_minutes' => 19,
                        'video_url' => 'https://www.youtube.com/watch?v=qp0HIF3SfI4',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'تقديم الرسالة بثقة ووضوح', 'en' => 'Delivering Your Message with Confidence', 'ku' => 'گەیاندنی پەیامەکەت بە متمانە و ڕوونی'],
                        'description' => ['ar' => 'نحوّل الفكرة الجيدة إلى رسالة تصل بوضوح وتدفع إلى الاستجابة.', 'en' => 'Turn a good idea into a message that lands clearly and earns a response.', 'ku' => 'بیرۆکەی باش دەگۆڕین بۆ پەیامێک کە بە ڕوونی بگات و وەڵام دروست بکات.'],
                        'duration_minutes' => 23,
                        'video_url' => 'https://www.youtube.com/watch?v=H14bBuluwB8',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار مهارات التواصل', 'en' => 'Communication Skills Quiz', 'ku' => 'تاقیکردنەوەی تواناهای پەیوەندی'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'ما أول خطوة لتحسين أسلوب التواصل؟', 'en' => 'What is the first step to improving communication?', 'ku' => 'یەکەم هەنگاو بۆ باشترکردنی پەیوەندی چییە؟'],
                            'options' => [
                                ['ar' => 'معرفة أسلوبك الحالي', 'en' => 'Knowing your current style', 'ku' => 'زانیاریی لەسەر شێوازی ئێستای خۆت'],
                                ['ar' => 'التحدث أسرع دائماً', 'en' => 'Always speaking faster', 'ku' => 'هەمیشە خێراتر قسەکردن'],
                                ['ar' => 'تجاهل ردود الفعل', 'en' => 'Ignoring feedback', 'ku' => 'پشتگوێخستنی وەڵامدانەوە'],
                                ['ar' => 'تقليل الإصغاء', 'en' => 'Listening less', 'ku' => 'کەمکردنەوەی گوێگرتن'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'ما الذي يبني الثقة أثناء الحوار؟', 'en' => 'What builds trust in conversation?', 'ku' => 'چی لە کاتی گفتوگۆ متمانە دروست دەکات؟'],
                            'options' => [
                                ['ar' => 'الإنصات الفعّال والفهم', 'en' => 'Active listening and understanding', 'ku' => 'گوێگرتنی کاریگەر و تێگەیشتن'],
                                ['ar' => 'المقاطعة المستمرة', 'en' => 'Constant interruption', 'ku' => 'پچڕاندنی بەردەوام'],
                                ['ar' => 'الغموض في الرد', 'en' => 'Being unclear in response', 'ku' => 'ناڕوونی لە وەڵامدا'],
                                ['ar' => 'عدم الاهتمام بالسياق', 'en' => 'Ignoring context', 'ku' => 'پشتگوێخستنی سیاق'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'كيف تصل الرسالة بشكل أفضل؟', 'en' => 'How does a message land best?', 'ku' => 'پەیامەکە چۆن باشتر دەگات؟'],
                            'options' => [
                                ['ar' => 'عندما تكون واضحة ومقصودة', 'en' => 'When it is clear and intentional', 'ku' => 'کاتێک ڕوون و بە مەبەست بێت'],
                                ['ar' => 'عندما تكون مشتتة', 'en' => 'When it is scattered', 'ku' => 'کاتێک پەرتەوازە بێت'],
                                ['ar' => 'عندما تتغير كل دقيقة', 'en' => 'When it changes every minute', 'ku' => 'کاتێک هەر خولەکێک بگۆڕێت'],
                                ['ar' => 'عندما تُلقى دون انتباه', 'en' => 'When delivered without attention', 'ku' => 'کاتێک بەبێ سەرنج بدرێت'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function defaultBlueprint(): array
    {
        return [
            [
                'title' => ['ar' => 'الأساسيات العملية', 'en' => 'Practical Foundations', 'ku' => 'بنەما کردارییەکان'],
                'lessons' => [
                    [
                        'title' => ['ar' => 'كيف تتعلم هذا المسار بفعالية', 'en' => 'How to Learn This Track Effectively', 'ku' => 'چۆن ئەم ڕێڕەوە بە کاریگەری فێربیت'],
                        'description' => ['ar' => 'نضع إطاراً بسيطاً للاستفادة من الدروس والتطبيق والمتابعة.', 'en' => 'Set a simple framework for learning from lessons, practice, and follow-up.', 'ku' => 'چوارچێوەیەکی سادە بۆ سوود وەرگرتن لە وانە و ڕاهێنان و شوێنکەوتن دادەنێین.'],
                        'duration_minutes' => 15,
                        'video_url' => 'https://www.youtube.com/watch?v=H14bBuluwB8',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'تنظيم الجهد والوقت خلال التطبيق', 'en' => 'Structuring Time and Effort during Practice', 'ku' => 'ڕێکخستنی کات و هەوڵ لە کاتی ڕاهێناندا'],
                        'description' => ['ar' => 'نوازن بين الفهم والتنفيذ حتى يستمر التقدم بشكل واقعي.', 'en' => 'Balance understanding and execution so progress stays realistic and steady.', 'ku' => 'هاوسەنگی نێوان تێگەیشتن و جێبەجێکردن دەکەین بۆ ئەوەی پێشکەوتن بەردەوام بێت.'],
                        'duration_minutes' => 18,
                        'video_url' => 'https://www.youtube.com/watch?v=V74AxCqOTvg',
                        'is_free' => true,
                    ],
                    [
                        'title' => ['ar' => 'تحويل المعرفة إلى ممارسة واضحة', 'en' => 'Turning Knowledge into Clear Practice', 'ku' => 'گۆڕینی زانیاری بۆ ڕاهێنانی ڕوون'],
                        'description' => ['ar' => 'نربط كل فكرة بخطوة عملية قابلة للتطبيق والمراجعة.', 'en' => 'Tie each idea to an actionable step that can be practiced and reviewed.', 'ku' => 'هەر بیرۆکەیەک بە هەنگاوێکی کرداری و پێداچوونەوە پەیوەست دەکەین.'],
                        'duration_minutes' => 22,
                        'video_url' => 'https://www.youtube.com/watch?v=Ks-_Mh1QhMc',
                    ],
                ],
                'exam' => [
                    'title' => ['ar' => 'اختبار الأساسيات', 'en' => 'Foundations Quiz', 'ku' => 'تاقیکردنەوەی بنەماکان'],
                    'questions' => [
                        [
                            'question' => ['ar' => 'ما الذي يجعل التعلّم أكثر فاعلية؟', 'en' => 'What makes learning more effective?', 'ku' => 'چی فێربوون کاریگەرتر دەکات؟'],
                            'options' => [
                                ['ar' => 'ربط الدرس بالتطبيق', 'en' => 'Connecting lessons to practice', 'ku' => 'پەیوەستکردنی وانە بە ڕاهێنان'],
                                ['ar' => 'تأجيل كل تطبيق', 'en' => 'Postponing all practice', 'ku' => 'دواخستنی هەموو ڕاهێنان'],
                                ['ar' => 'تجاهل الوقت', 'en' => 'Ignoring time', 'ku' => 'پشتگوێخستنی کات'],
                                ['ar' => 'الاعتماد على الحفظ فقط', 'en' => 'Relying on memorization only', 'ku' => 'تەنها پشتبەستن بە لەبەرکردن'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'لماذا نوزّع الوقت بوعي؟', 'en' => 'Why manage time intentionally?', 'ku' => 'بۆچی کات بە هۆشیاری بەڕێوە دەبەین؟'],
                            'options' => [
                                ['ar' => 'للحفاظ على تقدّم مستمر', 'en' => 'To maintain steady progress', 'ku' => 'بۆ پاراستنی پێشکەوتنی بەردەوام'],
                                ['ar' => 'لإرباك الخطة', 'en' => 'To confuse the plan', 'ku' => 'بۆ شێواوکردنی پلان'],
                                ['ar' => 'لتأخير الإنجاز', 'en' => 'To delay delivery', 'ku' => 'بۆ دواخستنی تەواوبوون'],
                                ['ar' => 'لتجاهل الأولويات', 'en' => 'To ignore priorities', 'ku' => 'بۆ پشتگوێخستنی پێشەنگکاری'],
                            ],
                            'correct_index' => 0,
                        ],
                        [
                            'question' => ['ar' => 'كيف نستفيد من أي معرفة جديدة؟', 'en' => 'How do we benefit from new knowledge?', 'ku' => 'چۆن لە هەر زانیارییەکی نوێ سوود وەردەگرین؟'],
                            'options' => [
                                ['ar' => 'بتحويلها إلى خطوة قابلة للتطبيق', 'en' => 'By turning it into an actionable step', 'ku' => 'بە گۆڕینی بۆ هەنگاوێکی جێبەجێکراو'],
                                ['ar' => 'بتركها نظرية فقط', 'en' => 'By keeping it purely theoretical', 'ku' => 'بە هێشتنەوەی تەنها تیۆری'],
                                ['ar' => 'بنسيانها سريعاً', 'en' => 'By forgetting it quickly', 'ku' => 'بە لەبیرچوونی زوو'],
                                ['ar' => 'بمنع المراجعة', 'en' => 'By avoiding review', 'ku' => 'بە ڕێگری لە پێداچوونەوە'],
                            ],
                            'correct_index' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }
}
