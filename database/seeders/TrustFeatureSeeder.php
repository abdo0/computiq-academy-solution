<?php

namespace Database\Seeders;

use App\Models\TrustFeature;
use Illuminate\Database\Seeder;

class TrustFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'title' => ['en' => 'Dedicated Support', 'ar' => 'دعم مخصص', 'ku' => 'پاڵپشتی تایبەت'],
                'description' => [
                    'en' => 'Our team is available round the clock to help you and answer your inquiries.',
                    'ar' => 'فريقنا متواجد على مدار الساعة لمساعدتك والإجابة على استفساراتك.',
                    'ku' => 'تیمەکەمان بەردەوام بەردەستە بۆ یارمەتیدانت و وەڵامدانەوەی پرسیارەکانت.'
                ],
                'icon' => 'heart',
                'sort_order' => 1,
            ],
            [
                'title' => ['en' => 'Platform Fee 0%', 'ar' => 'رسوم المنصة 0%', 'ku' => 'کرێی پلاتفۆرم 0%'],
                'description' => [
                    'en' => 'Keep what you raise. We charge 0% platform fee, relying only on optional tips.',
                    'ar' => 'احتفظ بما تجمعه. نحن لا نفرض أي رسوم على المنصة، بل نعتمد فقط على الإكراميات الاختيارية.',
                    'ku' => 'ئەوەی کۆتکردۆتەوە بۆ خۆتە. ئێمە 0% کرێی پلاتفۆرم وەردەگرین، تەنها پشت بە بەخشیشی ئارەزوومەندانە دەبەستین.'
                ],
                'icon' => 'percent',
                'sort_order' => 2,
            ],
            [
                'title' => ['en' => 'Free Withdrawals', 'ar' => 'سحوبات مجانية', 'ku' => 'ڕاکێشانی بێبەرامبەر'],
                'description' => [
                    'en' => 'Access your funds quickly and easily without any withdrawal fees from our side.',
                    'ar' => 'الوصول إلى أموالك بسرعة وسهولة دون أي رسوم سحب من جانبنا.',
                    'ku' => 'بە خێرایی و ئاسانی دەستت بە پارەکانت دەگات بەبێ هیچ کرێیەکی ڕاکێشان لەلایەن ئێمەوە.'
                ],
                'icon' => 'arrow-right-left',
                'sort_order' => 3,
            ],
            [
                'title' => ['en' => 'Secure Payments', 'ar' => 'مدفوعات آمنة', 'ku' => 'پارەدانی پارێزراو'],
                'description' => [
                    'en' => 'Your transactions are protected using the latest technologies and highest encryption standards.',
                    'ar' => 'تتم حماية معاملاتك باستخدام أحدث التقنيات وأعلى معايير التشفير.',
                    'ku' => 'مامەڵەکانت پارێزراون بە بەکارهێنانی نوێترین تەکنەلۆژیا و بەرزترین ستانداردەکانی کۆدکردن.'
                ],
                'icon' => 'shield-check',
                'sort_order' => 4,
            ],
        ];

        foreach ($features as $feature) {
            TrustFeature::updateOrCreate(
                ['sort_order' => $feature['sort_order']],
                $feature
            );
        }
    }
}
