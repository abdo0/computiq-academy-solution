<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LearningPathController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\SeoController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ============================================================================
// V1 API Routes
// ============================================================================
Route::prefix('v1')->group(function () {

    // Translations
    Route::get('/translations', [TranslationController::class, 'loadTranslations'])->name('api.v1.translations');

    // ========================================================================
    // Public Routes (No Authentication Required)
    // ========================================================================

    // Articles (Blog)
    Route::get('/articles', [ArticleController::class, 'index'])->name('api.v1.articles.index');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('api.v1.articles.show');

    // Consolidated Home Endpoint
    Route::get('/home', [HomeController::class, 'index'])->name('api.v1.home.index');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('api.v1.courses.index');
    Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('api.v1.courses.show');

    // Instructor Profile
    Route::get('/instructors/{slug}', [InstructorController::class, 'show'])->name('api.v1.instructors.show');

    // Content
    Route::get('/settings', [ContentController::class, 'settings'])->name('api.v1.settings');
    Route::get('/faqs', [ContentController::class, 'faqs'])->name('api.v1.faqs');
    Route::get('/content/hero', [ContentController::class, 'hero'])->name('api.v1.content.hero');
    Route::get('/content/footer-pages', [ContentController::class, 'footerPages'])->name('api.v1.content.footer-pages');
    Route::get('/content/other-pages', [ContentController::class, 'otherPages'])->name('api.v1.content.other-pages');
    Route::get('/payment-gateways', [ContentController::class, 'paymentGateways'])->name('api.v1.payment-gateways');
    Route::get('/categories', [ContentController::class, 'categories'])->name('api.v1.categories');

    // Learning Paths
    Route::get('/paths', [LearningPathController::class, 'index'])->name('api.v1.paths.index');
    Route::get('/paths/{slug}', [LearningPathController::class, 'show'])->name('api.v1.paths.show');

    Route::get('/countries', [ContentController::class, 'countries'])->name('api.v1.countries');
    Route::get('/countries/{country}/states', [ContentController::class, 'states'])->name('api.v1.states');
    Route::get('/content/home-sections', [ContentController::class, 'homeSections'])->name('api.v1.content.home-sections');
    Route::get('/content/testimonials', [ContentController::class, 'testimonials'])->name('api.v1.content.testimonials');

    // Page SEO endpoints
    Route::get('/pages/home', [ContentController::class, 'homeSeo'])->name('api.v1.pages.home');
    Route::get('/pages/about', [ContentController::class, 'aboutSeo'])->name('api.v1.pages.about');
    Route::get('/pages/contact', [ContentController::class, 'contactSeo'])->name('api.v1.pages.contact');
    Route::get('/pages/faq', [ContentController::class, 'faqSeo'])->name('api.v1.pages.faq');
    Route::get('/pages/{slug}', [ContentController::class, 'page'])->name('api.v1.pages.show');

    // SEO API Routes
    Route::prefix('seo')->group(function () {
        Route::get('/', [SeoController::class, 'getSeo'])->name('api.v1.seo.get');
    });

    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('api.v1.search');

    // Contact Form (public)
    Route::post('/contact', [\App\Http\Controllers\Api\ContactController::class, 'store'])->name('api.v1.contact.store')->middleware('turnstile');

    // Newsletter Subscription (public)
    Route::post('/subscribe', [\App\Http\Controllers\Api\SubscriberController::class, 'store'])->name('api.v1.subscribe')->middleware('turnstile');

    // ========================================================================
    // User Auth Routes
    // ========================================================================
    Route::prefix('user')->group(function () {
        // Public auth routes
        Route::post('/login', [\App\Http\Controllers\Api\UserAuthController::class, 'login'])->name('api.v1.user.login');
        Route::post('/register', [\App\Http\Controllers\Api\UserAuthController::class, 'register'])->name('api.v1.user.register');
        Route::post('/forgot-password', [\App\Http\Controllers\Api\UserAuthController::class, 'forgotPassword'])->name('api.v1.user.forgot-password');
        Route::post('/reset-password', [\App\Http\Controllers\Api\UserAuthController::class, 'resetPassword'])->name('api.v1.user.reset-password');

        // Authenticated user routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [\App\Http\Controllers\Api\UserAuthController::class, 'logout'])->name('api.v1.user.logout');
            Route::get('/profile', [\App\Http\Controllers\Api\UserAuthController::class, 'profile'])->name('api.v1.user.profile');
            Route::post('/profile', [\App\Http\Controllers\Api\UserAuthController::class, 'updateProfile'])->name('api.v1.user.profile.update');
            Route::post('/profile/email', [\App\Http\Controllers\Api\UserAuthController::class, 'updateEmail'])->name('api.v1.user.profile.email');
            Route::post('/profile/email/verify-otp', [\App\Http\Controllers\Api\UserAuthController::class, 'verifyEmailOTP'])->name('api.v1.user.profile.email.verify');
            Route::post('/profile/password', [\App\Http\Controllers\Api\UserAuthController::class, 'updatePassword'])->name('api.v1.user.profile.password');
            Route::post('/locale', [\App\Http\Controllers\Api\UserAuthController::class, 'updateLocale'])->name('api.v1.user.locale');
            Route::get('/dashboard/stats', [\App\Http\Controllers\Api\UserAuthController::class, 'dashboardStats'])->name('api.v1.user.dashboard.stats');
            Route::get('/enrollments', [\App\Http\Controllers\Api\UserAuthController::class, 'enrollments'])->name('api.v1.user.enrollments');

            // Cart routes
            Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index'])->name('api.v1.user.cart.index');
            Route::post('/cart', [\App\Http\Controllers\Api\CartController::class, 'store'])->name('api.v1.user.cart.store');
            Route::delete('/cart/{courseId}', [\App\Http\Controllers\Api\CartController::class, 'destroy'])->name('api.v1.user.cart.destroy');
            Route::delete('/cart', [\App\Http\Controllers\Api\CartController::class, 'clear'])->name('api.v1.user.cart.clear');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/checkout/quote', [CheckoutController::class, 'quote'])->name('api.v1.checkout.quote');
        Route::post('/checkout/initiate', [CheckoutController::class, 'initiate'])->name('api.v1.checkout.initiate');
        Route::get('/payments/verify/{transaction}', [PaymentController::class, 'verify'])->name('api.v1.payments.verify');
    });

    Route::get('/payments/callback/{transactionRef}', [PaymentController::class, 'callback'])->name('api.v1.payments.callback');

});

Route::post('/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('api.payments.webhook')
    ->withoutMiddleware(['web']);
