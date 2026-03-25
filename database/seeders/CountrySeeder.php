<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'code' => 'IQ',
                'iso2' => 'IQ',
                'phone_code' => '964',
                'sort_order' => 1,
                'name' => [
                    'en' => 'Iraq',
                    'ar' => 'العراق',
                    'ku' => 'عێراق',
                ],
            ],
            [
                'code' => 'JO',
                'iso2' => 'JO',
                'phone_code' => '962',
                'sort_order' => 2,
                'name' => [
                    'en' => 'Jordan',
                    'ar' => 'الأردن',
                    'ku' => 'ئوردن',
                ],
            ],
            [
                'code' => 'TR',
                'iso2' => 'TR',
                'phone_code' => '90',
                'sort_order' => 3,
                'name' => [
                    'en' => 'Turkey',
                    'ar' => 'تركيا',
                    'ku' => 'توركیا',
                ],
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                [
                    'name' => $country['name'],
                    'iso2' => strtoupper($country['iso2']),
                    'phone_code' => $country['phone_code'],
                    'sort_order' => $country['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
