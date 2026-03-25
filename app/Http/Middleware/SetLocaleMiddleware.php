<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = ['en', 'ku']; // Locales that appear in URL (ar is default, no prefix)
        $firstSegment = $request->segment(1);

        if ($firstSegment && in_array($firstSegment, $supportedLocales)) {
            // URL has locale prefix → use it
            $locale = $firstSegment;
        } else {
            // No prefix → always Arabic (URL is the source of truth)
            $locale = 'ar';
        }

        app()->setLocale($locale);
        session()->put('locale', $locale);

        return $next($request);
    }
}
