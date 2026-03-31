<?php

namespace Database\Seeders;

use App\Enums\PaymentGatewayType;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        PaymentGateway::updateOrCreate(
            ['code' => 'zaincash'],
            [
                'name' => ['ar' => 'زين كاش', 'en' => 'ZainCash', 'ku' => 'زەین کەش'],
                'description' => [
                    'ar' => 'بوابة دفع زين كاش لشراء الدورات',
                    'en' => 'ZainCash gateway for course checkout',
                    'ku' => 'دەروازەی زەین کەش بۆ کڕینی کۆرس',
                ],
                'type' => PaymentGatewayType::MOBILE_WALLET,
                'processing_fee_percentage' => 0,
                'processing_fee_fixed' => 0,
                'configuration' => [],
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
