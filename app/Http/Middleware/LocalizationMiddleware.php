<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get available locales
        $availableLocales = array_keys(config('filament-language-switcher.locals', ['en' => ['label' => 'English']]));

        $locale = null;
        // Priority 1: Check Accept-Language header (for API requests)
        if ($request->hasHeader('Accept-Language')) {
            $acceptLanguage = $request->header('Accept-Language');
            // Accept-Language header can be like "ar" or "ar,en;q=0.9" - extract first language
            $headerLocale = explode(',', $acceptLanguage)[0];
            $headerLocale = trim(explode(';', $headerLocale)[0]);

            if (in_array($headerLocale, $availableLocales)) {
                $locale = $headerLocale;
            }
        }

        // Priority 2: Check for locale parameter in URL
        if (! $locale && $request->has('locale')) {
            $requestedLocale = $request->get('locale');

            if (in_array($requestedLocale, $availableLocales)) {
                $locale = $requestedLocale;
                // Store locale in session
                session(['locale' => $requestedLocale]);

                // For web requests, redirect to clean URL without locale parameter
                if ($request->expectsJson() === false) {
                    $url = $request->url();
                    $params = $request->except('locale');
                    if (! empty($params)) {
                        $url .= '?'.http_build_query($params);
                    }

                    return redirect($url);
                }
            }
        }


        // Priority 3: Set locale based on authenticated user's language preference
        if (! $locale && Auth::check()) {
            $user = Auth::user();

            // Try to get user's language preference from user_languages table
            try {
                $userLanguage = DB::table('user_languages')
                    ->where('model_type', get_class($user))
                    ->where('model_id', $user->id)
                    ->first();

                if ($userLanguage && $userLanguage->lang) {
                    $userLocale = $userLanguage->lang;

                    // Validate locale against available locales
                    if (in_array($userLocale, $availableLocales)) {
                        $locale = $userLocale;
                    }
                }
            } catch (Exception $e) {
                // If user_languages table doesn't exist or there's an error,
                // fall back to session or default locale
            }
        }

        // Priority 4: Check for session locale (for all users, authenticated or not)
        if (! $locale) {
            $sessionLocale = session('locale');
            if ($sessionLocale && in_array($sessionLocale, $availableLocales)) {
                $locale = $sessionLocale;
            }
        }

        // Set the locale if found
        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
