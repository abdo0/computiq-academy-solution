<?php

namespace Database\Seeders;

use App\Enums\SmsTemplatePurpose;
use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'WELCOME_001',
                'name' => [
                    'en' => 'Welcome SMS',
                    'ar' => 'رسالة ترحيب',
                    'ku' => 'پەیامی بەخێرهاتن',
                ],
                'content' => [
                    'en' => 'Welcome {{user_name}}! Thank you for joining our platform.',
                    'ar' => 'مرحباً {{user_name}}! شكراً لك على الانضمام إلى منصتنا.',
                    'ku' => 'بەخێربێیت {{user_name}}! سوپاس بۆ بەشداریکردنت لە پلاتفۆرمەکەمان.',
                ],
                'purpose' => SmsTemplatePurpose::WELCOME,
                'is_default' => true,
                'variables' => ['user_name'],
            ],
            [
                'code' => 'OTP_VERIFICATION_001',
                'name' => [
                    'en' => 'OTP Verification',
                    'ar' => 'التحقق من رمز OTP',
                    'ku' => 'پشتڕاستکردنەوەی OTP',
                ],
                'content' => [
                    'en' => 'Your verification code is {{otp_code}}. Valid for {{expiry_minutes}} minutes.',
                    'ar' => 'رمز التحقق الخاص بك هو {{otp_code}}. صالح لمدة {{expiry_minutes}} دقيقة.',
                    'ku' => 'کۆدی پشتڕاستکردنەوەکەت {{otp_code}} یە. بۆ {{expiry_minutes}} خولەک بەکارهاتووە.',
                ],
                'purpose' => SmsTemplatePurpose::OTP_VERIFICATION,
                'is_default' => true,
                'variables' => ['otp_code', 'expiry_minutes'],
            ],
            [
                'code' => 'DONATION_CONFIRMATION_001',
                'name' => [
                    'en' => 'Donation Confirmation',
                    'ar' => 'تأكيد التبرع',
                    'ku' => 'دڵنیاکردنەوەی بەخشین',
                ],
                'content' => [
                    'en' => 'Thank you {{donor_name}}! 🙏 Your generous donation of {{amount}} {{currency}} for {{campaign_name}} has been received successfully. Your kindness makes a real difference! Ref: {{transaction_ref}}',
                    'ar' => 'شكراً جزيلاً {{donor_name}}! 🙏 تم استلام تبرعك الكريم بقيمة {{amount}} {{currency}} للحملة {{campaign_name}} بنجاح. كرمك يحدث فرقاً حقيقياً! المرجع: {{transaction_ref}}',
                    'ku' => 'زۆر سوپاس {{donor_name}}! 🙏 بەخشینە بەخشندەکانەکەت بە بڕی {{amount}} {{currency}} بۆ {{campaign_name}} بە سەرکەوتوویی وەرگیرا. بەخشندەیی تۆ جیاوازییەکی ڕاستەقینە دروست دەکات! پەیوەندی: {{transaction_ref}}',
                ],
                'purpose' => SmsTemplatePurpose::DONATION_CONFIRMATION,
                'is_default' => true,
                'variables' => ['donor_name', 'amount', 'currency', 'campaign_name', 'transaction_ref'],
            ],
            [
                'code' => 'DONATION_RECEIPT_001',
                'name' => [
                    'en' => 'Donation Receipt',
                    'ar' => 'إيصال التبرع',
                    'ku' => 'وەسڵی بەخشین',
                ],
                'content' => [
                    'en' => 'Receipt: {{donor_name}}, you donated {{amount}} {{currency}} to {{campaign_name}} on {{donation_date}}. Transaction: {{transaction_ref}}. Thank you for your generosity!',
                    'ar' => 'إيصال: {{donor_name}}، تبرعت بمبلغ {{amount}} {{currency}} للحملة {{campaign_name}} بتاريخ {{donation_date}}. المعاملة: {{transaction_ref}}. شكراً لكرمك!',
                    'ku' => 'وەسڵ: {{donor_name}}، تۆ {{amount}} {{currency}} بۆ {{campaign_name}} بەخشت لە {{donation_date}}. مامەڵە: {{transaction_ref}}. سوپاس بۆ بەخشندەییت!',
                ],
                'purpose' => SmsTemplatePurpose::DONATION_CONFIRMATION,
                'is_default' => false,
                'variables' => ['donor_name', 'amount', 'currency', 'campaign_name', 'donation_date', 'transaction_ref'],
            ],
            [
                'code' => 'PAYMENT_FAILED_001',
                'name' => [
                    'en' => 'Payment Failed',
                    'ar' => 'فشل الدفع',
                    'ku' => 'شکستی پارەدان',
                ],
                'content' => [
                    'en' => 'Payment failed for your donation to {{campaign_name}}. Amount: {{amount}} {{currency}}. Ref: {{transaction_ref}}. Please try again or contact support.',
                    'ar' => 'فشل الدفع لتبرعك للحملة {{campaign_name}}. المبلغ: {{amount}} {{currency}}. المرجع: {{transaction_ref}}. يرجى المحاولة مرة أخرى أو الاتصال بالدعم.',
                    'ku' => 'پارەدان شکستی هێنا بۆ بەخشینەکەت بۆ {{campaign_name}}. بڕ: {{amount}} {{currency}}. پەیوەندی: {{transaction_ref}}. تکایە دووبارە هەوڵ بدە یان پەیوەندی بە پشتگیری بکە.',
                ],
                'purpose' => SmsTemplatePurpose::TRANSACTION_ALERT,
                'is_default' => true,
                'variables' => ['campaign_name', 'amount', 'currency', 'transaction_ref'],
            ],
            [
                'code' => 'PAYMENT_PENDING_001',
                'name' => [
                    'en' => 'Payment Pending',
                    'ar' => 'الدفع قيد المعالجة',
                    'ku' => 'پارەدان لە چاوەڕوانیدایە',
                ],
                'content' => [
                    'en' => 'Your donation of {{amount}} {{currency}} to {{campaign_name}} is being processed. We will notify you once completed. Ref: {{transaction_ref}}',
                    'ar' => 'تبرعك بقيمة {{amount}} {{currency}} للحملة {{campaign_name}} قيد المعالجة. سنخطرك عند اكتمالها. المرجع: {{transaction_ref}}',
                    'ku' => 'بەخشینەکەت بە بڕی {{amount}} {{currency}} بۆ {{campaign_name}} لە چارەسەردایە. کاتێک تەواو بوو ئاگادارت دەکەینەوە. پەیوەندی: {{transaction_ref}}',
                ],
                'purpose' => SmsTemplatePurpose::TRANSACTION_ALERT,
                'is_default' => true,
                'variables' => ['amount', 'currency', 'campaign_name', 'transaction_ref'],
            ],
            [
                'code' => 'REFUND_NOTIFICATION_001',
                'name' => [
                    'en' => 'Refund Notification',
                    'ar' => 'إشعار الاسترداد',
                    'ku' => 'ئاگاداری گەڕاندنەوە',
                ],
                'content' => [
                    'en' => 'Refund processed: {{amount}} {{currency}} has been refunded for your donation to {{campaign_name}}. Ref: {{transaction_ref}}. The amount will appear in your account within 3-5 business days.',
                    'ar' => 'تم معالجة الاسترداد: تم استرداد {{amount}} {{currency}} لتبرعك للحملة {{campaign_name}}. المرجع: {{transaction_ref}}. سيظهر المبلغ في حسابك خلال 3-5 أيام عمل.',
                    'ku' => 'گەڕاندنەوە چارەسەر کرا: {{amount}} {{currency}} گەڕێندرایەوە بۆ بەخشینەکەت بۆ {{campaign_name}}. پەیوەندی: {{transaction_ref}}. بڕەکە لە 3-5 ڕۆژی کاردا لە هەژمارەکەتدا دەرکەوێت.',
                ],
                'purpose' => SmsTemplatePurpose::NOTIFICATION,
                'is_default' => true,
                'variables' => ['amount', 'currency', 'campaign_name', 'transaction_ref'],
            ],
            [
                'code' => 'CAMPAIGN_UPDATE_001',
                'name' => [
                    'en' => 'Campaign Update',
                    'ar' => 'تحديث الحملة',
                    'ku' => 'نوێکردنەوەی هەوڵ',
                ],
                'content' => [
                    'en' => 'Update on {{campaign_name}}: {{update_message}}. Progress: {{progress_percentage}}%. Thank you for your continued support!',
                    'ar' => 'تحديث عن {{campaign_name}}: {{update_message}}. التقدم: {{progress_percentage}}%. شكراً لدعمك المستمر!',
                    'ku' => 'نوێکردنەوە لەسەر {{campaign_name}}: {{update_message}}. پێشکەوتن: {{progress_percentage}}%. سوپاس بۆ پشتگیری بەردەوامەکەت!',
                ],
                'purpose' => SmsTemplatePurpose::CAMPAIGN_REMINDER,
                'is_default' => true,
                'variables' => ['campaign_name', 'update_message', 'progress_percentage'],
            ],
            [
                'code' => 'CAMPAIGN_COMPLETED_001',
                'name' => [
                    'en' => 'Campaign Completed',
                    'ar' => 'اكتمال الحملة',
                    'ku' => 'تەواوبوونی هەوڵ',
                ],
                'content' => [
                    'en' => 'Great news! {{campaign_name}} has reached its goal! 🎉 Total raised: {{total_raised}} {{currency}}. Thank you {{donor_name}} for being part of this success!',
                    'ar' => 'أخبار رائعة! {{campaign_name}} وصلت إلى هدفها! 🎉 إجمالي المبلغ: {{total_raised}} {{currency}}. شكراً {{donor_name}} لكونك جزءاً من هذا النجاح!',
                    'ku' => 'هەواڵێکی نایاب! {{campaign_name}} گەیشتە ئامانجەکەی! 🎉 کۆی گەیشتوو: {{total_raised}} {{currency}}. سوپاس {{donor_name}} بۆ بوون بە بەشێک لەم سەرکەوتنە!',
                ],
                'purpose' => SmsTemplatePurpose::CAMPAIGN_REMINDER,
                'is_default' => true,
                'variables' => ['campaign_name', 'total_raised', 'currency', 'donor_name'],
            ],
            [
                'code' => 'THANK_YOU_FOLLOWUP_001',
                'name' => [
                    'en' => 'Thank You Follow-up',
                    'ar' => 'رسالة شكر متابعة',
                    'ku' => 'پەیامی سوپاسی دواتر',
                ],
                'content' => [
                    'en' => 'Hi {{donor_name}}, we wanted to thank you again for your {{amount}} {{currency}} donation to {{campaign_name}}. Your support is making a real impact! 🙏',
                    'ar' => 'مرحباً {{donor_name}}، نود أن نشكرك مرة أخرى على تبرعك البالغ {{amount}} {{currency}} للحملة {{campaign_name}}. دعمك يحدث تأثيراً حقيقياً! 🙏',
                    'ku' => 'سڵاو {{donor_name}}، دەمانویست دووبارە سوپاست بکەین بۆ بەخشینەکەت بە بڕی {{amount}} {{currency}} بۆ {{campaign_name}}. پشتگیری تۆ کاریگەرییەکی ڕاستەقینە دروست دەکات! 🙏',
                ],
                'purpose' => SmsTemplatePurpose::DONATION_CONFIRMATION,
                'is_default' => false,
                'variables' => ['donor_name', 'amount', 'currency', 'campaign_name'],
            ],
            [
                'code' => 'GENERAL_INFO_001',
                'name' => [
                    'en' => 'General Information',
                    'ar' => 'معلومات عامة',
                    'ku' => 'زانیاری گشتی',
                ],
                'content' => [
                    'en' => '{{info_message}} For more information, visit our website or contact us at {{support_phone}}.',
                    'ar' => '{{info_message}} لمزيد من المعلومات، قم بزيارة موقعنا أو اتصل بنا على {{support_phone}}.',
                    'ku' => '{{info_message}} بۆ زانیاری زیاتر، سەردانی ماڵپەڕەکەمان بکە یان پەیوەندی بە {{support_phone}} بکە.',
                ],
                'purpose' => SmsTemplatePurpose::GENERAL,
                'is_default' => true,
                'variables' => ['info_message', 'support_phone'],
            ],
            [
                'code' => 'TRANSACTION_ALERT_001',
                'name' => [
                    'en' => 'Transaction Alert',
                    'ar' => 'تنبيه المعاملة',
                    'ku' => 'ئاگاداری مامەڵە',
                ],
                'content' => [
                    'en' => 'Transaction {{transaction_ref}}: {{status}}. Amount: {{amount}} {{currency}}. Date: {{transaction_date}}',
                    'ar' => 'المعاملة {{transaction_ref}}: {{status}}. المبلغ: {{amount}} {{currency}}. التاريخ: {{transaction_date}}',
                    'ku' => 'مامەڵە {{transaction_ref}}: {{status}}. بڕ: {{amount}} {{currency}}. بەروار: {{transaction_date}}',
                ],
                'purpose' => SmsTemplatePurpose::TRANSACTION_ALERT,
                'is_default' => true,
                'variables' => ['transaction_ref', 'status', 'amount', 'currency', 'transaction_date'],
            ],
            [
                'code' => 'CAMPAIGN_REMINDER_001',
                'name' => [
                    'en' => 'Campaign Reminder',
                    'ar' => 'تذكير الحملة',
                    'ku' => 'ئەزکەری هەوڵ',
                ],
                'content' => [
                    'en' => 'Reminder: {{campaign_name}} needs your support! Progress: {{progress_percentage}}%. Help us reach the goal!',
                    'ar' => 'تذكير: {{campaign_name}} يحتاج إلى دعمك! التقدم: {{progress_percentage}}%. ساعدنا في الوصول إلى الهدف!',
                    'ku' => 'ئەزکەر: {{campaign_name}} پشتگیری تۆ پێویستە! پێشکەوتن: {{progress_percentage}}%. یارمەتیمان بدە بۆ گەیشتن بە ئامانج!',
                ],
                'purpose' => SmsTemplatePurpose::CAMPAIGN_REMINDER,
                'is_default' => true,
                'variables' => ['campaign_name', 'progress_percentage'],
            ],
        ];

        foreach ($templates as $templateData) {
            // Check if template exists by code
            if (SmsTemplate::where('code', $templateData['code'])->exists()) {
                continue;
            }

            SmsTemplate::create($templateData);
        }
    }
}
