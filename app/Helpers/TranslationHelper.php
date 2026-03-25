<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class TranslationHelper
{
    /**
     * Cache duration for translations (in minutes)
     */
    const CACHE_DURATION = 1440; // 24 hours

    /**
     * Available locales for the application
     */
    protected static array $supportedLocales = ['en', 'ar', 'ku'];

    /**
     * In-memory cache for translations to avoid repeated cache calls
     */
    protected static array $memoryCache = [];

    /**
     * Load all translations for a specific locale into cache
     */
    public static function loadTranslationsForLocale(string $locale): array
    {
        // Check memory cache first
        if (isset(static::$memoryCache[$locale])) {
            return static::$memoryCache[$locale];
        }

        $cacheKey = "translations_{$locale}";

        $translations = Cache::remember($cacheKey, static::CACHE_DURATION, function () use ($locale) {
            return static::loadTranslationsFromFiles($locale);
        });

        // Store in memory cache for this request
        static::$memoryCache[$locale] = $translations;

        return $translations;
    }

    /**
     * Load translations from all available files for a locale
     */
    protected static function loadTranslationsFromFiles(string $locale): array
    {
        $translations = [];

        // Load from JSON file (for general translations)
        $jsonFile = base_path("lang/{$locale}.json");
        if (File::exists($jsonFile)) {
            $jsonTranslations = json_decode(File::get($jsonFile), true) ?: [];
            $translations = array_merge($translations, $jsonTranslations);
        }

        // Load from PHP files in lang/{locale}/ directory
        $phpDir = base_path("lang/{$locale}");
        if (File::exists($phpDir) && File::isDirectory($phpDir)) {
            $phpFiles = File::files($phpDir);

            foreach ($phpFiles as $file) {
                if ($file->getExtension() === 'php') {
                    $filename = $file->getFilenameWithoutExtension();
                    $fileTranslations = include $file->getPathname();

                    if (is_array($fileTranslations)) {
                        // Prefix with filename for namespacing
                        foreach ($fileTranslations as $key => $value) {
                            $translations["{$filename}.{$key}"] = $value;
                        }
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * Get a translation for a specific key and locale
     */
    public static function get(string $key, ?string $locale = null, $default = null): string
    {
        $locale = $locale ?: app()->getLocale();

        // Load translations for the locale
        $translations = static::loadTranslationsForLocale($locale);

        // Return translation if exists, otherwise return default or key
        return $translations[$key] ?? $default ?? $key;
    }

    /**
     * Translate a key using the current locale with fallback
     */
    public static function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        // Try to get from our cached translations first
        $translation = static::get($key, $locale);

        // If not found and not the same as key, use Laravel's built-in translation
        if ($translation === $key) {
            $translation = Lang::get($key, $replace, $locale);
        }

        // Apply replacements if any
        if (! empty($replace) && is_string($translation)) {
            foreach ($replace as $search => $replacement) {
                $translation = str_replace(":{$search}", $replacement, $translation);
            }
        }

        return $translation;
    }

    /**
     * Check if a translation exists for a key
     */
    public static function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?: app()->getLocale();
        $translations = static::loadTranslationsForLocale($locale);

        return array_key_exists($key, $translations);
    }

    /**
     * Get all translations for a locale
     */
    public static function all(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        return static::loadTranslationsForLocale($locale);
    }

    /**
     * Clear translation cache for all locales
     */
    public static function clearCache(): void
    {
        foreach (static::$supportedLocales as $locale) {
            Cache::forget("translations_{$locale}");
        }

        // Clear memory cache
        static::$memoryCache = [];
    }

    /**
     * Clear translation cache for a specific locale
     */
    public static function clearCacheForLocale(string $locale): void
    {
        Cache::forget("translations_{$locale}");
        unset(static::$memoryCache[$locale]);
    }

    /**
     * Refresh translations for a locale (reload from files)
     */
    public static function refresh(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        // Clear cache for this locale
        static::clearCacheForLocale($locale);

        // Reload translations
        return static::loadTranslationsForLocale($locale);
    }

    /**
     * Get supported locales
     */
    public static function getSupportedLocales(): array
    {
        return static::$supportedLocales;
    }

    /**
     * Set supported locales
     */
    public static function setSupportedLocales(array $locales): void
    {
        static::$supportedLocales = $locales;
    }

    /**
     * Preload translations for all supported locales
     */
    public static function preloadAll(): void
    {
        foreach (static::$supportedLocales as $locale) {
            static::loadTranslationsForLocale($locale);
        }
    }

    /**
     * Get translation statistics
     */
    public static function getStats(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $translations = static::loadTranslationsForLocale($locale);

        $total = count($translations);
        $translated = count(array_filter($translations, function ($value, $key) {
            return $value !== $key; // Count as translated if value differs from key
        }, ARRAY_FILTER_USE_BOTH));

        $percentage = $total > 0 ? round(($translated / $total) * 100, 2) : 0;

        return [
            'locale' => $locale,
            'total_keys' => $total,
            'translated_keys' => $translated,
            'untranslated_keys' => $total - $translated,
            'completion_percentage' => $percentage,
        ];
    }

    /**
     * Export translations for a locale to JSON format
     */
    public static function exportToJson(string $locale, bool $prettyPrint = true): string
    {
        $translations = static::loadTranslationsForLocale($locale);

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($translations, $flags);
    }

    /**
     * Import translations from array for a locale
     */
    public static function importFromArray(string $locale, array $translations): bool
    {
        try {
            $filePath = base_path("lang/{$locale}.json");

            // Merge with existing translations
            $existing = [];
            if (File::exists($filePath)) {
                $existing = json_decode(File::get($filePath), true) ?: [];
            }

            $merged = array_merge($existing, $translations);

            // Save to file
            $json = json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            File::put($filePath, $json);

            // Clear cache to force reload
            static::clearCacheForLocale($locale);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Find untranslated keys (where value equals key)
     */
    public static function getUntranslatedKeys(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $translations = static::loadTranslationsForLocale($locale);

        return array_keys(array_filter($translations, function ($value, $key) {
            return $value === $key;
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Helper method to get cached translation or fallback to Laravel's __() function
     */
    public static function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return static::translate($key, $replace, $locale);
    }
}
