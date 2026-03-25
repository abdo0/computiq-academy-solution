<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Change language endpoint for the entire application
     */
    public function changeLanguage(Request $request, string $locale): \Illuminate\Http\RedirectResponse
    {
        $supportedLocales = ['en', 'ar', 'ku'];
        
        if (in_array($locale, $supportedLocales)) {
            session()->put('Locale', $locale);
            app()->setLocale($locale);
        }

        // Build redirect URL with locale prefix
        $referer = $request->headers->get('Referer');
        $currentPath = '/';

        if ($referer) {
            $parsed = parse_url($referer);
            $currentPath = $parsed['path'] ?? '/';
        }

        // Strip any existing locale prefix from the path
        $currentPath = preg_replace('#^/(en|ar|ku)(/|$)#', '/', $currentPath);
        $currentPath = $currentPath ?: '/';

        // Build new path: non-default locales get a prefix, Arabic (default) stays clean
        if ($locale !== 'ar') {
            $targetPath = '/' . $locale . ($currentPath === '/' ? '' : $currentPath);
        } else {
            $targetPath = $currentPath;
        }

        return redirect($targetPath);
    }
}
