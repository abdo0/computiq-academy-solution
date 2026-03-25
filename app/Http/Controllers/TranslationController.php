<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Load frontend translations for a specific locale
     */
    public function loadTranslations(Request $request): JsonResponse
    {
        $locale = $request->get('Locale', app()->getLocale());

        // Validate locale
        $supportedLocales = ['en', 'ar', 'ku'];
        if (! in_array($locale, $supportedLocales)) {
            $locale = 'ar'; // Default to Arabic for Computiq Academy
        }

        $translationPath = lang_path("{$locale}.json");
        $translations = [];

        if (file_exists($translationPath)) {
            $translations = json_decode(file_get_contents($translationPath), true) ?: [];
        } else {
            // Fallback to English if locale file doesn't exist
            $fallbackPath = lang_path('ar.json');
            if (file_exists($fallbackPath)) {
                $translations = json_decode(file_get_contents($fallbackPath), true) ?: [];
            }
        }

        // Generate hash for cache busting on frontend
        $hash = md5(json_encode($translations));

        return response()->json([
            'locale' => $locale,
            'hash' => $hash,
            'translations' => $translations,
        ]);
    }
}
