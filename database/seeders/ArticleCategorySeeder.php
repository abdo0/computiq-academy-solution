<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'news',
                'name' => [
                    'en' => 'News',
                    'ar' => 'الأخبار',
                    'ku' => 'هەواڵەکان',
                ],
                'description' => [
                    'en' => 'Latest news and updates',
                    'ar' => 'آخر الأخبار والتحديثات',
                    'ku' => 'دوایین هەواڵەکان و نوێکردنەوەکان',
                ],
                'sort_order' => 1,
            ],
            [
                'code' => 'success_stories',
                'name' => [
                    'en' => 'Success Stories',
                    'ar' => 'قصص النجاح',
                    'ku' => 'چیرۆکە سەرکەوتووەکان',
                ],
                'description' => [
                    'en' => 'Inspiring success stories from our campaigns',
                    'ar' => 'قصص نجاح ملهمة من حملاتنا',
                    'ku' => 'چیرۆکە سەرکەوتووە بەخشێنەرەکان لە هەوڵدەرەکانمان',
                ],
                'sort_order' => 2,
            ],
            [
                'code' => 'how_to_help',
                'name' => [
                    'en' => 'How to Help',
                    'ar' => 'كيفية المساعدة',
                    'ku' => 'چۆن یارمەتی بدەین',
                ],
                'description' => [
                    'en' => 'Ways to contribute and make a difference',
                    'ar' => 'طرق المساهمة وإحداث فرق',
                    'ku' => 'شێوازەکانی بەشداریکردن و گۆڕانکاری دروستکردن',
                ],
                'sort_order' => 3,
            ],
            [
                'code' => 'platform_updates',
                'name' => [
                    'en' => 'Platform Updates',
                    'ar' => 'تحديثات المنصة',
                    'ku' => 'نوێکردنەوەکانی پلاتفۆرم',
                ],
                'description' => [
                    'en' => 'Updates about our platform features and improvements',
                    'ar' => 'تحديثات حول ميزات المنصة وتحسيناتها',
                    'ku' => 'نوێکردنەوەکان دەربارەی تایبەتمەندییەکانی پلاتفۆرم و باشترکردنەکان',
                ],
                'sort_order' => 4,
            ],
            [
                'code' => 'community',
                'name' => [
                    'en' => 'Community',
                    'ar' => 'المجتمع',
                    'ku' => 'کۆمەڵگا',
                ],
                'description' => [
                    'en' => 'Community stories and engagement',
                    'ar' => 'قصص المجتمع والمشاركة',
                    'ku' => 'چیرۆکەکانی کۆمەڵگا و بەشداریکردن',
                ],
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            // Check if category exists by code
            if (ArticleCategory::where('code', $categoryData['code'])->exists()) {
                continue;
            }

            ArticleCategory::create([
                'code' => $categoryData['code'],
                'name' => $categoryData['name'],
                'description' => $categoryData['description'] ?? null,
                'sort_order' => $categoryData['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
