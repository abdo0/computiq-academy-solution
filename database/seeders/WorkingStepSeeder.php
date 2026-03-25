<?php

namespace Database\Seeders;

use App\Models\WorkingStep;
use Illuminate\Database\Seeder;

class WorkingStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = [
            [
                'title' => ['en' => 'Start a Fundraiser', 'ar' => 'ابدأ حملة تبرع', 'ku' => 'کەمپەینێکی بەخشین دەستپێبکە'],
                'description' => [
                    'en' => 'Because our platform is reliable and fast, creating a free fundraiser takes just a few clicks.',
                    'ar' => 'لأن منصتنا موثوقة وسريعة، فإن إنشاء حملة تبرع مجانية لا يتطلب سوى بضع نقرات.',
                    'ku' => 'چونکە پلاتفۆرمەکەمان متمانەپێکراو و خێرایە، دروستکردنی کەمپەینێکی بەخشینی بێبەرامبەر تەنها چەند کرتەیەکی پێویستە.'
                ],
                'step_number' => 1,
            ],
            [
                'title' => ['en' => 'Share Your Fundraiser', 'ar' => 'شارك حملتك', 'ku' => 'کەمپەینەکەت هاوبەش بکە'],
                'description' => [
                    'en' => 'Share your fundraiser on Email, WhatsApp and Social Media channels to reach a worldwide audience & boost your fundraiser.',
                    'ar' => 'شارك حملتك عبر البريد الإلكتروني وواتساب ووسائل التواصل الاجتماعي للوصول إلى جمهور عالمي وتعزيز حملتك.',
                    'ku' => 'کەمپەینەکەت لە ئیمەیل، واتسئەپ و تۆڕە کۆمەڵایەتییەکان هاوبەش بکە بۆ گەیشتن بە بینەرێکی جیهانی و بەهێزکردنی کەمپەینەکەت.'
                ],
                'step_number' => 2,
            ],
            [
                'title' => ['en' => 'Receive Donations', 'ar' => 'استلم التبرعات', 'ku' => 'بەخشینەکان وەربگرە'],
                'description' => [
                    'en' => 'Receive donations directly to your connected bank account. Enjoy payouts even if you fail to meet your goal.',
                    'ar' => 'احصل على التبرعات مباشرة إلى حسابك المصرفي المتصل. استمتع بالدفعات حتى لو لم تحقق هدفك.',
                    'ku' => 'بەخشینەکان ڕاستەوخۆ وەربگرە بۆ هەژمارە بانکییە پەیوەستکراوەکەت. چێژ لە پێدانەکان وەربگرە تەنانەت ئەگەر ئامانجەکەشت نەپێکابێت.'
                ],
                'step_number' => 3,
            ],
            [
                'title' => ['en' => 'Thank Your Donors', 'ar' => 'اشكر متبرعيك', 'ku' => 'سوپاسی بەخشەرەکانت بکە'],
                'description' => [
                    'en' => 'Send automated or personalized thank you messages to your donors to show their support matters.',
                    'ar' => 'أرسل رسائل شكر تلقائية أو مخصصة للمتبرعين لإظهار أن دعمهم يهمك.',
                    'ku' => 'نامەی سوپاسگوزاری ئۆتۆماتیکی یان تایبەت بۆ بەخشەرەکانت بنێرە بۆ ئەوەی پیشانی بدەیت کە پاڵپشتییەکەیان گرنگە.'
                ],
                'step_number' => 4,
            ],
        ];

        foreach ($steps as $step) {
            WorkingStep::updateOrCreate(
                ['step_number' => $step['step_number']],
                $step
            );
        }
    }
}
