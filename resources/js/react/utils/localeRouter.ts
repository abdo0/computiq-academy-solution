import { useParams, useLocation } from 'react-router-dom';
import { useAppNavigate } from '../hooks/useAppNavigate';
import { useLanguage, Language } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';

/**
 * Available locales in the application
 */
export const AVAILABLE_LOCALES = ['ar', 'en', 'ku'] as const;
export type Locale = typeof AVAILABLE_LOCALES[number];

/**
 * Default locale
 */
export const DEFAULT_LOCALE: Locale = 'ar';

/**
 * Extract locale from URL path
 */
export function extractLocaleFromPath(pathname: string): Locale | null {
    const segments = pathname.split('/').filter(Boolean);
    const firstSegment = segments[0];

    if (firstSegment && AVAILABLE_LOCALES.includes(firstSegment as Locale)) {
        return firstSegment as Locale;
    }

    return null;
}

/**
 * Get current locale from URL or default
 */
export function getCurrentLocale(pathname: string): Locale {
    const extracted = extractLocaleFromPath(pathname);
    // If no locale found in path, fallback to default (Arabic)
    return extracted || DEFAULT_LOCALE;
}

/**
 * Generate path with locale prefix
 */
export function localePath(path: string, locale: Locale): string {
    const cleanPath = path.startsWith('/') ? path.slice(1) : path;
    // Arabic (default) has no prefix to maintain old URLs
    if (locale === 'ar') {
        return cleanPath ? `/${cleanPath}` : '/';
    }
    return `/${locale}${cleanPath ? `/${cleanPath}` : ''}`;
}

/**
 * Remove locale from path
 */
export function removeLocaleFromPath(path: string): string {
    const segments = path.split('/').filter(Boolean);
    if (segments.length > 0 && AVAILABLE_LOCALES.includes(segments[0] as Locale)) {
        const pathWithoutLocale = segments.slice(1).join('/');
        return pathWithoutLocale ? `/${pathWithoutLocale}` : '/';
    }
    return path;
}

/**
 * Switch locale in current path
 */
export function switchLocaleInPath(pathname: string, newLocale: Locale): string {
    const currentPath = removeLocaleFromPath(pathname);
    // If switching to default language (ar), return path without prefix
    if (newLocale === 'ar') {
        return currentPath || '/';
    }
    return localePath(currentPath, newLocale);
}

/**
 * Hook to get current locale from URL params
 */
export function useLocale(): Locale {
    const params = useParams<{ locale?: string }>();
    const location = useLocation();
    const { language } = useLanguage();
    const { __ } = useTranslation();

    // Try to get from route params first
    if (params.locale && AVAILABLE_LOCALES.includes(params.locale as Locale)) {
        return params.locale as Locale;
    }

    // Fallback to extracting from pathname
    const extracted = extractLocaleFromPath(location.pathname);
    if (extracted) {
        return extracted;
    }

    // Otherwise fallback to user language context or default
    return language as Locale || DEFAULT_LOCALE;
}

/**
 * Hook to generate paths with current locale
 */
export function useLocalePath() {
    const locale = useLocale();
    const navigate = useAppNavigate();

    return {
        locale,
        path: (path: string) => localePath(path, locale),
        navigate: (path: string, options?: { replace?: boolean }) => {
            navigate(localePath(path, locale), options);
        }
    };
}
