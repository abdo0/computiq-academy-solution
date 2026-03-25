<?php

use App\Helpers\TranslationHelper;

if (! function_exists('cached_trans')) {
    /**
     * Get cached translation using TranslationHelper
     */
    function cached_trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return TranslationHelper::translate($key, $replace, $locale);
    }
}

if (! function_exists('cached__')) {
    /**
     * Cached version of __() function
     */
    function cached__(string $key, array $replace = [], ?string $locale = null): string
    {
        return TranslationHelper::translate($key, $replace, $locale);
    }
}

if (! function_exists('trans_has')) {
    /**
     * Check if translation key exists
     */
    function trans_has(string $key, ?string $locale = null): bool
    {
        return TranslationHelper::has($key, $locale);
    }
}

if (! function_exists('trans_all')) {
    /**
     * Get all translations for a locale
     */
    function trans_all(?string $locale = null): array
    {
        return TranslationHelper::all($locale);
    }
}

if (! function_exists('trans_stats')) {
    /**
     * Get translation statistics
     */
    function trans_stats(?string $locale = null): array
    {
        return TranslationHelper::getStats($locale);
    }
}

if (! function_exists('trans_untranslated')) {
    /**
     * Get untranslated keys for a locale
     */
    function trans_untranslated(?string $locale = null): array
    {
        return TranslationHelper::getUntranslatedKeys($locale);
    }
}

if (! function_exists('trans_refresh')) {
    /**
     * Refresh translations cache
     */
    function trans_refresh(?string $locale = null): array
    {
        return TranslationHelper::refresh($locale);
    }
}

if (! function_exists('trans_preload')) {
    /**
     * Preload all translations
     */
    function trans_preload(): void
    {
        TranslationHelper::preloadAll();
    }
}
