<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\SeoController;
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
    Route::get('/categories', [ContentController::class, 'categories'])->name('api.v1.categories');
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

    // Contact Form (public)
    Route::post('/contact', [\App\Http\Controllers\Api\ContactController::class, 'store'])->name('api.v1.contact.store');

    // Newsletter Subscription (public)
    Route::post('/subscribe', [\App\Http\Controllers\Api\SubscriberController::class, 'store'])->name('api.v1.subscribe');

});
