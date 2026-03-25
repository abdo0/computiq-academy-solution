<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about-us',
                'title' => [
                    'en' => 'About Us',
                    'ar' => 'من نحن',
                    'ku' => 'دەربارەی ئێمە',
                ],
                'content' => [
                    'en' => <<<'HTML'
<div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center mb-20">
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Our Story</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            Founded with a vision to create lasting impact, Nakhwaa has grown into a trusted platform connecting generous donors with those in urgent need across Iraq and beyond.
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            We believe that transparency, accountability, and compassion are the cornerstones of effective charitable giving. Our team works tirelessly to verify campaigns and ensure your contributions make a real difference.
        </p>
    </div>
    <div class="mt-10 lg:mt-0 relative">
        <img src="/images/SVG/3.svg" alt="Team work" class="rounded-sm shadow-2xl rotate-3 hover:rotate-0 transition-all duration-300" />
    </div>
</div>
<div class="grid md:grid-cols-2 gap-8 mb-20">
    <div class="bg-brand-50 dark:bg-gray-800 p-8 rounded-sm border border-brand-100 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-brand-900 dark:text-brand-400 mb-4">Our Vision</h3>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            A world where no one in need is left behind, and every individual has access to clear pathways for charitable giving and community support.
        </p>
    </div>
    <div class="bg-brand-50 dark:bg-gray-800 p-8 rounded-sm border border-brand-100 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-brand-900 dark:text-brand-400 mb-4">Our Mission</h3>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            To provide a secure, transparent, and easy-to-use platform that connects compassionate donors directly with verified campaigns and individuals in need.
        </p>
    </div>
</div>
HTML,
                    'ar' => <<<'HTML'
<div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center mb-20">
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">قصتنا</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            تأسست نخوة برؤية لإحداث تأثير دائم، وتطورت لتصبح منصة موثوقة تربط المتبرعين الكرماء بأولئك الذين هم في أمس الحاجة في جميع أنحاء العراق وخارجه.
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            نحن نؤمن بأن الشفافية والمساءلة والرحمة هي حجر الزاوية للعطاء الخيري الفعال. يعمل فريقنا بلا كلل للتحقق من الحملات وضمان أن تحدث مساهماتك فرقاً حقيقياً.
        </p>
    </div>
    <div class="mt-10 lg:mt-0 relative">
        <img src="/images/SVG/3.svg" alt="Team work" class="rounded-sm shadow-2xl rotate-3 hover:rotate-0 transition-all duration-300" />
    </div>
</div>
<div class="grid md:grid-cols-2 gap-8 mb-20">
    <div class="bg-brand-50 dark:bg-gray-800 p-8 rounded-sm border border-brand-100 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-brand-900 dark:text-brand-400 mb-4">رؤيتنا</h3>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            عالم لا يُترك فيه أي محتاج، ويتمتع فيه كل فرد بفرص واضحة للعطاء الخيري ودعم المجتمع.
        </p>
    </div>
    <div class="bg-brand-50 dark:bg-gray-800 p-8 rounded-sm border border-brand-100 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-brand-900 dark:text-brand-400 mb-4">مهمتنا</h3>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            توفير منصة آمنة وشفافة وسهلة الاستخدام تربط المتبرعين المتعاطفين مباشرة بالحملات الموثوقة والأفراد المحتاجين.
        </p>
    </div>
</div>
HTML,
                    'ku' => <<<'HTML'
<div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center mb-20">
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">چیرۆکی ئێمە</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            بە تێڕوانینێک بۆ دروستکردنی کاریگەری بەردەوام دامەزراوە، نەخوە گەشەی کردووە بۆ پلاتفۆرمێکی متمانەپێکراو کە بەخشەرە دڵفراوانەکان بەوانەی پێویستیان هەیە لە سەرانسەری عێراق دەبەستێتەوە.
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            باوەڕمان وایە کە شەفافیەت، لێپرسینەوە، و سۆز کۆڵەکەی بەخشینی خێرخوازی کاریگەرە. تیمەکەمان بەبێ وەستان کاردەکات بۆ پشتڕاستکردنەوەی کەمپینەکان و دڵنیابوون لەوەی بەشدارییەکانت گۆڕانکاری ڕاستەقینە دروست دەکەن.
        </p>
    </div>
    <div class="mt-10 lg:mt-0 relative">
        <img src="/images/SVG/3.svg" alt="Team work" class="rounded-sm shadow-2xl rotate-3 hover:rotate-0 transition-all duration-300" />
    </div>
</div>
<div class="grid md:grid-cols-2 gap-8 mb-20">
    <div class="bg-brand-50 dark:bg-gray-800 p-8 rounded-sm border border-brand-100 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-brand-900 dark:text-brand-400 mb-4">تێڕوانینمان</h3>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            جیهانێک کە هیچ کەسێک کە پێویستی بە یارمەتییە بەجێناهێڵرێت، وە هەر تاکێک دەستی دەگات بە ڕێگای ڕوون بۆ بەخشینی خێرخوازی و پاڵپشتی کۆمەڵگا.
        </p>
    </div>
    <div class="bg-brand-50 dark:bg-gray-800 p-8 rounded-sm border border-brand-100 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-brand-900 dark:text-brand-400 mb-4">ئەرکمان</h3>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
            بۆ دابینکردنی پلاتفۆرمێکی سەلامەت، شەفاف، و ئاسان بۆ بەکارهێنان کە بەخشەرە بەسۆزەکان ڕاستەوخۆ بە کەمپینە پشتڕاستکراوەکان و ئەو کەسانەی پێویستیانە دەبەستێتەوە.
        </p>
    </div>
</div>
HTML,
                ],
                'is_published' => true,
                'sort_order' => 1,
                'show_in_header' => false,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'About Us - Nakhwaa',
                    'ar' => 'من نحن - نخوة',
                    'ku' => 'دەربارەی ئێمە - نەخوە'
                ],
                'meta_description' => [
                    'en' => 'Learn about Nakhwaa platform and our mission to connect donors with those in need.',
                    'ar' => 'تعرف على منصة نخوة ومهمتنا في ربط المتبرعين بالمحتاجين.',
                    'ku' => 'سەبارەت بە پلاتفۆرمی نەخوە و ئەرکمان بۆ بەستنەوەی بەخشەران بەو کەسانەی پێویستیانە بزانە.'
                ],
            ],
            [
                'slug' => 'how-it-works',
                'title' => [
                    'en' => 'How It Works',
                    'ar' => 'كيف يعمل',
                    'ku' => 'چۆن کاردەکات',
                ],
                'content' => [
                    'en' => '<h2>How Nakhwaa Works</h2><ol><li><strong>Browse Campaigns:</strong> Explore verified campaigns across various categories</li><li><strong>Choose to Donate:</strong> Select a campaign that resonates with you</li><li><strong>Secure Payment:</strong> Make your donation through our secure payment gateways</li><li><strong>Track Impact:</strong> See how your contribution makes a difference</li></ol>',
                    'ar' => '<h2>كيف تعمل نخوة</h2><ol><li><strong>تصفح الحملات:</strong> استكشف الحملات الموثوقة عبر فئات متنوعة</li><li><strong>اختر التبرع:</strong> اختر حملة تتردد صداها معك</li><li><strong>دفع آمن:</strong> قدم تبرعك من خلال بوابات الدفع الآمنة لدينا</li><li><strong>تتبع التأثير:</strong> شاهد كيف يحدث مساهمتك فرقاً</li></ol>',
                    'ku' => '<h2>چۆن نەخوە کاردەکات</h2><ol><li><strong>گەڕان لە هەوڵدەرەکان:</strong> لێکۆڵینەوە لە هەوڵدەرە پشتڕاستکراوەکان لە بەشە جۆراوجۆرەکان</li><li><strong>هەڵبژاردنی بەخشین:</strong> هەوڵدەرێک هەڵبژێرە کە لەگەڵت دەگونجێت</li><li><strong>پارەدانی سەلامەت:</strong> بەخشینەکەت لە ڕێگەی دەروازەی پارەدانی سەلامەتەکانمان بکە</li><li><strong>شوێنکەوتنی کاریگەری:</strong> بزانە چۆن بەشدارییەکەت گۆڕانکاری دروستدەکات</li></ol>',
                ],
                'is_published' => true,
                'sort_order' => 2,
                'show_in_header' => false,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'How It Works - Nakhwaa',
                    'ar' => 'كيف نعمل - نخوة',
                    'ku' => 'ئێمە چۆن کاردەکەین - نەخوە'
                ],
                'meta_description' => [
                    'en' => 'Learn how to use Nakhwaa platform to make donations and support campaigns.',
                    'ar' => 'تعرف على كيفية عمل منصتنا وأثر التبرعات.',
                    'ku' => 'بزانە ئێمە چۆن کاردەکەین و چۆن بەخشینەکان بەڕێوەدەبەین.'
                ],
            ],
            [
                'slug' => 'contact-us',
                'title' => [
                    'en' => 'Contact Us',
                    'ar' => 'اتصل بنا',
                    'ku' => 'پەیوەندی بە ئێمەوە',
                ],
                'content' => [
                    'en' => <<<'HTML'
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">We'd love to hear from you</h2>
    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
        Whether you have a question about a campaign, want to partner with us, or need technical support, our dedicated team is ready to help you every step of the way. Please use the form on this page to send us a message, or reach out using the contact information provided.
    </p>
    <div class="bg-brand-50 dark:bg-gray-800 p-6 rounded-sm border-l-4 border-brand-500">
        <h4 class="font-bold text-gray-900 dark:text-white mb-2">Office Hours</h4>
        <p class="text-sm text-gray-700 dark:text-gray-400">Sunday - Thursday: 9:00 AM - 5:00 PM (Baghdad Time)</p>
        <p class="text-sm text-gray-700 dark:text-gray-400">Friday - Saturday: Closed</p>
    </div>
</div>
HTML,
                    'ar' => <<<'HTML'
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">نحب أن نسمع منك</h2>
    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
        سواء كان لديك سؤال حول إحدى الحملات، أو ترغب في الشراكة معنا، أو تحتاج إلى دعم فني، فإن فريقنا المخصص مستعد لمساعدتك في كل خطوة. يرجى استخدام النموذج الموجود في هذه الصفحة لإرسال رسالة إلينا، أو التواصل معنا باستخدام معلومات الاتصال المقدمة.
    </p>
    <div class="bg-brand-50 dark:bg-gray-800 p-6 rounded-sm border-l-4 border-brand-500">
        <h4 class="font-bold text-gray-900 dark:text-white mb-2">ساعات العمل</h4>
        <p class="text-sm text-gray-700 dark:text-gray-400">الأحد - الخميس: 9:00 صباحاً - 5:00 مساءً (بتوقيت بغداد)</p>
        <p class="text-sm text-gray-700 dark:text-gray-400">الجمعة - السبت: مغلق</p>
    </div>
</div>
HTML,
                    'ku' => <<<'HTML'
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">دڵخۆش دەبین بە بیستنی لە تۆ</h2>
    <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
        گرنگ نییە پرسیارێک لەسەر کەمپینێک هەیە، دەتەوێت لەگەڵمان هاوبەشی بکەیت، یان پێویستت بە پاڵپشتی تەکنیکی هەیە، تیمە تەرخانکراوەکەمان ئامادەیە لە هەموو هەنگاوێکدا هاوکاریت بکات. تکایە فۆڕمی سەر ئەم پەڕەیە بەکاربهێنە بۆ ناردنی نامەیەک، یان پەیوەندیمان پێوە بکە لە ڕێگەی زانیارییەکانی پەیوەندییەوە.
    </p>
    <div class="bg-brand-50 dark:bg-gray-800 p-6 rounded-sm border-l-4 border-brand-500">
        <h4 class="font-bold text-gray-900 dark:text-white mb-2">کاتەکانی دەوام</h4>
        <p class="text-sm text-gray-700 dark:text-gray-400">یەکشەممە - پێنجشەممە: 9:00 بەیانی - 5:00 ئێوارە (بە کاتی بەغدا)</p>
        <p class="text-sm text-gray-700 dark:text-gray-400">هەینی - شەممە: داخراوە</p>
    </div>
</div>
HTML,
                ],
                'is_published' => true,
                'sort_order' => 3,
                'show_in_header' => false,
                'show_in_footer' => false,
                'meta_title' => [
                    'en' => 'Contact Us - Nakhwaa',
                    'ar' => 'اتصل بنا - نخوة',
                    'ku' => 'پەیوەندی بە ئێمەوە - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Get in touch with Nakhwaa team for support and inquiries.',
                    'ar' => 'تواصل مع فريق نخوة للحصول على الدعم والاستفسارات.',
                    'ku' => 'لەگەڵ تیمی نەخوە لە پەیوەندیدابە بۆ پاڵپشتی و لێپرسینەوە.',
                ],
            ],
            [
                'slug' => 'privacy-policy',
                'title' => [
                    'en' => 'Privacy Policy',
                    'ar' => 'سياسة الخصوصية',
                    'ku' => 'سیاسەتی تایبەتمەندی',
                ],
                'content' => [
                    'en' => '<h2>Privacy Policy</h2><p>At Nakhwaa, we take your privacy seriously. This policy explains how we collect, use, and protect your personal information.</p><h3>Information We Collect</h3><p>We collect information that you provide directly to us, including name, email, phone number, and payment information.</p><h3>How We Use Your Information</h3><p>We use your information to process donations, communicate with you, and improve our services.</p>',
                    'ar' => '<h2>سياسة الخصوصية</h2><p>في نخوة، نأخذ خصوصيتك على محمل الجد. توضح هذه السياسة كيفية جمع معلوماتك الشخصية واستخدامها وحمايتها.</p><h3>المعلومات التي نجمعها</h3><p>نجمع المعلومات التي تقدمها لنا مباشرة، بما في ذلك الاسم والبريد الإلكتروني ورقم الهاتف ومعلومات الدفع.</p><h3>كيف نستخدم معلوماتك</h3><p>نستخدم معلوماتك لمعالجة التبرعات والتواصل معك وتحسين خدماتنا.</p>',
                    'ku' => '<h2>سیاسەتی تایبەتمەندی</h2><p>لە نەخوە، تایبەتمەندییەکەت بە گرنگی وەردەگرین. ئەم سیاسەتە ڕوون دەکاتەوە چۆن زانیاری کەسییەکانت کۆدەکەینەوە، بەکاردەهێنین و پارێزگاری لێدەکەین.</p><h3>زانیارییەکانی کۆدەکەینەوە</h3><p>زانیارییەکانی کۆدەکەینەوە کە بە ڕاستەوخۆ پێمان دەدەیت، لەوانە ناو، ئیمەیڵ، ژمارەی تەلەفۆن و زانیاری پارەدان.</p><h3>چۆن زانیارییەکانت بەکاردەهێنین</h3><p>زانیارییەکانت بەکاردەهێنین بۆ جێبەجێکردنی بەخشین، پەیوەندی لەگەڵ تۆ و باشترکردنی خزمەتگوزارییەکانمان.</p>',
                ],
                'is_published' => true,
                'sort_order' => 4,
                'show_in_header' => false,
                'show_in_footer' => false,
                'meta_title' => [
                    'en' => 'Privacy Policy - Nakhwaa',
                    'ar' => 'سياسة الخصوصية - نخوة',
                    'ku' => 'سیاسەتی تایبەتمەندی - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Read our privacy policy to understand how we protect your personal information.',
                    'ar' => 'اقرأ سياسة الخصوصية الخاصة بنا لتفهم كيف نحمي معلوماتك الشخصية.',
                    'ku' => 'سیاسەتی تایبەتمەندیمان بخوێنەوە بۆ تێگەیشتن لە چۆنیەتی پاراستنی زانیارییە کەسییەکانت.',
                ],
            ],
            [
                'slug' => 'terms-of-service',
                'title' => [
                    'en' => 'Terms of Service',
                    'ar' => 'شروط الخدمة',
                    'ku' => 'مەرجەکانی خزمەتگوزاری',
                ],
                'content' => [
                    'en' => '<h2>Terms of Service</h2><p>By using Nakhwaa platform, you agree to these terms and conditions.</p><h3>User Responsibilities</h3><p>Users are responsible for providing accurate information and using the platform in accordance with applicable laws.</p><h3>Campaign Guidelines</h3><p>All campaigns must comply with our guidelines and be verified before going live.</p>',
                    'ar' => '<h2>شروط الخدمة</h2><p>باستخدام منصة نخوة، أنت توافق على هذه الشروط والأحكام.</p><h3>مسؤوليات المستخدم</h3><p>المستخدمون مسؤولون عن تقديم معلومات دقيقة واستخدام المنصة وفقاً للقوانين المعمول بها.</p><h3>إرشادات الحملات</h3><p>يجب أن تتوافق جميع الحملات مع إرشاداتنا وأن يتم التحقق منها قبل الإطلاق.</p>',
                    'ku' => '<h2>مەرجەکانی خزمەتگوزاری</h2><p>بە بەکارهێنانی پلاتفۆرمی نەخوە، تۆ ڕازیت بەم مەرجانە.</p><h3>بەرپرسیارییەتەکانی بەکارهێنەر</h3><p>بەکارهێنەران بەرپرسیارن لە دابینکردنی زانیاری دروست و بەکارهێنانی پلاتفۆرم بەپێی یاساکانی جێبەجێکراو.</p><h3>ڕێنماییەکانی هەوڵدەر</h3><p>هەموو هەوڵدەرەکان دەبێت لەگەڵ ڕێنماییەکانمان بگونجێن و پشتڕاست بکرێنەوە پێش ئەوەی بە زیندوویی بێن.</p>',
                ],
                'is_published' => true,
                'sort_order' => 5,
                'show_in_header' => false,
                'show_in_footer' => false,
                'meta_title' => [
                    'en' => 'Terms of Service - Nakhwaa',
                    'ar' => 'شروط الخدمة - نخوة',
                    'ku' => 'مەرجەکانی خزمەتگوزاری - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Read our terms of service to understand the rules and guidelines for using Nakhwaa platform.',
                    'ar' => 'اقرأ شروط الخدمة لتعرف القواعد والإرشادات الخاصة بمنصة نخوة.',
                    'ku' => 'مەرجەکانی خزمەتگوزاریمان بخوێنەوە بۆ زانینی ڕێنماییەکانمان.'
                ],
            ],
            [
                'slug' => 'pricing',
                'title' => [
                    'en' => 'Pricing',
                    'ar' => 'الأسعار والرسوم',
                    'ku' => 'نرخ و خرچەکان',
                ],
                'content' => [
                    'en' => '<h2>Pricing & Fees</h2><p>Learn about our platform fees, payment gateway charges, and how we keep costs transparent for donors and organizations.</p>',
                    'ar' => '<h2>الأسعار والرسوم</h2><p>تعرف على رسوم المنصة ورسوم بوابات الدفع وكيف نحافظ على الشفافية في التكاليف للمتبرعين والمنظمات.</p>',
                    'ku' => '<h2>نرخ و خرچەکان</h2><p>بزانە نرخەکانی پلاتفۆرم، تێچووی دەرهێنەرانی پارە و چۆن شەفافییەت لە تێچووەکان دەپارێزین بۆ بەخشەران و ڕێکخراوەکان.</p>',
                ],
                'is_published' => true,
                'sort_order' => 6,
                'show_in_header' => true,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Pricing - Nakhwaa',
                    'ar' => 'الأسعار - نخوة',
                    'ku' => 'پەیوەندی - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Understand Nakhwaa platform pricing, fees, and transparency policy.',
                    'ar' => 'تعرف على تسعيرة منصتنا والمصاريف التشغيلية للمنصات.',
                    'ku' => 'پاراستنی تێچووەکان و ڕێنماییەکانی دارایی بەکارهێنەران بخوێنەوە.',
                ],
            ],
            [
                'slug' => 'trust-safety',
                'title' => [
                    'en' => 'Trust & Safety',
                    'ar' => 'الثقة والأمان',
                    'ku' => 'متمانە و پاراستن',
                ],
                'content' => [
                    'en' => '<h2>Trust & Safety</h2><p>Learn how we verify organizations and campaigns, monitor transactions, and protect donors and beneficiaries.</p>',
                    'ar' => '<h2>الثقة والأمان</h2><p>تعرف كيف نقوم بالتحقق من المنظمات والحملات، ومراقبة المعاملات، وحماية المتبرعين والمستفيدين.</p>',
                    'ku' => '<h2>متمانە و پاراستن</h2><p>بزانە چۆن ڕێکخراوەکان و هەوڵدەرەکان پشتڕاستدەکەینەوە، مامەڵەکان نێزانین و پاراستنی بەخشەران و سوودمەندان دەپەروێزکەین.</p>',
                ],
                'is_published' => true,
                'sort_order' => 7,
                'show_in_header' => true,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Trust & Safety - Nakhwaa',
                    'ar' => 'الثقة والأمان - نخوة',
                    'ku' => 'متمانە و پاراستن - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Learn about Nakhwaa trust & safety measures for donors and organizations.',
                    'ar' => 'تعرف على إجراءات الثقة والأمان للمتبرعين والحملات.',
                    'ku' => 'ڕۆشنکردنی یاسای پاراستن و مەرجی گشتییەکان.',
                ],
            ],
            [
                'slug' => 'success-stories',
                'title' => [
                    'en' => 'Success Stories',
                    'ar' => 'قصص النجاح',
                    'ku' => 'چیرۆکانی سەرکەوتن',
                ],
                'content' => [
                    'en' => '<h2>Success Stories</h2><p>Read real stories of impact made possible by generous donors like you.</p>',
                    'ar' => '<h2>قصص النجاح</h2><p>اقرأ قصصاً حقيقية عن التأثير الذي أحدثه المتبرعون الكرماء مثلك.</p>',
                    'ku' => '<h2>چیرۆکانی سەرکەوتن</h2><p>چیرۆکە ڕاستەقینەکان بخوێنەوە لەسەر کاریگەری بەخشەران وەک تۆ.</p>',
                ],
                'is_published' => true,
                'sort_order' => 8,
                'show_in_header' => false,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Success Stories - Nakhwaa',
                    'ar' => 'قصص النجاح - نخوة',
                    'ku' => 'چیرۆکانی سەرکەوتن - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Real impact stories from Nakhwaa campaigns and donors.',
                    'ar' => 'قصص أثر رائعة وملهمة من المتبرعين المستفيدين عبر منصة نخوة.',
                    'ku' => 'چیرۆکە سەرکەوتووەکانی نەخوە لەلایەن بەخشەرەکان و هەوڵدەرەکان.',
                ],
            ],
            [
                'slug' => 'impact-reports',
                'title' => [
                    'en' => 'Impact Reports',
                    'ar' => 'تقارير الأثر',
                    'ku' => 'ڕاپۆرتەکانی کاریگەری',
                ],
                'content' => [
                    'en' => '<h2>Impact Reports</h2><p>Access detailed impact and financial reports to see how donations are used.</p>',
                    'ar' => '<h2>تقارير الأثر</h2><p>اطلع على تقارير تفصيلية للأثر والتقارير المالية لترى كيفية استخدام التبرعات.</p>',
                    'ku' => '<h2>ڕاپۆرتەکانی کاریگەری</h2><p>ڕاپۆرتە وردەکارییەکان بخوێنەوە بۆ کاریگەری و دارایی بۆ بینینی چۆن بەخشین بەکاردەهاتووە.</p>',
                ],
                'is_published' => true,
                'sort_order' => 9,
                'show_in_header' => false,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Impact Reports - Nakhwaa',
                    'ar' => 'تقارير الأثر - نخوة',
                    'ku' => 'ڕاپۆرتەکانی کاریگەری - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Impact and financial reporting for Nakhwaa donations and campaigns.',
                    'ar' => 'تقارير الأثر السنوي لجمع التبرعات في منصة نخوة.',
                    'ku' => 'ڕاپۆرتە داراییەکان و وردەکارییەکانی بەخشینەکان دەخوێنەوە.',
                ],
            ],
            [
                'slug' => 'guide',
                'title' => [
                    'en' => 'Guide',
                    'ar' => 'الدليل',
                    'ku' => 'ڕێنمود',
                ],
                'content' => [
                    'en' => '<h2>User Guide</h2><p>Learn how to use Nakhwaa step-by-step, from creating an account to supporting campaigns and tracking your impact.</p>',
                    'ar' => '<h2>دليل المستخدم</h2><p>تعرف على كيفية استخدام منصة نخوة خطوة بخطوة، من إنشاء حساب حتى دعم الحملات وتتبع تأثير تبرعاتك.</p>',
                    'ku' => '<h2>ڕێنمودی بەکارهێنەر</h2><p>فێربە چۆن پلاتفۆرمی نەخوە بەکاربهێنیت هەلەهەمەدانە، لە دروستکردنی هەژمار تا پاڵپشتیکردن لە هەوڵدەرەکان و شوێنکەوتنی کاریگەری بەخشینەکانت.</p>',
                ],
                'is_published' => true,
                'sort_order' => 10,
                'show_in_header' => false,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Guide - Nakhwaa',
                    'ar' => 'الدليل - نخوة',
                    'ku' => 'ڕێنمود - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Step-by-step guide to using the Nakhwaa donation platform.',
                    'ar' => 'دليل خطوة بخطوة لاستخدام منصة التبرعات الخاصة بنا.',
                    'ku' => 'ڕێنمودی هەنگاو بە هەنگاو بۆ بەکارھێنانی نەخوە.',
                ],
            ],
            [
                'slug' => 'campaigns',
                'title' => [
                    'en' => 'Campaigns',
                    'ar' => 'حملاتنا',
                    'ku' => 'هەڵمەتەکانمان',
                ],
                'content' => [
                    'en' => 'Campaigns',
                    'ar' => 'الحملات',
                    'ku' => 'هەڵمەتەکان',
                ],
                'is_published' => true,
                'sort_order' => 11,
                'show_in_header' => true,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Campaigns - Nakhwaa',
                    'ar' => 'الحملات - نخوة',
                    'ku' => 'هەڵمەتەکان - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Choose a campaign and help change a life. Every donation, no matter how small, makes a great impact.',
                    'ar' => 'اختر حملة وساهم في تغيير حياة إنسان. كل تبرع، مهما كان بسيطاً، يصنع أثراً عظيماً.',
                    'ku' => 'هەڵمەتێک هەڵبژێرە و یارمەتیدەربە لە گۆڕینی ژیانێک. هەر بەخشینێک، با هەرچەند بچووکیش بێت، کاریگەرییەکی گەورەی هەیە.'
                ],
            ],
            [
                'slug' => 'blog',
                'title' => [
                    'en' => 'Our Blog & Updates',
                    'ar' => 'أخبارنا وقصص النجاح',
                    'ku' => 'بلۆگ و نوێکارییەکانمان',
                ],
                'content' => [
                    'en' => 'Blog',
                    'ar' => 'المدونة',
                    'ku' => 'بلۆگ',
                ],
                'is_published' => true,
                'sort_order' => 12,
                'show_in_header' => true,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'Blog - Nakhwaa',
                    'ar' => 'المدونة - نخوة',
                    'ku' => 'بلۆگ - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Stay informed with the latest news, success stories, and updates from our community and ongoing campaigns.',
                    'ar' => 'ابق على اطلاع بآخر الأخبار وقصص النجاح والتحديثات من مجتمعنا وحملاتنا المستمرة.',
                    'ku' => 'ئاگاداربە لە نوێترین هەواڵەکان، چیرۆکەکانی سەرکەوتن، و نوێکارییەکان لە کۆمەڵگەکەمان و هەڵمەتە بەردەوامەکانمانەوە.'
                ],
            ],
            [
                'slug' => 'favorites',
                'title' => [
                    'en' => 'My Whitelist',
                    'ar' => 'قائمة المفضلة',
                    'ku' => 'لیستی دڵخوازەکانم',
                ],
                'content' => [
                    'en' => 'Favorites',
                    'ar' => 'المفضلة',
                    'ku' => 'دڵخوازەکان',
                ],
                'is_published' => true,
                'sort_order' => 13,
                'show_in_header' => true,
                'show_in_footer' => true,
                'meta_title' => [
                    'en' => 'My Whitelist - Nakhwaa',
                    'ar' => 'قائمة المفضلة - نخوة',
                    'ku' => 'لیستی دڵخوازەکانم - نەخوە',
                ],
                'meta_description' => [
                    'en' => 'Campaigns you have marked with the heart.',
                    'ar' => 'الحملات التي قمت بتمييزها بعلامة القلب لسهولة الوصول إليها لاحقاً.',
                    'ku' => 'ئەو هەڵمەتانەی کە بە دڵ نیشانەت کردوون.'
                ],
            ],
        ];

        // Ensure database gets updated
        Page::truncate();

        foreach ($pages as $pageData) {
            Page::create([
                'slug' => $pageData['slug'],
                'title' => $pageData['title'],
                'content' => $pageData['content'],
                'is_published' => $pageData['is_published'] ?? false,
                'sort_order' => $pageData['sort_order'],
                'meta_title' => $pageData['meta_title'] ?? null,
                'meta_description' => $pageData['meta_description'] ?? null,
                'show_in_header' => $pageData['show_in_header'] ?? false,
                'show_in_footer' => $pageData['show_in_footer'] ?? false,
            ]);
        }
    }
}
