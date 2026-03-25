import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { useSeoData } from '../../hooks/useSeoData';
import { useSEO } from '../../hooks/useSEO';
import { useTranslation } from '../../contexts/TranslationProvider';

export const SeoWrapper: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const seoData = useSeoData();
    const location = useLocation();

    // Apply SEO tags
    useSEO({
        title: seoData?.title,
        description: seoData?.description,
        keywords: seoData?.keywords,
        ogImage: seoData?.og_image,
        // We can map `ogType` directly to `og_type` from the DB since Open Graph specifies types.
        // If our useSEO type only supports standard ones or we want to pass it through:
        canonical: seoData?.canonical,
        structuredData: seoData?.structured_data,
        articlePublishedTime: seoData?.article_published_time,
        articleModifiedTime: seoData?.article_modified_time,
        articleAuthor: seoData?.article_author,
        articleSection: seoData?.article_section,
        // Provide a sensible default title while fetching
        ...(!seoData && { title: 'Computiq Academy - Online Learning Platform' })
    });

    // Log SEO updates in development
    useEffect(() => {
        if (import.meta.env.DEV) {
            console.log(`[SeoWrapper] Route Change -> ${location.pathname}`);
            if (seoData) {
                console.log(`[SeoWrapper] Applied SEO for -> ${seoData.title}`);
            }
        }
    }, [location.pathname, seoData]);

    return <>{children}</>;
};

export default SeoWrapper;
