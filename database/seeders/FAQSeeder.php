<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => [
                    'en' => 'How do I make a donation?',
                    'ar' => 'كيف يمكنني التبرع؟',
                    'ku' => 'چۆن بەخشین بکەم؟',
                ],
                'answer' => [
                    'en' => '<p>Making a donation is easy! Simply browse our campaigns, select one that resonates with you, and click the "Donate" button. You can choose your payment method and enter the amount you wish to contribute.</p>',
                    'ar' => '<p>التبرع سهل! ببساطة تصفح حملاتنا، اختر واحدة تتردد صداها معك، وانقر على زر "تبرع". يمكنك اختيار طريقة الدفع وإدخال المبلغ الذي ترغب في المساهمة به.</p>',
                    'ku' => '<p>بەخشینکردن ئاسانە! بە ساکاری لە هەوڵدەرەکانمان بگەڕە، یەکێک هەڵبژێرە کە لەگەڵت دەگونجێت، و کلیک لە دوگمەی "بەخشین" بکە. دەتوانیت شێوازی پارەدانت هەڵبژێریت و بڕی پارەکەت بنووسیت کە دەتەوێت بەشداری بکەیت.</p>',
                ],
                'category' => 'Donations',
                'sort_order' => 1,
            ],
            [
                'question' => [
                    'en' => 'What payment methods are accepted?',
                    'ar' => 'ما هي طرق الدفع المقبولة؟',
                    'ku' => 'چ شێوازێکی پارەدان قبوڵ دەکرێت؟',
                ],
                'answer' => [
                    'en' => '<p>We accept multiple payment methods including Qi Card, ZainCash, FastPay, Nasaq, and Asia Hawala. All transactions are processed securely through our payment gateways.</p>',
                    'ar' => '<p>نقبل طرق دفع متعددة بما في ذلك كي كارد وزين كاش وفاست باي وناسق وآسيا حوالة. تتم معالجة جميع المعاملات بأمان من خلال بوابات الدفع لدينا.</p>',
                    'ku' => '<p>چەند شێوازێکی پارەدان قبوڵ دەکەین لەوانە کی کارد، زەین کاش، فاست پەی، ناساق و ئاسیا حوالە. هەموو کاروبارەکان بە سەلامەتی لە ڕێگەی دەروازەی پارەدانەکانمان جێبەجێ دەکرێن.</p>',
                ],
                'category' => 'Payments',
                'sort_order' => 2,
            ],
            [
                'question' => [
                    'en' => 'How do I create a campaign?',
                    'ar' => 'كيف أنشئ حملة؟',
                    'ku' => 'چۆن هەوڵدەرێک دروست بکەم؟',
                ],
                'answer' => [
                    'en' => '<p>To create a campaign, you need to register your organization first. Once verified, you can create a campaign by providing details about your cause, setting a fundraising goal, and adding images. All campaigns go through an approval process before going live.</p>',
                    'ar' => '<p>لإنشاء حملة، تحتاج أولاً إلى تسجيل منظمتك. بمجرد التحقق، يمكنك إنشاء حملة من خلال تقديم تفاصيل عن قضيتك وتحديد هدف لجمع التبرعات وإضافة الصور. تمر جميع الحملات بعملية موافقة قبل الإطلاق.</p>',
                    'ku' => '<p>بۆ دروستکردنی هەوڵدەر، پێشتر پێویستت بە تۆمارکردنی دامەزراوەکەت هەیە. دوای پشتڕاستکردنەوە، دەتوانیت هەوڵدەرێک دروست بکەیت بە دابینکردنی وردەکاری دەربارەی هۆکارەکەت، دانانی ئامانجی کۆکردنەوەی پارە و زیادکردنی وێنە. هەموو هەوڵدەرەکان بە پڕۆسەی پەسندکردن دەڕۆن پێش ئەوەی بە زیندوویی بێن.</p>',
                ],
                'category' => 'Campaigns',
                'sort_order' => 3,
            ],
            [
                'question' => [
                    'en' => 'Is my donation secure?',
                    'ar' => 'هل تبرعي آمن؟',
                    'ku' => 'بەخشینەکەم سەلامەتە؟',
                ],
                'answer' => [
                    'en' => '<p>Yes, absolutely! We use industry-standard encryption and secure payment gateways to protect your financial information. All transactions are processed securely, and we never store your full payment card details.</p>',
                    'ar' => '<p>نعم، بالتأكيد! نستخدم التشفير المعياري في الصناعة وبوابات الدفع الآمنة لحماية معلوماتك المالية. تتم معالجة جميع المعاملات بأمان، ولا نخزن أبداً تفاصيل بطاقة الدفع الكاملة الخاصة بك.</p>',
                    'ku' => '<p>بەڵێ، بە دڵنیایی! شێوازی شاردنەوەی ستانداردی پیشەسازی و دەروازەی پارەدانی سەلامەت بەکاردەهێنین بۆ پارێزگاری لە زانیاری داراییەکانت. هەموو کاروبارەکان بە سەلامەتی جێبەجێ دەکرێن، و هەرگیز وردەکارییە تەواوەکانی کارتی پارەدانەکەت ناهەڵگرین.</p>',
                ],
                'category' => 'Security',
                'sort_order' => 4,
            ],
            [
                'question' => [
                    'en' => 'Can I donate anonymously?',
                    'ar' => 'هل يمكنني التبرع بشكل مجهول؟',
                    'ku' => 'دەتوانم بە نەناسراوی بەخشین بکەم؟',
                ],
                'answer' => [
                    'en' => '<p>Yes, you can choose to make an anonymous donation. When making a donation, you\'ll have the option to keep your identity private. Your contribution will still be counted towards the campaign goal.</p>',
                    'ar' => '<p>نعم، يمكنك اختيار التبرع بشكل مجهول. عند التبرع، سيكون لديك خيار إبقاء هويتك خاصة. ستظل مساهمتك محسوبة ضمن هدف الحملة.</p>',
                    'ku' => '<p>بەڵێ، دەتوانیت هەڵبژاردنی بەخشینکردنی نەناسراوی بکەیت. کاتێک بەخشین دەکەیت، هەڵبژاردەت هەیە ناسنامەکەت تایبەت بەجێبهێڵیت. بەشدارییەکەت هێشتا لە ئامانجی هەوڵدەردا دەژمێردرێت.</p>',
                ],
                'category' => 'Donations',
                'sort_order' => 5,
            ],
            [
                'question' => [
                    'en' => 'How are funds distributed to beneficiaries?',
                    'ar' => 'كيف يتم توزيع الأموال على المستفيدين؟',
                    'ku' => 'چۆن پارەکان دابەش دەکرێن بۆ وەرگرەکان؟',
                ],
                'answer' => [
                    'en' => '<p>Funds are distributed directly to the organization running the campaign. We ensure transparency by tracking all transactions and providing regular updates on how funds are being used. Organizations must provide documentation for fund usage.</p>',
                    'ar' => '<p>يتم توزيع الأموال مباشرة على المنظمة التي تدير الحملة. نضمن الشفافية من خلال تتبع جميع المعاملات وتقديم تحديثات منتظمة حول كيفية استخدام الأموال. يجب على المنظمات تقديم وثائق لاستخدام الأموال.</p>',
                    'ku' => '<p>پارەکان بە ڕاستەوخۆ دابەش دەکرێن بۆ دامەزراوەکە کە هەوڵدەرەکە بەڕێوەدەبات. شەفافیەت دڵنیا دەکەینەوە بە شوێنکەوتنی هەموو کاروبارەکان و دابینکردنی نوێکردنەوەی بەردەوام دەربارەی چۆن پارەکان بەکاردەهێنرێن. دامەزراوەکان دەبێت بەڵگەنامە دابین بکەن بۆ بەکارهێنانی پارە.</p>',
                ],
                'category' => 'Campaigns',
                'sort_order' => 6,
            ],
            [
                'question' => [
                    'en' => 'What fees are charged?',
                    'ar' => 'ما هي الرسوم المفروضة؟',
                    'ku' => 'چ نرخێک دەسەڵات دەدرێت؟',
                ],
                'answer' => [
                    'en' => '<p>We charge a small platform commission (typically 3-5%) to cover operational costs. Payment gateways may also charge processing fees. All fees are clearly displayed before you complete your donation.</p>',
                    'ar' => '<p>نفرض عمولة منصة صغيرة (عادة 3-5٪) لتغطية التكاليف التشغيلية. قد تفرض بوابات الدفع أيضاً رسوم معالجة. يتم عرض جميع الرسوم بوضوح قبل إتمام تبرعك.</p>',
                    'ku' => '<p>کمیسیۆنی بچووکی پلاتفۆرم دەسەڵات دەدەین (بەزۆری 3-5%) بۆ دابینکردنی تێچووی کارکردن. دەروازەی پارەدانیش دەتوانن نرخی جێبەجێکردن دەسەڵات بدەن. هەموو نرخەکان بە ڕوونی پیشان دەدرێن پێش ئەوەی بەخشینەکەت تەواو بکەیت.</p>',
                ],
                'category' => 'Payments',
                'sort_order' => 7,
            ],
            [
                'question' => [
                    'en' => 'How can I report a suspicious campaign?',
                    'ar' => 'كيف يمكنني الإبلاغ عن حملة مشبوهة؟',
                    'ku' => 'چۆن دەتوانم دەربارەی هەوڵدەرێکی گوماناوی ڕاپۆرت بدەم؟',
                ],
                'answer' => [
                    'en' => '<p>If you notice any suspicious activity or believe a campaign violates our terms, you can report it directly from the campaign page. Our team reviews all reports promptly and takes appropriate action.</p>',
                    'ar' => '<p>إذا لاحظت أي نشاط مشبوه أو تعتقد أن حملة تنتهك شروطنا، يمكنك الإبلاغ عنها مباشرة من صفحة الحملة. يراجع فريقنا جميع التقارير على الفور ويتخذ الإجراءات المناسبة.</p>',
                    'ku' => '<p>ئەگەر تێبینی چالاکییەکی گوماناویت کرد یان باوەڕت وەرگرت هەوڵدەرێک یاساکانمان دەشکێنێت، دەتوانیت بە ڕاستەوخۆ لە پەڕەی هەوڵدەر ڕاپۆرت بدەیت. تیمەکەمان هەموو ڕاپۆرتەکان بە خێرایی پێداچوونەوە دەکات و کارێکی گونجاو دەکات.</p>',
                ],
                'category' => 'Safety',
                'sort_order' => 8,
            ],
        ];

        foreach ($faqs as $faqData) {
            // Check if FAQ exists by question (checking English version)
            $questionEn = $faqData['question']['en'] ?? '';
            if (FAQ::whereJsonContains('question->en', $questionEn)->exists()) {
                continue;
            }

            FAQ::create([
                'question' => $faqData['question'],
                'answer' => $faqData['answer'],
                'category' => $faqData['category'] ?? null,
                'sort_order' => $faqData['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
