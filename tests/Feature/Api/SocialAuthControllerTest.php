<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google.client_id', 'google-client-id');
        config()->set('services.google.client_secret', 'google-client-secret');
        config()->set('services.google.redirect', 'https://computiq-academy.test/api/v1/auth/google/callback');
    }

    public function test_social_redirect_returns_provider_url(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturnSelf();
        $provider->shouldReceive('getTargetUrl')->once()->andReturn('https://accounts.google.com/test-auth');

        $this->getJson('/api/v1/auth/google/redirect')
            ->assertOk()
            ->assertJsonPath('data.redirect_url', 'https://accounts.google.com/test-auth');
    }

    public function test_social_callback_creates_user_assigns_student_role_and_logs_in(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $socialUser = Mockery::mock(SocialiteUser::class);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialUser);

        $socialUser->shouldReceive('getEmail')->once()->andReturn('social@example.com');
        $socialUser->shouldReceive('getId')->times(2)->andReturn('google-123');
        $socialUser->shouldReceive('getName')->once()->andReturn('Social Student');

        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated('student');
        $this->assertDatabaseHas('users', [
            'email' => 'social@example.com',
            'provider' => 'google',
            'provider_id' => 'google-123',
            'active_role' => 'student',
        ]);
        $this->assertTrue(User::firstOrFail()->hasRole('Student'));
    }

    public function test_social_callback_merges_guest_cart_and_redirects_back_to_checkout_when_requested(): void
    {
        $provider = Mockery::mock(SocialiteProvider::class);
        $socialUser = Mockery::mock(SocialiteUser::class);
        $course = Course::create([
            'title' => ['en' => 'Guest course', 'ar' => 'Guest course', 'ku' => 'Guest course'],
            'slug' => 'guest-social-course',
            'short_description' => ['en' => 'Short', 'ar' => 'Short', 'ku' => 'Short'],
            'description' => ['en' => 'Description', 'ar' => 'Description', 'ku' => 'Description'],
            'price' => 150,
            'is_active' => true,
        ]);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($socialUser);

        $socialUser->shouldReceive('getEmail')->once()->andReturn('checkout-social@example.com');
        $socialUser->shouldReceive('getId')->times(2)->andReturn('google-checkout-123');
        $socialUser->shouldReceive('getName')->once()->andReturn('Checkout Social');

        $response = $this->withSession([
            'guest_cart.course_ids' => [$course->id],
            'social_auth_redirect_to' => '/checkout',
        ])->get('/api/v1/auth/google/callback');

        $response->assertRedirect('/checkout');

        $user = User::where('email', 'checkout-social@example.com')->firstOrFail();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_social_redirect_requires_provider_configuration(): void
    {
        config()->set('services.github.client_id', null);
        config()->set('services.github.client_secret', null);
        config()->set('services.github.redirect', null);

        $this->getJson('/api/v1/auth/github/redirect')
            ->assertStatus(500)
            ->assertJsonPath('message', 'The Github login integration is not configured yet.');
    }
}
