<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'card',
                'name' => ['ar' => 'بطاقة', 'en' => 'Card', 'ku' => 'کارت'],
                'description' => ['ar' => 'الدفع باستخدام البطاقة', 'en' => 'Card payment', 'ku' => 'پارەدان بە کارت'],
                'icon' => 'credit-card',
                'sort_order' => 1,
            ],
            [
                'code' => 'mobile_wallet',
                'name' => ['ar' => 'محفظة إلكترونية', 'en' => 'Mobile Wallet', 'ku' => 'جزدانەی مۆبایل'],
                'description' => ['ar' => 'الدفع باستخدام المحفظة', 'en' => 'Wallet payment', 'ku' => 'پارەدان بە جزدان'],
                'icon' => 'device-phone-mobile',
                'sort_order' => 2,
            ],
            [
                'code' => 'bank',
                'name' => ['ar' => 'تحويل بنكي', 'en' => 'Bank Transfer', 'ku' => 'گواستنەوەی بانکی'],
                'description' => ['ar' => 'الدفع البنكي', 'en' => 'Bank transfer', 'ku' => 'پارەدان بە بانک'],
                'icon' => 'building-library',
                'sort_order' => 3,
            ],
            [
                'code' => 'money_transfer',
                'name' => ['ar' => 'حوالة', 'en' => 'Money Transfer', 'ku' => 'حەواڵە'],
                'description' => ['ar' => 'خدمة تحويل الأموال', 'en' => 'Money transfer service', 'ku' => 'خزمەتی حەواڵە'],
                'icon' => 'arrows-right-left',
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                array_merge($method, ['is_active' => true])
            );
        }
    }
}
