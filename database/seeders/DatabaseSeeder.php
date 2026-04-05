<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Model::unguard();

        // Run the seeders in order
        $this->call([
            // System seeders
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,

            // Basic entities
            CurrencySeeder::class,
            CountrySeeder::class,
            StateSeeder::class,
            PaymentMethodSeeder::class,
            PaymentGatewaySeeder::class,
            PromoCodeSeeder::class,


            // Content Management seeders
            ArticleCategorySeeder::class,
            ArticleSeeder::class,
            PageSeeder::class,
            FAQSeeder::class,
            ContactMessageSeeder::class,
            TestimonialSeeder::class,
            SectionSeeder::class,

            // Template seeders
            EmailTemplateSeeder::class,
            SmsTemplateSeeder::class,

            // Settings seeder
            SettingSeeder::class,

            // Course & Instructor seeders
            InstructorSeeder::class,
            CourseSeeder::class,
            CourseCurriculumSeeder::class,
            CourseCertificateTemplateSeeder::class,
            CoursePurchaseSeeder::class,
            CourseReviewSeeder::class,
            LearningPathSeeder::class,
        ]);
    }
}
