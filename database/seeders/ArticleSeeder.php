<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ArticleCategory::all();
        $users = User::all();

        if ($categories->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Article categories or users not found. Please run ArticleCategorySeeder and UserSeeder first.');

            return;
        }

        $articles = [
            [
                'slug' => 'welcome-to-nakhwaa-platform',
                'title' => [
                    'en' => 'Welcome to Nakhwaa Platform',
                    'ar' => 'مرحباً بكم في منصة نخوة',
                    'ku' => 'بەخێربێن بۆ پلاتفۆرمی نەخوە',
                ],
                'excerpt' => [
                    'en' => 'Discover how Nakhwaa is transforming charitable giving in Iraq',
                    'ar' => 'اكتشف كيف تحول نخوة العطاء الخيري في العراق',
                    'ku' => 'بزانە چۆن نەخوە بەخشینکردنی خێرەکان لە عێراق دەگۆڕێت',
                ],
                'content' => [
                    'en' => '<p>Nakhwaa is a revolutionary platform that connects donors with those in need across Iraq. Our mission is to make charitable giving transparent, secure, and impactful.</p><p>Through our platform, you can support various campaigns including medical treatment, education, humanitarian aid, and community development projects.</p>',
                    'ar' => '<p>نخوة هي منصة ثورية تربط المتبرعين مع المحتاجين في جميع أنحاء العراق. مهمتنا هي جعل العطاء الخيري شفافاً وآمناً ومؤثراً.</p><p>من خلال منصتنا، يمكنك دعم حملات متنوعة تشمل العلاج الطبي والتعليم والمساعدات الإنسانية ومشاريع تنمية المجتمع.</p>',
                    'ku' => '<p>نەخوە پلاتفۆرمێکی شۆڕشگێڕانەیە کە بەخشەرەکان لەگەڵ ئەوانەی پێویستیان هەیە لە سەرانسەری عێراق دەبەستێتەوە. ئامانجمان ئەوەیە بەخشینکردنی خێرەکان شەفاف، سەلامەت و کاریگەر بکەین.</p><p>لە ڕێگەی پلاتفۆرمەکەمان، دەتوانیت پشتگیری هەوڵدەرە جۆراوجۆرەکان بکەیت لەوانە چارەسەری پزیشکی، پەروەردە، یارمەتی مرۆیی و پرۆژەکانی گەشەپێدانی کۆمەڵگا.</p>',
                ],
                'category_code' => 'platform_updates',
                'is_published' => true,
                'published_at' => now()->subDays(30),
                'image_url' => 'https://images.unsplash.com/photo-1531206715517-5c0ba140bef2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=800&q=80',
            ],
            [
                'slug' => 'how-to-create-a-campaign',
                'title' => [
                    'en' => 'How to Create a Campaign',
                    'ar' => 'كيفية إنشاء حملة',
                    'ku' => 'چۆن هەوڵدەرێک دروست بکەین',
                ],
                'excerpt' => [
                    'en' => 'Step-by-step guide to creating your first fundraising campaign',
                    'ar' => 'دليل خطوة بخطوة لإنشاء أول حملة تمويل جماعي',
                    'ku' => 'ڕێنمایی هەنگاو بە هەنگاو بۆ دروستکردنی یەکەم هەوڵدەری کۆکردنەوەی پارە',
                ],
                'content' => [
                    'en' => '<p>Creating a campaign on Nakhwaa is simple and straightforward. Follow these steps:</p><ol><li>Register your organization</li><li>Verify your account</li><li>Create your campaign with clear goals</li><li>Add compelling images and descriptions</li><li>Submit for approval</li></ol>',
                    'ar' => '<p>إنشاء حملة على نخوة بسيط ومباشر. اتبع هذه الخطوات:</p><ol><li>سجل منظمتك</li><li>تحقق من حسابك</li><li>أنشئ حملتك بأهداف واضحة</li><li>أضف صوراً ووصفاً مقنعاً</li><li>قدم للموافقة</li></ol>',
                    'ku' => '<p>دروستکردنی هەوڵدەر لەسەر نەخوە ساکار و ڕاستەوخۆیە. ئەم هەنگاوانە بەدواداچێ:</p><ol><li>دامەزراوەکەت تۆمار بکە</li><li>هەژمارەکەت پشتڕاست بکەرەوە</li><li>هەوڵدەرەکەت بە ئامانجی ڕوون دروست بکە</li><li>وێنە و وەسفی بەهێز زیاد بکە</li><li>بۆ پەسندکردن ناردە</li></ol>',
                ],
                'category_code' => 'how_to_help',
                'is_published' => true,
                'published_at' => now()->subDays(25),
                'image_url' => 'https://images.unsplash.com/photo-1555421689-491a97ff2040?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=800&q=80',
            ],
            [
                'slug' => 'success-story-medical-treatment',
                'title' => [
                    'en' => 'Success Story: Medical Treatment Campaign',
                    'ar' => 'قصة نجاح: حملة العلاج الطبي',
                    'ku' => 'چیرۆکی سەرکەوتن: هەوڵدەری چارەسەری پزیشکی',
                ],
                'excerpt' => [
                    'en' => 'How a medical campaign helped save lives in Baghdad',
                    'ar' => 'كيف ساعدت حملة طبية في إنقاذ الأرواح في بغداد',
                    'ku' => 'چۆن هەوڵدەرێکی پزیشکی یارمەتی دا لە پاراستنی ژیان لە بەغدا',
                ],
                'content' => [
                    'en' => '<p>Last month, we successfully raised funds for emergency medical treatment in Baghdad. Thanks to generous donors, we were able to help 15 families receive critical medical care.</p><p>The campaign exceeded its target by 120%, allowing us to support additional families in need.</p>',
                    'ar' => '<p>الشهر الماضي، نجحنا في جمع الأموال للعلاج الطبي الطارئ في بغداد. بفضل المتبرعين الكرماء، تمكنا من مساعدة 15 عائلة في الحصول على رعاية طبية حرجة.</p><p>تجاوزت الحملة هدفها بنسبة 120%، مما سمح لنا بدعم عائلات إضافية محتاجة.</p>',
                    'ku' => '<p>مانگی پێشوو، بە سەرکەوتوویی پارەمان کۆکردەوە بۆ چارەسەری پزیشکی فریاکەوتن لە بەغدا. بەهۆی بەخشەرە بەخشەکانەوە، توانیمان یارمەتی 15 خێزان بدەین چاودێری پزیشکی گرنگ وەربگرن.</p><p>هەوڵدەرەکە ئامانجەکەی بە 120% تێپەڕاند، ئەمەش ڕێگەمان پێدا پشتگیری خێزانە زیاترەکان بکەین کە پێویستیان هەیە.</p>',
                ],
                'category_code' => 'success_stories',
                'is_published' => true,
                'published_at' => now()->subDays(20),
                'image_url' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=800&q=80',
            ],
            [
                'slug' => 'new-payment-gateways-added',
                'title' => [
                    'en' => 'New Payment Gateways Added',
                    'ar' => 'إضافة بوابات دفع جديدة',
                    'ku' => 'دەروازەی پارەدانێکی نوێ زیادکرا',
                ],
                'excerpt' => [
                    'en' => 'We\'ve added more payment options for your convenience',
                    'ar' => 'أضفنا المزيد من خيارات الدفع لراحتك',
                    'ku' => 'هەڵبژاردەی پارەدانی زیاترمان زیادکرد بۆ ئاسانکاری',
                ],
                'content' => [
                    'en' => '<p>We are excited to announce the addition of new payment gateways including ZainCash, FastPay, and Nasaq. This makes it easier for donors to contribute using their preferred payment method.</p><p>All transactions are secure and processed instantly.</p>',
                    'ar' => '<p>يسعدنا الإعلان عن إضافة بوابات دفع جديدة بما في ذلك زين كاش وفاست باي وناسق. هذا يجعل من السهل على المتبرعين المساهمة باستخدام طريقة الدفع المفضلة لديهم.</p><p>جميع المعاملات آمنة ومعالجة على الفور.</p>',
                    'ku' => '<p>دڵخۆشین بە ئاشکراکردنی زیادکردنی دەروازەی پارەدانی نوێ لەوانە زەین کاش، فاست پەی و ناساق. ئەمە ئاسانتر دەکات بۆ بەخشەران بەشداری بکەن بە بەکارهێنانی شێوازی پارەدانی بەلایان.</p><p>هەموو کاروبارەکان سەلامەتن و بە خێرایی جێبەجێ دەکرێن.</p>',
                ],
                'category_code' => 'platform_updates',
                'is_published' => true,
                'published_at' => now()->subDays(15),
                'image_url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=800&q=80',
            ],
            [
                'slug' => 'community-impact-2024',
                'title' => [
                    'en' => 'Community Impact Report 2024',
                    'ar' => 'تقرير تأثير المجتمع 2024',
                    'ku' => 'ڕاپۆرتی کاریگەری کۆمەڵگا 2024',
                ],
                'excerpt' => [
                    'en' => 'A look at how our community has made a difference this year',
                    'ar' => 'نظرة على كيفية إحداث مجتمعنا فرقاً هذا العام',
                    'ku' => 'سەیرکردنێک بۆ چۆن کۆمەڵگاکەمان ساڵی ئەمسا گۆڕانکاری دروستکرد',
                ],
                'content' => [
                    'en' => '<p>In 2024, our community has raised over 500 million IQD across 200+ campaigns. Together, we\'ve helped thousands of families access medical care, education, and essential services.</p><p>Thank you to all our donors and supporters for making this possible.</p>',
                    'ar' => '<p>في عام 2024، جمع مجتمعنا أكثر من 500 مليون دينار عراقي عبر أكثر من 200 حملة. معاً، ساعدنا آلاف العائلات في الوصول إلى الرعاية الطبية والتعليم والخدمات الأساسية.</p><p>شكراً لجميع متبرعينا وداعمينا على جعل هذا ممكناً.</p>',
                    'ku' => '<p>لە 2024، کۆمەڵگاکەمان زیاتر لە 500 ملیۆن دیناری عێراقی کۆکردەوە لە زیاتر لە 200 هەوڵدەردا. پێکەوە، یارمەتی هەزاران خێزانمان دا بگەن بە چاودێری پزیشکی، پەروەردە و خزمەتگوزارییە بنەڕەتییەکان.</p><p>سوپاس بۆ هەموو بەخشەر و پشتگیرەکانمان بۆ ئەوەی ئەمە دەستکەوت.</p>',
                ],
                'category_code' => 'community',
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'image_url' => 'https://images.unsplash.com/photo-1528605105345-5344ea20e269?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=800&q=80',
            ],
        ];

        foreach ($articles as $articleData) {
            $this->command->info("Processing article: {$articleData['title']['en']}");

            // Check if article exists by slug
            $article = Article::where('slug', $articleData['slug'])->first();

            if (! $article) {
                $category = $categories->firstWhere('code', $articleData['category_code']);
                $author = $users->random();

                $article = Article::create([
                    'slug' => $articleData['slug'],
                    'title' => $articleData['title'],
                    'excerpt' => $articleData['excerpt'] ?? null,
                    'content' => $articleData['content'],
                    'article_category_id' => $category?->id,
                    'author_id' => $author->id,
                    'is_published' => $articleData['is_published'] ?? false,
                    'published_at' => $articleData['published_at'] ?? null,
                    'sort_order' => 0,
                    'meta_title' => $articleData['title']['en'] ?? null,
                    'meta_description' => $articleData['excerpt']['en'] ?? null,
                ]);
            }

            // Handle Image Download
            if (isset($articleData['image_url']) && $article->getMedia('featured_image')->count() === 0 && $article->getMedia('images')->count() === 0) {
                $this->addImageToArticle($article, $articleData['image_url']);
            }
        }
        
        $this->command->info('🎉 Article media seeding completed!');
    }

    private function addImageToArticle(Article $article, string $imageUrl): void
    {
        try {
            // First attempt to download the provided image
            $response = Http::withOptions(['verify' => false])->timeout(60)->get($imageUrl);

            // If it fails, use a fallback placeholder
            if (! $response->successful()) {
                $this->command->warn('  ⚠️ Failed to download primary image for article, trying fallback...');
                $fallbackUrl = 'https://placehold.co/1200x800/10b981/ffffff.png?text='.urlencode($article->getTranslation('title', 'en', false) ?? 'Article');
                $response = Http::withOptions(['verify' => false])->timeout(60)->get($fallbackUrl);
            }

            if ($response->successful()) {
                $extension = str_contains($response->header('Content-Type') ?? '', 'png') ? '.png' : '.jpg';
                $tempPath = tempnam(sys_get_temp_dir(), 'article_cover_').$extension;

                file_put_contents($tempPath, $response->body());

                if (file_exists($tempPath)) {
                    $article->addMedia($tempPath)
                        ->usingName($article->getTranslation('title', 'en', false) ?? 'Article Image')
                        ->usingFileName("article_{$article->id}_cover_".time().'_'.rand(1000, 9999).$extension)
                        ->toMediaCollection('featured_image'); // Assuming 'featured_image' is the collection used for articles in dataService

                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    $this->command->info('  📷 Added image to article.');
                }
            }
        } catch (\Exception $e) {
            $this->command->error("  ❌ Failed to process image for article: {$e->getMessage()}");
        }
    }
}
