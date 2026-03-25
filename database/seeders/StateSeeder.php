<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            [
                'country_code' => 'IQ',
                'code' => 'BG',
                'sort_order' => 1,
                'name' => [
                    'en' => 'Baghdad',
                    'ar' => 'بغداد',
                    'ku' => 'بەغداد',
                ],
            ],
            [
                'country_code' => 'IQ',
                'code' => 'ER',
                'sort_order' => 2,
                'name' => [
                    'en' => 'Erbil',
                    'ar' => 'أربيل',
                    'ku' => 'هه‌ولێر',
                ],
            ],
            [
                'country_code' => 'IQ',
                'code' => 'BS',
                'sort_order' => 3,
                'name' => [
                    'en' => 'Basra',
                    'ar' => 'البصرة',
                    'ku' => 'بەصرە',
                ],
            ],
            [
                'country_code' => 'JO',
                'code' => 'AM',
                'sort_order' => 1,
                'name' => [
                    'en' => 'Amman',
                    'ar' => 'عمّان',
                    'ku' => 'عمان',
                ],
            ],
            [
                'country_code' => 'TR',
                'code' => 'IST',
                'sort_order' => 1,
                'name' => [
                    'en' => 'Istanbul',
                    'ar' => 'إسطنبول',
                    'ku' => 'ئێستنبول',
                ],
            ],
        ];

        foreach ($states as $state) {
            $countryId = Country::where('code', $state['country_code'])->value('id');

            if (! $countryId) {
                continue;
            }

            State::updateOrCreate(
                [
                    'country_id' => $countryId,
                    'code' => $state['code'],
                ],
                [
                    'name' => $state['name'],
                    'sort_order' => $state['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
