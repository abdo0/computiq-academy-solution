<?php

use App\Http\Controllers\ManifestController;
use App\Http\Controllers\ReactAppController;
use App\Http\Controllers\LocaleController;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Web App Manifest Route
Route::get('/site.webmanifest', [ManifestController::class, 'show'])->name('manifest');

// Reports Export Route
// Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');

// Payment callback route (for gateway redirects)
// Route::get('/payment/callback/{transactionRef}', [\App\Http\Controllers\PaymentController::class, 'callback'])
//     ->name('payment.callback');

// Payment webhook route (for gateway callbacks - no CSRF protection)
// Route::post('/payment/webhook/{gateway}', [\App\Http\Controllers\PaymentWebhookController::class, 'handle'])
//    ->name('payment.webhook')
//    ->withoutMiddleware(['web']);

// React App Routes with SEO
// Language Switcher
Route::get('/lang/{locale}', [LocaleController::class, 'changeLanguage'])->name('change.language');

// Default routes (Arabic, no prefix)
Route::middleware([SetLocaleMiddleware::class])->group(function () {
    Route::get('/', [ReactAppController::class, 'home'])->name('home');
    Route::get('/about', [ReactAppController::class, 'about'])->name('about');
    Route::get('/contact', [ReactAppController::class, 'contact'])->name('contact');
    Route::get('/blog', [ReactAppController::class, 'blog'])->name('blog');
    Route::get('/blog/{slug}', [ReactAppController::class, 'blogShow'])->name('blog.show');
    Route::get('/faq', [ReactAppController::class, 'faq'])->name('faq');
    Route::get('/page/{slug}', [ReactAppController::class, 'page'])->name('page.show');
    Route::get('/courses', [ReactAppController::class, 'home'])->name('courses');
    Route::get('/paths', [ReactAppController::class, 'home'])->name('paths');
    Route::get('/login', [ReactAppController::class, 'home'])->name('login');
    Route::get('/signup', [ReactAppController::class, 'home'])->name('signup');
    Route::get('/forgot-password', [ReactAppController::class, 'home'])->name('forgot-password');
    Route::get('/verify-email', [ReactAppController::class, 'home'])->name('verify-email');
    Route::get('/dashboard', [ReactAppController::class, 'home'])->name('dashboard');
    // Catch-all for SPA
    Route::get('/{any?}', [ReactAppController::class, 'home'])->where('any', '^(?!admin|api|site\.webmanifest|storage|lang).*$');
});

// Locale-prefixed routes (en, ku) — no named routes to avoid collisions
Route::prefix('{locale}')
    ->where(['locale' => 'en|ku'])
    ->middleware([SetLocaleMiddleware::class])
    ->group(function () {
        Route::get('/', [ReactAppController::class, 'home']);
        Route::get('/about', [ReactAppController::class, 'about']);
        Route::get('/contact', [ReactAppController::class, 'contact']);
        Route::get('/blog', [ReactAppController::class, 'blog']);
        Route::get('/blog/{slug}', [ReactAppController::class, 'blogShow']);
        Route::get('/faq', [ReactAppController::class, 'faq']);
        Route::get('/page/{slug}', [ReactAppController::class, 'page']);

        // Catch-all for SPA
        Route::get('/{any?}', [ReactAppController::class, 'home'])->where('any', '^(?!admin|api|site\.webmanifest|storage|lang).*$');
    });
