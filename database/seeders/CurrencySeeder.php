<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_default' => false, 'is_active' => true, 'sort_order' => 1],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_default' => false, 'is_active' => true, 'sort_order' => 2],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'is_default' => false, 'is_active' => true, 'sort_order' => 3],
            ['code' => 'IQD', 'name' => 'Iraqi Dinar', 'symbol' => 'د.ع', 'is_default' => true, 'is_active' => true, 'sort_order' => 4],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'is_default' => false, 'is_active' => true, 'sort_order' => 5],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_default' => false, 'is_active' => true, 'sort_order' => 6],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'is_default' => false, 'is_active' => true, 'sort_order' => 7],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'is_default' => false, 'is_active' => true, 'sort_order' => 8],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'is_default' => false, 'is_active' => true, 'sort_order' => 9],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺', 'is_default' => false, 'is_active' => true, 'sort_order' => 10],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
