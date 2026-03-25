<?php

namespace Database\Seeders;

use App\Enums\EmailTemplatePurpose;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
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
                    'en' => 'Welcome Email',
                    'ar' => 'بريد الترحيب',
                    'ku' => 'ئیمەیڵی بەخێرهاتن',
                ],
                'subject' => [
                    'en' => 'Welcome to Our Platform!',
                    'ar' => 'مرحباً بك في منصتنا!',
                    'ku' => 'بەخێربێیت بۆ پلاتفۆرمەکەمان!',
                ],
                'body' => [
                    'en' => '<h1>Welcome {{user_name}}!</h1><p>Thank you for joining our platform. We are excited to have you on board.</p><p>Best regards,<br>The Team</p>',
                    'ar' => '<h1>مرحباً {{user_name}}!</h1><p>شكراً لك على الانضمام إلى منصتنا. نحن متحمسون لوجودك معنا.</p><p>مع أطيب التحيات،<br>الفريق</p>',
                    'ku' => '<h1>بەخێربێیت {{user_name}}!</h1><p>سوپاس بۆ بەشداریکردنت لە پلاتفۆرمەکەمان. ئێمە زۆر دڵخۆشین کە لەگەڵمانیت.</p><p>بە باشترین شێوە،<br>تیمەکە</p>',
                ],
                'purpose' => EmailTemplatePurpose::WELCOME,
                'is_default' => true,
                'variables' => ['user_name', 'user_email'],
            ],
            [
                'code' => 'PASSWORD_RESET_001',
                'name' => [
                    'en' => 'Password Reset',
                    'ar' => 'إعادة تعيين كلمة المرور',
                    'ku' => 'دووبارەکردنەوەی تێپەڕەوشە',
                ],
                'subject' => [
                    'en' => 'Reset Your Password',
                    'ar' => 'إعادة تعيين كلمة المرور',
                    'ku' => 'تێپەڕەوشەکەت دووبارە بکەرەوە',
                ],
                'body' => [
                    'en' => '<p>Hello {{user_name}},</p><p>You requested to reset your password. Click the link below to reset it:</p><p><a href="{{reset_link}}">Reset Password</a></p><p>This link will expire in {{expiry_time}} minutes.</p>',
                    'ar' => '<p>مرحباً {{user_name}},</p><p>لقد طلبت إعادة تعيين كلمة المرور. انقر على الرابط أدناه لإعادة تعيينها:</p><p><a href="{{reset_link}}">إعادة تعيين كلمة المرور</a></p><p>سينتهي هذا الرابط في {{expiry_time}} دقيقة.</p>',
                    'ku' => '<p>سڵاو {{user_name}},</p><p>تۆ داوات لە دووبارەکردنەوەی تێپەڕەوشەکەت. کلیک لەسەر بەستەرەکەی خوارەوە بکە بۆ دووبارەکردنەوەی:</p><p><a href="{{reset_link}}">دووبارەکردنەوەی تێپەڕەوشە</a></p><p>ئەم بەستەرە لە {{expiry_time}} خولەکدا دەبێتە بەکارنەهاتوو.</p>',
                ],
                'purpose' => EmailTemplatePurpose::PASSWORD_RESET,
                'is_default' => true,
                'variables' => ['user_name', 'reset_link', 'expiry_time'],
            ],
            [
                'code' => 'EMAIL_VERIFICATION_001',
                'name' => [
                    'en' => 'Email Verification',
                    'ar' => 'التحقق من البريد الإلكتروني',
                    'ku' => 'پشتڕاستکردنەوەی ئیمەیڵ',
                ],
                'subject' => [
                    'en' => 'Verify Your Email Address',
                    'ar' => 'تحقق من عنوان بريدك الإلكتروني',
                    'ku' => 'ناونیشانی ئیمەیڵەکەت پشتڕاست بکەرەوە',
                ],
                'body' => [
                    'en' => '<p>Hello {{user_name}},</p><p>Please verify your email address by clicking the link below:</p><p><a href="{{verification_link}}">Verify Email</a></p>',
                    'ar' => '<p>مرحباً {{user_name}},</p><p>يرجى التحقق من عنوان بريدك الإلكتروني بالنقر على الرابط أدناه:</p><p><a href="{{verification_link}}">التحقق من البريد الإلكتروني</a></p>',
                    'ku' => '<p>سڵاو {{user_name}},</p><p>تکایە ناونیشانی ئیمەیڵەکەت پشتڕاست بکەرەوە بە کلیککردن لەسەر بەستەرەکەی خوارەوە:</p><p><a href="{{verification_link}}">پشتڕاستکردنەوەی ئیمەیڵ</a></p>',
                ],
                'purpose' => EmailTemplatePurpose::EMAIL_VERIFICATION,
                'is_default' => true,
                'variables' => ['user_name', 'verification_link'],
            ],
            [
                'code' => 'DONATION_RECEIPT_001',
                'name' => [
                    'en' => 'Donation Receipt',
                    'ar' => 'إيصال التبرع',
                    'ku' => 'وەسڵی بەخشین',
                ],
                'subject' => [
                    'en' => 'Thank You for Your Donation',
                    'ar' => 'شكراً لك على تبرعك',
                    'ku' => 'سوپاس بۆ بەخشینەکەت',
                ],
                'body' => [
                    'en' => '<h1>Thank You {{donor_name}}!</h1><p>We received your donation of {{amount}} {{currency}} for {{campaign_name}}.</p><p>Transaction Reference: {{transaction_ref}}</p><p>Date: {{donation_date}}</p><p>Your generosity makes a difference!</p>',
                    'ar' => '<h1>شكراً لك {{donor_name}}!</h1><p>لقد استلمنا تبرعك بقيمة {{amount}} {{currency}} للحملة {{campaign_name}}.</p><p>رقم المرجع: {{transaction_ref}}</p><p>التاريخ: {{donation_date}}</p><p>كرمك يحدث فرقاً!</p>',
                    'ku' => '<h1>سوپاس {{donor_name}}!</h1><p>بەخشینەکەت بە بڕی {{amount}} {{currency}} بۆ {{campaign_name}} وەرگرتیم.</p><p>ژمارەی پەیوەندی: {{transaction_ref}}</p><p>بەروار: {{donation_date}}</p><p>بەخشندەیی تۆ جیاوازی دروست دەکات!</p>',
                ],
                'purpose' => EmailTemplatePurpose::DONATION_RECEIPT,
                'is_default' => true,
                'variables' => ['donor_name', 'amount', 'currency', 'campaign_name', 'transaction_ref', 'donation_date'],
            ],
            [
                'code' => 'CAMPAIGN_UPDATE_001',
                'name' => [
                    'en' => 'Campaign Update',
                    'ar' => 'تحديث الحملة',
                    'ku' => 'نوێکردنەوەی هەوڵ',
                ],
                'subject' => [
                    'en' => 'Update on {{campaign_name}}',
                    'ar' => 'تحديث حول {{campaign_name}}',
                    'ku' => 'نوێکردنەوە لەسەر {{campaign_name}}',
                ],
                'body' => [
                    'en' => '<h2>Campaign Update</h2><p>Dear {{donor_name}},</p><p>We wanted to update you on the progress of {{campaign_name}}.</p><p>Current Progress: {{progress_percentage}}%</p><p>Amount Raised: {{raised_amount}} {{currency}}</p><p>Thank you for your continued support!</p>',
                    'ar' => '<h2>تحديث الحملة</h2><p>عزيزي {{donor_name}},</p><p>أردنا إطلاعك على تقدم {{campaign_name}}.</p><p>التقدم الحالي: {{progress_percentage}}%</p><p>المبلغ المجموع: {{raised_amount}} {{currency}}</p><p>شكراً لدعمك المستمر!</p>',
                    'ku' => '<h2>نوێکردنەوەی هەوڵ</h2><p>{{donor_name}}ی گران,</p><p>دەمانویست تۆ ئاگادار بکەینەوە لە پێشکەوتنی {{campaign_name}}.</p><p>پێشکەوتنی ئێستا: {{progress_percentage}}%</p><p>بڕی کۆکراوە: {{raised_amount}} {{currency}}</p><p>سوپاس بۆ پشتگیری بەردەوامەکەت!</p>',
                ],
                'purpose' => EmailTemplatePurpose::CAMPAIGN_UPDATE,
                'is_default' => true,
                'variables' => ['donor_name', 'campaign_name', 'progress_percentage', 'raised_amount', 'currency'],
            ],
            [
                'code' => 'TRANSACTION_CONFIRMATION_001',
                'name' => [
                    'en' => 'Transaction Confirmation',
                    'ar' => 'تأكيد المعاملة',
                    'ku' => 'دڵنیاکردنەوەی مامەڵە',
                ],
                'subject' => [
                    'en' => 'Transaction Confirmation - {{transaction_ref}}',
                    'ar' => 'تأكيد المعاملة - {{transaction_ref}}',
                    'ku' => 'دڵنیاکردنەوەی مامەڵە - {{transaction_ref}}',
                ],
                'body' => [
                    'en' => '<p>Hello {{user_name}},</p><p>Your transaction has been confirmed.</p><p>Transaction Reference: {{transaction_ref}}</p><p>Amount: {{amount}} {{currency}}</p><p>Status: {{status}}</p><p>Date: {{transaction_date}}</p>',
                    'ar' => '<p>مرحباً {{user_name}},</p><p>تم تأكيد معاملتك.</p><p>رقم المرجع: {{transaction_ref}}</p><p>المبلغ: {{amount}} {{currency}}</p><p>الحالة: {{status}}</p><p>التاريخ: {{transaction_date}}</p>',
                    'ku' => '<p>سڵاو {{user_name}},</p><p>مامەڵەکەت دڵنیاکرایەوە.</p><p>ژمارەی پەیوەندی: {{transaction_ref}}</p><p>بڕ: {{amount}} {{currency}}</p><p>دۆخ: {{status}}</p><p>بەروار: {{transaction_date}}</p>',
                ],
                'purpose' => EmailTemplatePurpose::TRANSACTION_CONFIRMATION,
                'is_default' => true,
                'variables' => ['user_name', 'transaction_ref', 'amount', 'currency', 'status', 'transaction_date'],
            ],
            [
                'code' => 'EMAIL_OTP_001',
                'name' => [
                    'en' => 'Email Verification Code',
                    'ar' => 'رمز التحقق',
                    'ku' => 'کۆدی پشتڕاستکردنەوە',
                ],
                'subject' => [
                    'en' => 'Your Verification Code',
                    'ar' => 'رمز التحقق الخاص بك',
                    'ku' => 'کۆدی پشتڕاستکردنەوەکەت',
                ],
                'body' => [
                    'en' => '<h2>Email Verification Code</h2><p>Hello {{user_name}},</p><p>Your email verification code is:</p><div style="text-align: center; margin: 30px 0;"><div style="display: inline-block; background: #f3f4f6; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">{{otp_code}}</div></div><p>This code will expire in {{expiry_minutes}} minutes.</p><p style="margin-top: 30px; font-size: 0.9em; color: #777;">If you did not request this code, please ignore this email.</p>',
                    'ar' => '<h2>رمز التحقق من البريد الإلكتروني</h2><p>مرحباً {{user_name}},</p><p>رمز التحقق من بريدك الإلكتروني هو:</p><div style="text-align: center; margin: 30px 0;"><div style="display: inline-block; background: #f3f4f6; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">{{otp_code}}</div></div><p>سينتهي هذا الرمز في {{expiry_minutes}} دقيقة.</p><p style="margin-top: 30px; font-size: 0.9em; color: #777;">إذا لم تطلب هذا الرمز، يرجى تجاهل هذا البريد الإلكتروني.</p>',
                    'ku' => '<h2>کۆدی پشتڕاستکردنەوەی ئیمەیڵ</h2><p>سڵاو {{user_name}},</p><p>کۆدی پشتڕاستکردنەوەی ئیمەیڵەکەت:</p><div style="text-align: center; margin: 30px 0;"><div style="display: inline-block; background: #f3f4f6; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">{{otp_code}}</div></div><p>ئەم کۆدە لە {{expiry_minutes}} خولەکدا دەبێتە بەکارنەهاتوو.</p><p style="margin-top: 30px; font-size: 0.9em; color: #777;">ئەگەر تۆ ئەم کۆدەت داوە نەکردووە، تکایە ئەم ئیمەیڵە پشتگوێ بخە.</p>',
                ],
                'purpose' => EmailTemplatePurpose::EMAIL_OTP,
                'is_default' => true,
                'variables' => ['user_name', 'otp_code', 'expiry_minutes'],
            ],
            [
                'code' => 'PASSWORD_RESET_OTP_001',
                'name' => [
                    'en' => 'Password Reset Code',
                    'ar' => 'رمز إعادة تعيين كلمة المرور',
                    'ku' => 'کۆدی دووبارەکردنەوەی تێپەڕەوشە',
                ],
                'subject' => [
                    'en' => 'Your Password Reset Code',
                    'ar' => 'رمز إعادة تعيين كلمة المرور',
                    'ku' => 'کۆدی دووبارەکردنەوەی تێپەڕەوشەکەت',
                ],
                'body' => [
                    'en' => '<h2>Password Reset Code</h2><p>Hello {{user_name}},</p><p>You requested to reset your password. Your password reset code is:</p><div style="text-align: center; margin: 30px 0;"><div style="display: inline-block; background: #f3f4f6; border: 2px solid #f59e0b; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #f59e0b;">{{otp_code}}</div></div><p>This code will expire in {{expiry_minutes}} minutes.</p><p style="margin-top: 30px; font-size: 0.9em; color: #777;">If you did not request a password reset, please ignore this email and your password will remain unchanged.</p>',
                    'ar' => '<h2>رمز إعادة تعيين كلمة المرور</h2><p>مرحباً {{user_name}},</p><p>لقد طلبت إعادة تعيين كلمة المرور. رمز إعادة تعيين كلمة المرور الخاص بك هو:</p><div style="text-align: center; margin: 30px 0;"><div style="display: inline-block; background: #f3f4f6; border: 2px solid #f59e0b; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #f59e0b;">{{otp_code}}</div></div><p>سينتهي هذا الرمز في {{expiry_minutes}} دقيقة.</p><p style="margin-top: 30px; font-size: 0.9em; color: #777;">إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد الإلكتروني وستبقى كلمة المرور الخاصة بك كما هي.</p>',
                    'ku' => '<h2>کۆدی دووبارەکردنەوەی تێپەڕەوشە</h2><p>سڵاو {{user_name}},</p><p>تۆ داوات لە دووبارەکردنەوەی تێپەڕەوشەکەت. کۆدی دووبارەکردنەوەی تێپەڕەوشەکەت:</p><div style="text-align: center; margin: 30px 0;"><div style="display: inline-block; background: #f3f4f6; border: 2px solid #f59e0b; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #f59e0b;">{{otp_code}}</div></div><p>ئەم کۆدە لە {{expiry_minutes}} خولەکدا دەبێتە بەکارنەهاتوو.</p><p style="margin-top: 30px; font-size: 0.9em; color: #777;">ئەگەر تۆ داوات لە دووبارەکردنەوەی تێپەڕەوشە نەکردووە، تکایە ئەم ئیمەیڵە پشتگوێ بخە و تێپەڕەوشەکەت وەک خۆی دەمێنێتەوە.</p>',
                ],
                'purpose' => EmailTemplatePurpose::PASSWORD_RESET_OTP,
                'is_default' => true,
                'variables' => ['user_name', 'otp_code', 'expiry_minutes'],
            ],
        ];

        foreach ($templates as $templateData) {
            // Check if template exists by code
            if (EmailTemplate::where('code', $templateData['code'])->exists()) {
                continue;
            }

            EmailTemplate::create($templateData);
        }
    }
}
