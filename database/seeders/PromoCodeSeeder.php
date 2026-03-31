<?php

namespace Database\Seeders;

use App\Enums\PromoCodeDiscountType;
use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::updateOrCreate(
            ['code' => 'SAVE10'],
            [
                'discount_type' => PromoCodeDiscountType::PERCENTAGE,
                'discount_value' => 10,
                'starts_at' => null,
                'expires_at' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'is_active' => true,
            ]
        );
    }
}
