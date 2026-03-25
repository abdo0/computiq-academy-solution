import { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useLanguage } from '../contexts/LanguageContext';
import { dataService } from '../services/dataService';
import { useTranslation } from '../contexts/TranslationProvider';
import { determineSeoParams } from '../utils/seoUtils';

export interface SeoData {
    title?: string;
    description?: string;
    keywords?: string;
    og_image?: string;
    og_type?: string;
    canonical?: string;
    structured_data?: object;
    article_published_time?: string;
    article_modified_time?: string;
    article_author?: string;
    article_section?: string;
}

// Ensure the locales match what App.tsx will use
export const removeLocaleFromPath = (path: string) => {
    const parts = path.split('/').filter(Boolean);
    if (parts.length > 0 && ['en', 'ar', 'ku'].includes(parts[0])) {
        return '/' + parts.slice(1).join('/');
    }
    return path === '/' ? '/' : path;
};

export const useSeoData = (): SeoData | null => {
    const location = useLocation();
    const { language: lang } = useLanguage();
    const { __ } = useTranslation();
    const [seoData, setSeoData] = useState<SeoData | null>(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const loadSeo = async () => {
            setIsLoading(true);
            setSeoData(null); // Reset SEO data when route changes

            try {
                // Remove locale prefix from path to match route patterns
                const path = removeLocaleFromPath(location.pathname);
                
                const { type, slug } = determineSeoParams(path);
                const data = await dataService.fetchSeo(type, slug);

                if (data) {
                    console.log('✅ [SEO] SEO data loaded for:', location.pathname, data);
                    setSeoData(data);
                } else {
                    console.warn('⚠️ [SEO] No SEO data returned for:', location.pathname);
                    setSeoData(null);
                }
            } catch (error) {
                console.error('❌ [SEO] Failed to load SEO data:', error);
                setSeoData(null);
            } finally {
                setIsLoading(false);
            }
        };

        loadSeo();
    }, [location.pathname, lang]);

    return seoData;
};
