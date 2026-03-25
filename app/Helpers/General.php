<?php

use App\Helpers\SEO;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

function settings(?string $key = null, $default = null)
{
    // Return default if we're running in console
    if (app()->runningInConsole()) {
        return $default;
    }

    // Check if settings table exists before trying to load settings
    if (! Schema::hasTable('settings')) {
        return $default;
    }

    $cacheKey = 'site_settings';

    if (! Cache::has($cacheKey)) {
        loadSettings();
    }

    $settings = Cache::get($cacheKey, []);

    if (is_null($key)) {
        return $settings;
    }

    $value = $settings[$key] ?? $default;

    // Special handling for currency - use Currency model if available
    if ($key === 'currency') {
        try {
            if (Schema::hasTable('currencies')) {
                $defaultCurrency = \App\Models\Currency::getDefaultCode();

                return $defaultCurrency;
            }
        } catch (\Exception $e) {
            // Fallback to settings if Currency model is not available
        }
    }

    // Handle multilingual settings manually
    // If value is array with locale keys, return current locale value
    // if value is json string, decode it
    if (is_string($value) && isJsonString($value)) {
        $value = json_decode($value, true);
    }
    if (is_array($value) && isset($value[app()->getLocale()])) {
        return $value[app()->getLocale()] ?? $value['en'] ?? $default;
    }

    return $value;
}
function loadSettings(): void
{
    // Check if settings table exists before trying to load settings
    if (Schema::hasTable('settings')) {
        $cacheKey = 'site_settings';

        // Fields that should be decoded as JSON
        $jsonFields = [
            'app_locales',
            'company_name',
            'company_slogan',
            'company_description',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ];

        // Get all settings with selective JSON decoding
        $all_settings = [];
        foreach (\App\Models\Setting::all() as $setting) {
            $value = $setting->value;

            // Only decode JSON for fields that should be arrays/objects
            if (in_array($setting->key, $jsonFields) && is_string($value) && isJsonString($value)) {
                $value = json_decode($value, true);
            }

            $all_settings[$setting->key] = $value;
        }

        // Handle logo and icon URLs
        $logoKeys = ['company_logo', 'company_favicon', 'logo', 'sidebar_logo', 'sidebar_collapsed_logo', 'login_image', 'favicon'];
        foreach ($logoKeys as $imageKey) {
            if (isset($all_settings[$imageKey]) && ! empty($all_settings[$imageKey]) && is_string($all_settings[$imageKey])) {
                // Generate URL using asset helper for storage paths
                $all_settings[$imageKey] = asset('storage/'.$all_settings[$imageKey]);
            }
        }

        // Handle app_locales (convert to locales key for compatibility)
        if (isset($all_settings['app_locales']) && is_array($all_settings['app_locales'])) {
            $all_settings['locales'] = $all_settings['app_locales'];
        }

        Cache::put($cacheKey, $all_settings, now()->addDay());
    }
}

function isJsonString($string): bool
{
    if (! is_string($string)) {
        return false;
    }

    json_decode($string);

    return json_last_error() === JSON_ERROR_NONE;
}
function get_setting(?string $key = null, $default = null)
{
    // Just use the settings() function - same implementation
    return settings($key, $default);
}

function storage($disk = 'public'): Filesystem
{
    return Storage::disk($disk);
}

function seo($tag = null, $default = null)
{
    if (is_null($tag)) {
        return new SEO;
    }

    $value = SEO::getTag($tag) ?? $default;

    try {
        if ($tag == 'keywords' && is_array($value)) {
            $value = implode(',', $value);
        }
    } catch (Exception) {
    }

    return $value;
}

if (! function_exists('hexToRgb')) {
    /**
     * Convert a hex color code to RGB format (000 000 000).
     */
    function hexToRgb(string $hex): string
    {
        // Remove the '#' if present
        $hex = ltrim($hex, '#');

        // Ensure the hex code is valid
        if (strlen($hex) !== 6) {
            return '0 0 0';
        }

        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Format to ensure 3 digits with leading zeros
        return sprintf('%03d %03d %03d', $r, $g, $b);
    }
}

/**
 * Get locales and prioritize current locale (simplified for single database)
 */
function appLocales()
{
    $locales = settings('app_locales', ['en', 'ar', 'ku']);
    $current = app()->getLocale();

    // Ensure locales is an array
    if (is_string($locales)) {
        $locales = json_decode($locales, true) ?: ['en'];
    }

    if (! is_array($locales)) {
        $locales = ['en'];
    }

    $locales = array_values(array_diff($locales, [$current]));
    array_unshift($locales, $current);

    return $locales;
}

function getLocaleDir(): string
{
    $locale = app()->getLocale();
    if ($locale === 'ar' || $locale === 'ku') {
        return 'rtl';
    }

    return 'ltr';
}

/**
 * Get locale direction based on current locale (simplified for single database)
 */
function getAppLocaleDir(): string
{
    $locale = app()->getLocale();

    if ($locale === 'ar' || $locale === 'ku') {
        return 'rtl';
    }

    return 'ltr';
}

if (! function_exists('money')) {
    /**
     * Format a number as a currency string.
     *
     * @param  float|int  $amount  The amount to format.
     * @param  string|null  $currencyCode  The currency code (e.g., 'USD', 'EUR'). If null, uses settings.
     * @param  int  $decimals  The number of decimal places.
     * @param  string  $decimalSeparator  The separator for the decimal point.
     * @param  string  $thousandsSeparator  The thousands separator.
     * @return string The formatted currency string.
     */
    function money(float|int $amount, ?string $currencyCode = null, int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = ','): string
    {
        $currencySymbol = '';
        if ($currencyCode === null) {
            $currencyCode = settings('currency', 'USD');
        }

        // You can extend this to get symbols based on currencyCode from settings or a config file
        switch (strtoupper($currencyCode)) {
            case 'USD':
                $currencySymbol = '$';
                break;
            case 'EUR':
                $currencySymbol = '€';
                break;
            case 'GBP':
                $currencySymbol = '£';
                break;
                // Add more currency cases as needed
            default:
                $currencySymbol = strtoupper($currencyCode).' ';
        }

        $formattedAmount = number_format($amount, $decimals, $decimalSeparator, $thousandsSeparator);

        // Example: Place symbol before or after based on locale/currency convention if needed
        // For simplicity, this example places it before.
        return $currencySymbol.$formattedAmount;
    }
}

function setUpEmailInfo($email)
{
    $customer = User::where('email', $email)->first();
    if ($customer) {
        $nationality = strtoupper($customer->nationality);
        $language = config('countries.languages.'.$nationality, config('app.fallback_locale', 'en'));
        context()->add('email_language', $language);
        $direction = in_array($language, config('countries.rtl_languages')) ? 'rtl' : 'ltr';
        context()->add('email_direction', $direction);
    }
}

function emailTranslation($key)
{
    $language = context()->get('email_language', 'en');

    return __($key, [], $language);
}
