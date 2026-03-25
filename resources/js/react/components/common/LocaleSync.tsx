import { useAppNavigate } from '../../hooks/useAppNavigate';
import { useEffect } from 'react';

import { useLanguage, Language } from '../../contexts/LanguageContext';
import { useLocale, useLocalePath, AVAILABLE_LOCALES, extractLocaleFromPath } from '../../utils/localeRouter';
import { useLocation} from 'react-router-dom';
import { useTranslation } from '../../contexts/TranslationProvider';

/**
 * LocaleSync: Synchronizes the URL parameter (`/:locale/`) with the application's LanguageContext
 */
export default function LocaleSync() {
    const { language, setLanguage } = useLanguage();
    const { __ } = useTranslation();
    const urlLocale = useLocale();
    const location = useLocation();
    const navigate = useAppNavigate();

    useEffect(() => {
        // If we're on a path with an invalid locale prefix (e.g., /fr/about but 'fr' is not supported)
        // The localeRouter will return the DEFAULT_LOCALE ('ar')
        // We want to force a redirect in this edge case if the first segment looks like a locale but isn't
        const segments = location.pathname.split('/').filter(Boolean);
        const firstSegment = segments[0] as any;

        // Check if it's strictly a 2-char string that isn't an available locale, 
        // to prevent infinite loops and weird behavior (e.g. going to /fr/ -> /ar/fr/)
        if (firstSegment && firstSegment.length === 2 && !AVAILABLE_LOCALES.includes(firstSegment)) {
            const restOfPath = segments.slice(1).join('/');
            navigate(`/${restOfPath}`, { replace: true });
            return;
        }

        if (sessionStorage.getItem('language_switch_in_progress')) {
            return;
        }

        const extracted = extractLocaleFromPath(location.pathname);

        if (extracted) {
            // URL has an explicit locale. Sync context to match URL.
            if (language !== extracted) {
                setLanguage(extracted as Language);
            }
        } else {
            // URL does not have a locale prefix (implies Arabic).
            // Check window location directly to prevent React Router state delays
            const currentPath = window.location.pathname;
            const currentExtracted = extractLocaleFromPath(currentPath);

            // If we are strictly on a path without a locale (like `/`)
            if (!currentExtracted && language !== 'ar') {
                // We MUST set the language to Arabic to match the URL,
                // otherwise the user sees English while on the Arabic (default) URL.
                setLanguage('ar');
                localStorage.setItem('language', 'ar');
            }
        }
    }, [language, setLanguage, navigate, location.pathname]);

    return null;
}
