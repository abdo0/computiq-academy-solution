<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settingsData = [
            // =================================================================
            // Application Settings
            // =================================================================
            [
                'key' => 'app_name',
                'value' => 'Computiq',
                'helper_text' => 'The name of the application',
                'default' => 'Computiq',
            ],
            [
                'key' => 'app_description',
                'value' => 'Technology Solutions & Education',
                'helper_text' => 'Short description of the application',
                'default' => 'Technology Solutions & Education',
            ],
            [
                'key' => 'app_locales',
                'value' => ['en', 'ar', 'ku'],
                'helper_text' => 'Available languages (comma-separated)',
                'default' => 'en,ar,ku',
            ],
            [
                'key' => 'default_locale',
                'value' => 'en',
                'helper_text' => 'Default language',
                'default' => 'en',
            ],
            [
                'key' => 'timezone',
                'value' => 'Asia/Baghdad',
                'helper_text' => 'Application timezone',
                'default' => 'Asia/Baghdad',
            ],
            [
                'key' => 'currency',
                'value' => 'IQD',
                'helper_text' => 'Default currency',
                'default' => 'IQD',
            ],
            [
                'key' => 'hold_period_days',
                'value' => '0',
                'helper_text' => 'Number of days to hold donation funds before they become available for withdrawal (0 = no hold period)',
                'default' => '0',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'helper_text' => 'Date format',
                'default' => 'Y-m-d',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'helper_text' => 'Time format',
                'default' => 'H:i:s',
            ],
            [
                'key' => 'items_per_page',
                'value' => '25',
                'helper_text' => 'Number of items per page in tables',
                'default' => '25',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'helper_text' => 'Enable maintenance mode',
                'default' => 'false',
            ],

            // =================================================================
            // Company About Information (Multilingual)
            // =================================================================
            [
                'key' => 'company_name',
                'value' => [
                    'en' => 'Computiq',
                    'ar' => 'كمبيوتيك',
                    'ku' => 'کۆمپیوتیک',
                ],
                'helper_text' => 'Full legal company name',
                'default' => 'Computiq',
            ],
            [
                'key' => 'company_slogan',
                'value' => [
                    'en' => 'Technology Solutions & Education',
                    'ar' => 'الحلول التقنية والتعليم',
                    'ku' => 'چارەسەرە تەکنیکی و پەروەردە',
                ],
                'helper_text' => 'Company tagline or slogan',
                'default' => 'Technology Solutions & Education',
            ],
            [
                'key' => 'company_description',
                'value' => [
                    'en' => 'Computiq, an Iraqi-registered company, is a prominent technology business offering technical solutions and education.',
                    'ar' => 'كمبيوتيك شركة تقنية رائدة تقدم خدمتين رئيسيتين: الحلول التقنية والتعليم.',
                    'ku' => 'کۆمپیوتیک کۆمپانیایەکی تەکنەلۆژیای پێشەنگە دوو خزمەتگوزاری سەرەکی پێشکەش دەکات: چارەسەرە تەکنیکی و پەروەردە.',
                ],
                'helper_text' => 'Full company description',
                'default' => 'Technology solutions and education business.',
            ],
            [
                'key' => 'company_registration_no',
                'value' => '',
                'helper_text' => 'Commercial registration number',
                'default' => '',
            ],
            [
                'key' => 'company_tax_id',
                'value' => '',
                'helper_text' => 'Tax identification number',
                'default' => '',
            ],
            [
                'key' => 'company_founded_year',
                'value' => '2025',
                'helper_text' => 'Year company was established',
                'default' => '2025',
            ],
            [
                'key' => 'company_logo',
                'value' => '',
                'helper_text' => 'Path to company logo image',
                'default' => '/images/logo.png',
            ],
            [
                'key' => 'company_favicon',
                'value' => '',
                'helper_text' => 'Path to favicon',
                'default' => '/favicon.ico',
            ],

            // =================================================================
            // Contact Information
            // =================================================================
            [
                'key' => 'contact_email',
                'value' => 'info@computiq.tech',
                'helper_text' => 'Primary contact email',
                'default' => 'info@computiq.tech',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+964 776 677 0066',
                'helper_text' => 'Primary contact phone number',
                'default' => '+964 776 677 0066',
            ],
            [
                'key' => 'contact_phone_secondary',
                'value' => '',
                'helper_text' => 'Secondary contact phone number',
                'default' => '',
            ],
            [
                'key' => 'contact_whatsapp',
                'value' => '+964 750 123 4567',
                'helper_text' => 'WhatsApp number',
                'default' => '',
            ],
            [
                'key' => 'contact_fax',
                'value' => '',
                'helper_text' => 'Fax number',
                'default' => '',
            ],

            // =================================================================
            // Location Information
            // =================================================================
            [
                'key' => 'location_address',
                'value' => 'Al-Karrada',
                'helper_text' => 'Full street address',
                'default' => '',
            ],
            [
                'key' => 'location_city',
                'value' => 'Baghdad',
                'helper_text' => 'City',
                'default' => 'Baghdad',
            ],
            [
                'key' => 'location_state',
                'value' => 'Kurdistan Region',
                'helper_text' => 'State or province',
                'default' => 'Kurdistan Region',
            ],
            [
                'key' => 'location_country',
                'value' => 'Iraq',
                'helper_text' => 'Country',
                'default' => 'Iraq',
            ],
            [
                'key' => 'location_postal_code',
                'value' => '44001',
                'helper_text' => 'Postal/ZIP code',
                'default' => '',
            ],
            [
                'key' => 'location_latitude',
                'value' => '33.314244',
                'helper_text' => 'Latitude coordinate for maps',
                'default' => '33.314244',
            ],
            [
                'key' => 'location_longitude',
                'value' => '44.422855',
                'helper_text' => 'Longitude coordinate for maps',
                'default' => '44.422855',
            ],
            [
                'key' => 'location_map_url',
                'value' => 'https://maps.google.com/?q=36.191113,44.009167',
                'helper_text' => 'Google Maps URL or embedded map URL',
                'default' => '',
            ],
            [
                'key' => 'location_map_embed',
                'value' => '',
                'helper_text' => 'Google Maps embed iframe code',
                'default' => '',
            ],

            // =================================================================
            // Social Media & Web Presence
            // =================================================================
            [
                'key' => 'social_website',
                'value' => 'https://www.computiq.tech',
                'helper_text' => 'Company website URL',
                'default' => '',
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/nakhwaa',
                'helper_text' => 'Facebook page URL',
                'default' => '',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/nakhwaa',
                'helper_text' => 'Instagram profile URL',
                'default' => '',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/nakhwaa',
                'helper_text' => 'Twitter/X profile URL',
                'default' => '',
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/nakhwaa',
                'helper_text' => 'LinkedIn company page URL',
                'default' => '',
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/@nakhwaa',
                'helper_text' => 'YouTube channel URL',
                'default' => '',
            ],
            [
                'key' => 'social_tiktok',
                'value' => 'https://tiktok.com/@nakhwaa',
                'helper_text' => 'TikTok profile URL',
                'default' => '',
            ],

            // =================================================================
            // Business Hours
            // =================================================================
            [
                'key' => 'business_hours_weekdays',
                'value' => '9:00 AM - 5:00 PM',
                'helper_text' => 'Working hours (Sunday to Thursday)',
                'default' => '9:00 AM - 5:00 PM',
            ],
            [
                'key' => 'business_hours_weekend',
                'value' => 'Closed',
                'helper_text' => 'Weekend hours (Friday-Saturday)',
                'default' => 'Closed',
            ],
            [
                'key' => 'business_days',
                'value' => 'Sunday,Monday,Tuesday,Wednesday,Thursday',
                'helper_text' => 'Working days (comma-separated)',
                'default' => 'Sunday,Monday,Tuesday,Wednesday,Thursday',
            ],

            // =================================================================
            // SEO & Meta Information (Multilingual)
            // =================================================================
            [
                'key' => 'meta_title',
                'value' => [
                    'en' => 'Computiq - Tech & Education',
                    'ar' => 'كمبيوتيك - تقنية وتعليم',
                    'ku' => 'کۆمپیوتیک - تەکنەلۆژیا و پەروەردە',
                ],
                'helper_text' => 'SEO meta title',
                'default' => '',
            ],
            [
                'key' => 'meta_description',
                'value' => [
                    'en' => 'Computiq is a prominent technology business offering technical solutions and education.',
                    'ar' => 'كمبيوتيك شركة تقنية رائدة تقدم الحلول التقنية والتعليم.',
                    'ku' => 'کۆمپیوتیک کۆمپانیایەکی تەکنەلۆژیای پێشەنگە کە چارەسەری تەکنیکی و پەروەردە پێشکەش دەکات.',
                ],
                'helper_text' => 'SEO meta description',
                'default' => '',
            ],
            [
                'key' => 'meta_keywords',
                'value' => [
                    'en' => 'technology, solutions, education, iraq, baghdad',
                    'ar' => 'كمبيوتيك، حلول، تقنية، تعليم، العراق، بغداد',
                    'ku' => 'کۆمپیوتیک، چارەسەر، تەکنەلۆژیا، پەروەردە، عێراق، بەغدا',
                ],
                'helper_text' => 'SEO keywords (comma-separated)',
                'default' => '',
            ],

            // =================================================================
            // Email Configuration
            // =================================================================
            [
                'key' => 'email_from_address',
                'value' => 'noreply@computiq.tech',
                'helper_text' => 'Default "from" email address',
                'default' => 'noreply@computiq.tech',
            ],
            [
                'key' => 'email_from_name',
                'value' => 'Computiq Academy',
                'helper_text' => 'Default "from" name',
                'default' => 'Computiq Academy',
            ],
            [
                'key' => 'email_signature',
                'value' => 'Best Regards,<br>Computiq<br>Technology Solutions & Education',
                'helper_text' => 'Email signature template',
                'default' => '',
            ],

            // =================================================================
            // Features & Modules
            // =================================================================
            [
                'key' => 'enable_chat',
                'value' => 'false',
                'helper_text' => 'Enable internal chat system',
                'default' => 'false',
            ],
            [
                'key' => 'enable_notifications',
                'value' => 'true',
                'helper_text' => 'Enable system notifications',
                'default' => 'true',
            ],
            [
                'key' => 'enable_activity_log',
                'value' => 'true',
                'helper_text' => 'Enable activity logging',
                'default' => 'true',
            ],

            // =================================================================
            // WhatsApp Integration (UltraMsg)
            // =================================================================
            [
                'key' => 'ultramsg_instance_id',
                'value' => env('ULTRAMSG_DEFAULT_INSTANCE_ID', ''),
                'helper_text' => 'UltraMsg Instance ID for WhatsApp integration',
                'default' => '',
            ],
            [
                'key' => 'ultramsg_token',
                'value' => env('ULTRAMSG_DEFAULT_TOKEN', ''),
                'helper_text' => 'UltraMsg Token for WhatsApp integration',
                'default' => '',
            ],
            [
                'key' => 'ultramsg_test_phone_number',
                'value' => env('ULTRAMSG_TEST_PHONE_NUMBER', ''),
                'helper_text' => 'Test phone number for WhatsApp messages in test mode',
                'default' => '',
            ],
            [
                'key' => 'ultramsg_test_mode',
                'value' => env('ULTRAMSG_TEST_MODE', 'true'),
                'helper_text' => 'Enable test mode for WhatsApp messages (routes all messages to test number)',
                'default' => 'true',
            ],
            [
                'key' => 'ultramsg_log_requests',
                'value' => env('ULTRAMSG_LOG_REQUESTS', 'true'),
                'helper_text' => 'Enable logging of WhatsApp API requests',
                'default' => 'true',
            ],
            [
                'key' => 'ultramsg_log_responses',
                'value' => env('ULTRAMSG_LOG_RESPONSES', 'true'),
                'helper_text' => 'Enable logging of WhatsApp API responses',
                'default' => 'true',
            ],
            // ---- Frontend Site Identity ----
            ['key' => 'site_name_ar',     'value' => 'كمبيوتيك',   'helper_text' => 'Site name in Arabic',  'default' => ''],
            ['key' => 'site_name_en',     'value' => 'Computiq', 'helper_text' => 'Site name in English', 'default' => ''],
            ['key' => 'site_name_ku',     'value' => 'کۆمپیوتیک',   'helper_text' => 'Site name in Kurdish', 'default' => ''],
            // ---- Frontend Footer ----
            ['key' => 'footer_desc_ar',   'value' => 'كمبيوتيك شركة تقنية رائدة تقدم خدمتين رئيسيتين: الحلول التقنية والتعليم.', 'helper_text' => 'Footer description (AR)', 'default' => ''],
            ['key' => 'footer_desc_en',   'value' => 'Computiq is a prominent technology business offering technical solutions and education.', 'helper_text' => 'Footer description (EN)', 'default' => ''],
            ['key' => 'footer_desc_ku',   'value' => 'کۆمپیوتیک کۆمپانیایەکی تەکنەلۆژیای پێشەنگە دوو خزمەتگوزاری سەرەکی پێشکەش دەکات: چارەسەرە تەکنیکی و پەروەردە.', 'helper_text' => 'Footer description (KU)', 'default' => ''],
            // ---- Address ----
            ['key' => 'address_ar',       'value' => 'الكرادة، بغداد، العراق',          'helper_text' => 'Address (AR)', 'default' => ''],
            ['key' => 'address_en',       'value' => 'Al-Karrada, Baghdad, Iraq',           'helper_text' => 'Address (EN)', 'default' => ''],
            ['key' => 'address_ku',       'value' => 'کەرادە، بەغدا، عێراق',         'helper_text' => 'Address (KU)', 'default' => ''],
            // ---- Contact flat keys for API ----
            ['key' => 'contact_phone_1',  'value' => '+964 776 677 0066', 'helper_text' => 'Primary phone',   'default' => ''],
            ['key' => 'contact_phone_2',  'value' => '',                   'helper_text' => 'Secondary phone', 'default' => ''],
            // ---- Default SEO ----
            ['key' => 'seo_title_ar',       'value' => 'كمبيوتيك - الحلول التقنية والتعليم',                    'helper_text' => 'Default SEO title (AR)',       'default' => ''],
            ['key' => 'seo_title_en',       'value' => 'Computiq - Technology Solutions & Education',              'helper_text' => 'Default SEO title (EN)',       'default' => ''],
            ['key' => 'seo_title_ku',       'value' => 'کۆمپیوتیک - چارەسەری تەکنیکی و پەروەردە',                'helper_text' => 'Default SEO title (KU)',       'default' => ''],
            ['key' => 'seo_description_ar', 'value' => 'كمبيوتيك شركة تقنية رائدة',                 'helper_text' => 'Default SEO description (AR)', 'default' => ''],
            ['key' => 'seo_description_en', 'value' => 'A prominent technology solutions business',   'helper_text' => 'Default SEO description (EN)', 'default' => ''],
            ['key' => 'seo_description_ku', 'value' => 'کۆمپانیایەکی تەکنەلۆژیای پێشەنگ', 'helper_text' => 'Default SEO description (KU)', 'default' => ''],
            ['key' => 'seo_keywords_ar',    'value' => 'تقنية,تعليم,كمبيوتيك',                       'helper_text' => 'Default SEO keywords (AR)',    'default' => ''],
            ['key' => 'seo_keywords_en',    'value' => 'technology,education,computiq',                 'helper_text' => 'Default SEO keywords (EN)',    'default' => ''],
            ['key' => 'seo_keywords_ku',    'value' => 'تەکنەلۆژیا,پەروەردە,کۆمپیوتیک',                   'helper_text' => 'Default SEO keywords (KU)',    'default' => ''],
            ['key' => 'seo_og_image',       'value' => '',                                          'helper_text' => 'Default OG image URL',         'default' => ''],
            // ---- Hero Section ----
            ['key' => 'hero_title_ar',    'value' => 'شركة تقنية وبدائل تعليمية رائدة',                           'helper_text' => 'Hero title (AR)',    'default' => ''],
            ['key' => 'hero_title_en',    'value' => 'Prominent Technology & Education',                            'helper_text' => 'Hero title (EN)',    'default' => ''],
            ['key' => 'hero_title_ku',    'value' => 'تەکنەلۆجیای پێشەنگ و پەروەردە',                            'helper_text' => 'Hero title (KU)',    'default' => ''],
            ['key' => 'hero_subtitle_ar', 'value' => 'نقدم خدمتين رئيسيتين: الحلول التقنية والتعليم لبناء جيل جديد من المهارات.',  'helper_text' => 'Hero subtitle (AR)', 'default' => ''],
            ['key' => 'hero_subtitle_en', 'value' => 'Offering two primary services: technical solutions and education to mainstream digital skills.',  'helper_text' => 'Hero subtitle (EN)', 'default' => ''],
            ['key' => 'hero_subtitle_ku', 'value' => 'دوو خزمەتگوزاری سەرەکی پێشکەش دەکات: چارەسەرە تەکنیکی و پەروەردە.', 'helper_text' => 'Hero subtitle (KU)', 'default' => ''],
            ['key' => 'hero_perks_ar',    'value' => 'حلول تقنية • تعليم رقمي متقدم',       'helper_text' => 'Hero perks text (AR)',      'default' => ''],
            ['key' => 'hero_perks_en',    'value' => 'Technical Solutions • Digital Education', 'helper_text' => 'Hero perks text (EN)', 'default' => ''],
            ['key' => 'hero_perks_ku',    'value' => 'چارەسەری تەکنیکی • پەروەردەی دیجیتاڵی', 'helper_text' => 'Hero perks text (KU)', 'default' => ''],
            ['key' => 'hero_cta_text_ar', 'value' => 'تصفح برامجنا',         'helper_text' => 'Hero CTA button text (AR)', 'default' => ''],
            ['key' => 'hero_cta_text_en', 'value' => 'Explore Our Programs',   'helper_text' => 'Hero CTA button text (EN)', 'default' => ''],
            ['key' => 'hero_cta_text_ku', 'value' => 'بەرنامەکانمان ببینە', 'helper_text' => 'Hero CTA button text (KU)', 'default' => ''],
            // ---- Hero Background Image ----
            ['key' => 'hero_background_image', 'value' => '', 'helper_text' => 'Hero section background image path (leave empty for default)', 'default' => ''],
            // ---- Refund ----
            ['key' => 'refund_max_days',  'value' => '0', 'helper_text' => 'Max days for refund requests (0 = disabled)', 'default' => '0'],
        ];

        foreach ($settingsData as $setting) {
            // JSON encode array values before inserting
            if (is_array($setting['value'])) {
                $setting['value'] = json_encode($setting['value']);
            }

            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        \Illuminate\Support\Facades\Cache::forget('settings_all');
    }
}
