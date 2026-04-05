<?php

namespace Tests\Feature\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\PromoCodeDiscountType;
use App\Enums\TransactionStatus;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected PaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Checkout User',
            'email' => 'checkout@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'locale' => 'en',
        ]);

        PaymentMethod::create([
            'code' => 'mobile_wallet',
            'name' => ['en' => 'Mobile Wallet', 'ar' => 'محفظة', 'ku' => 'جزدان'],
            'description' => ['en' => 'Wallet', 'ar' => 'محفظة', 'ku' => 'جزدان'],
            'is_active' => true,
        ]);

        $this->gateway = PaymentGateway::create([
            'code' => 'zaincash',
            'name' => ['en' => 'ZainCash', 'ar' => 'زين كاش', 'ku' => 'زەین کەش'],
            'description' => ['en' => 'ZainCash gateway', 'ar' => 'زين كاش', 'ku' => 'زەین کەش'],
            'type' => PaymentGatewayType::MOBILE_WALLET,
            'is_active' => true,
        ]);
    }

    public function test_checkout_initiation_creates_order_items_and_transaction(): void
    {
        $this->fakeZainCashInit();
        $course = $this->createCourse('course-one', 150);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 150,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/checkout/initiate', [
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_url', 'https://gateway.test/pay');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'payment_gateway_id' => $this->gateway->id,
            'status' => TransactionStatus::PROCESSING->value,
        ]);
    }

    public function test_checkout_quote_returns_discounted_totals_for_percentage_promo(): void
    {
        $this->gateway->update([
            'processing_fee_percentage' => 10,
        ]);

        $course = $this->createCourse('course-quote', 200);
        $promoCode = $this->createPromoCode('save10', PromoCodeDiscountType::PERCENTAGE, 10);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 200,
        ]);

        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/checkout/quote', [
            'payment_gateway_id' => $this->gateway->id,
            'promo_code' => $promoCode->code,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.totals.subtotal_before_discount', '200.00')
            ->assertJsonPath('data.totals.discount_amount', '20.00')
            ->assertJsonPath('data.totals.subtotal_after_discount', '180.00')
            ->assertJsonPath('data.totals.gateway_processing_fee', '18.00')
            ->assertJsonPath('data.totals.total_amount', '198.00')
            ->assertJsonPath('data.promo.code', 'SAVE10');
    }

    public function test_guest_can_quote_checkout_from_session_cart(): void
    {
        $course = $this->createCourse('guest-quote-course', 175);

        $this->withSession([
            'guest_cart.course_ids' => [$course->id],
        ])->postJson('/api/v1/checkout/quote', [
            'payment_gateway_id' => $this->gateway->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.items.0.course_id', (string) $course->id)
            ->assertJsonPath('data.totals.subtotal_before_discount', '175.00');
    }

    public function test_guest_cannot_initiate_checkout_without_authentication(): void
    {
        $course = $this->createCourse('guest-initiate-course', 175);

        $this->withSession([
            'guest_cart.course_ids' => [$course->id],
        ])->postJson('/api/v1/checkout/initiate', [
            'payment_gateway_id' => $this->gateway->id,
        ])->assertUnauthorized();
    }

    public function test_checkout_quote_rejects_expired_promo_code(): void
    {
        $course = $this->createCourse('course-expired', 120);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 120,
        ]);

        $this->createPromoCode(
            'expired10',
            PromoCodeDiscountType::FIXED,
            10,
            expiresAt: now()->subDay()
        );

        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/checkout/quote', [
            'payment_gateway_id' => $this->gateway->id,
            'promo_code' => 'expired10',
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_checkout_initiation_snapshots_promo_code_and_discounted_totals(): void
    {
        $this->fakeZainCashInit();
        $course = $this->createCourse('course-promo', 150);
        $promoCode = $this->createPromoCode('save25', PromoCodeDiscountType::FIXED, 25);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 150,
        ]);

        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/checkout/initiate', [
            'payment_gateway_id' => $this->gateway->id,
            'promo_code' => $promoCode->code,
        ])
            ->assertCreated()
            ->assertJsonPath('data.promo.code', 'SAVE25')
            ->assertJsonPath('data.totals.discount_amount', '25.00')
            ->assertJsonPath('data.totals.total_amount', '125.00');

        $this->assertDatabaseHas('orders', [
            'promo_code_id' => $promoCode->id,
            'promo_code' => 'SAVE25',
            'discount_type' => PromoCodeDiscountType::FIXED->value,
            'discount_amount' => '25.00',
            'subtotal_before_discount' => '150.00',
            'subtotal_after_discount' => '125.00',
            'total_amount' => '125.00',
        ]);

        $this->assertDatabaseHas('transactions', [
            'amount' => '125.00',
            'total_amount' => '125.00',
        ]);
    }

    public function test_checkout_initiation_fails_for_empty_cart(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/checkout/initiate', [
            'payment_gateway_id' => $this->gateway->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_payment_verification_enrolls_user_marks_order_paid_and_clears_cart(): void
    {
        $this->fakeZainCashInit();
        $course = $this->createCourse('course-two', 200);
        $promoCode = $this->createPromoCode('verify20', PromoCodeDiscountType::FIXED, 20);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 200,
        ]);

        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/checkout/initiate', [
            'payment_gateway_id' => $this->gateway->id,
            'promo_code' => $promoCode->code,
        ])->assertCreated();

        $transaction = Transaction::firstOrFail();

        $this->fakeZainCashInquiry('SUCCESS');

        $this->getJson("/api/v1/payments/verify/{$transaction->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'completed');

        $transaction->refresh();
        $order = Order::firstOrFail();
        $course->refresh();

        $this->assertEquals(TransactionStatus::COMPLETED, $transaction->status);
        $this->assertEquals(OrderStatus::PAID, $order->status);
        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'transaction_id' => $transaction->id,
        ]);
        $this->assertSame(1, $course->students_count);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(1, $promoCode->fresh()->used_count);
    }

    public function test_repeated_payment_verification_is_idempotent_for_enrollments_and_course_count(): void
    {
        $this->fakeZainCashInit();
        $course = $this->createCourse('course-three', 300);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 300,
        ]);

        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/checkout/initiate', [
            'payment_gateway_id' => $this->gateway->id,
        ])->assertCreated();

        $transaction = Transaction::firstOrFail();

        $this->fakeZainCashInquiry('SUCCESS');
        $this->getJson("/api/v1/payments/verify/{$transaction->id}")->assertOk();

        $this->fakeZainCashInquiry('SUCCESS');
        $this->getJson("/api/v1/payments/verify/{$transaction->id}")->assertOk();

        $course->refresh();

        $this->assertDatabaseCount('course_enrollments', 1);
        $this->assertSame(1, $course->students_count);
    }

    public function test_failed_orders_do_not_permanently_consume_promo_usage_limit(): void
    {
        $course = $this->createCourse('course-usage', 100);
        $promoCode = $this->createPromoCode('limit1', PromoCodeDiscountType::FIXED, 10, usageLimit: 1);

        Order::create([
            'user_id' => $this->user->id,
            'promo_code_id' => $promoCode->id,
            'promo_code' => $promoCode->code,
            'discount_type' => $promoCode->discount_type,
            'discount_value' => $promoCode->discount_value,
            'discount_amount' => 10,
            'subtotal_before_discount' => 100,
            'subtotal_after_discount' => 90,
            'subtotal_amount' => 90,
            'gateway_processing_fee' => 0,
            'total_amount' => 90,
            'status' => OrderStatus::FAILED,
        ]);

        CartItem::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
            'price' => 100,
        ]);

        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/checkout/quote', [
            'payment_gateway_id' => $this->gateway->id,
            'promo_code' => $promoCode->code,
        ])
            ->assertOk()
            ->assertJsonPath('data.promo.code', 'LIMIT1');
    }

    protected function createCourse(string $slug, float $price): Course
    {
        return Course::create([
            'title' => ['en' => ucfirst($slug), 'ar' => ucfirst($slug), 'ku' => ucfirst($slug)],
            'slug' => $slug,
            'short_description' => ['en' => 'Short', 'ar' => 'Short', 'ku' => 'Short'],
            'description' => ['en' => 'Description', 'ar' => 'Description', 'ku' => 'Description'],
            'price' => $price,
            'is_active' => true,
        ]);
    }

    protected function createPromoCode(
        string $code,
        PromoCodeDiscountType $discountType,
        float $discountValue,
        ?\Illuminate\Support\Carbon $startsAt = null,
        ?\Illuminate\Support\Carbon $expiresAt = null,
        ?int $usageLimit = null,
    ): PromoCode {
        return PromoCode::create([
            'code' => $code,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'usage_limit' => $usageLimit,
            'is_active' => true,
        ]);
    }

    protected function fakeZainCashInit(): void
    {
        Http::fake([
            'https://pg-api-uat.zaincash.iq/oauth2/token' => Http::response([
                'access_token' => 'token-123',
            ], 200),
            'https://pg-api-uat.zaincash.iq/api/v2/payment-gateway/transaction/init' => Http::response([
                'status' => 'SUCCESS',
                'redirectUrl' => 'https://gateway.test/pay',
                'transactionDetails' => [
                    'transactionId' => 'gateway-txn-1',
                ],
            ], 200),
        ]);
    }

    protected function fakeZainCashInquiry(string $status): void
    {
        Http::fake([
            'https://pg-api-uat.zaincash.iq/oauth2/token' => Http::response([
                'access_token' => 'token-123',
            ], 200),
            'https://pg-api-uat.zaincash.iq/api/v2/payment-gateway/transaction/inquiry/*' => Http::response([
                'status' => $status,
            ], 200),
        ]);
    }
}
