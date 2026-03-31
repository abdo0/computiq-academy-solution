<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentGatewayType;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\Currency;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurrencyConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_currency_helpers_fall_back_to_iqd_when_no_currency_exists(): void
    {
        Currency::query()->delete();
        Currency::clearCurrencyCache();

        $this->assertNull(Currency::getDefault());
        $this->assertSame('IQD', Currency::getDefaultCode());
        $this->assertSame('د.ع', Currency::getDefaultSymbol());
        $this->assertSame([
            'code' => 'IQD',
            'symbol' => 'د.ع',
            'name' => 'Iraqi Dinar',
        ], Currency::getDefaultCurrencyData());
    }

    public function test_settings_api_uses_default_currency_from_currencies_table(): void
    {
        Setting::set('Currency', 'USD');
        Setting::set('Currency symbol', '$');

        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.currency.code', 'EUR')
            ->assertJsonPath('data.currency.symbol', '€')
            ->assertJsonPath('data.currency.name', 'Euro');
    }

    public function test_switching_default_currency_updates_settings_and_checkout_quote_payloads(): void
    {
        $iqd = Currency::create([
            'code' => 'IQD',
            'name' => 'Iraqi Dinar',
            'symbol' => 'د.ع',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $eur = Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->assertSame('IQD', Currency::getDefaultCode());

        $eur->update([
            'is_default' => true,
            'is_active' => false,
        ]);

        $iqd->refresh();
        $eur->refresh();

        $this->assertFalse($iqd->is_default);
        $this->assertTrue($eur->is_default);
        $this->assertTrue($eur->is_active);
        $this->assertSame(1, Currency::query()->where('is_default', true)->count());

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.currency.code', 'EUR')
            ->assertJsonPath('data.currency.symbol', '€');

        $user = User::create([
            'name' => 'Currency Student',
            'email' => 'currency-student@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'locale' => 'en',
        ]);

        $gateway = PaymentGateway::create([
            'code' => 'zaincash',
            'name' => ['en' => 'ZainCash', 'ar' => 'زين كاش', 'ku' => 'زەین کەش'],
            'description' => ['en' => 'Gateway', 'ar' => 'بوابة', 'ku' => 'دەروازە'],
            'type' => PaymentGatewayType::MOBILE_WALLET,
            'is_active' => true,
        ]);

        $course = Course::create([
            'title' => ['en' => 'Currency Course', 'ar' => 'دورة العملة', 'ku' => 'کۆرسی دراو'],
            'slug' => 'currency-course',
            'short_description' => ['en' => 'Short', 'ar' => 'قصير', 'ku' => 'کورت'],
            'description' => ['en' => 'Description', 'ar' => 'وصف', 'ku' => 'وەسف'],
            'price' => 150,
            'is_active' => true,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'price' => 150,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/checkout/quote', [
            'payment_gateway_id' => $gateway->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.currency.code', 'EUR')
            ->assertJsonPath('data.currency.symbol', '€');
    }
}
