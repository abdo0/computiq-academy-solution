import React from 'react';
import { Helmet } from 'react-helmet-async';
import { useLanguage } from '../contexts/LanguageContext';
import { useSettings } from '../contexts/SettingsContext';

interface SeoProps {
    title?: string;
    description?: string;
    keywords?: string;
    image?: string;
}

const Seo: React.FC<SeoProps> = ({ title, description, keywords, image }) => {
    const { language, dir } = useLanguage();
    const { settings } = useSettings();

    const siteName = settings.siteName || 'Computiq Academy';
    const defaultTitle = settings.defaultSeo?.title || '';
    const defaultDescription = settings.defaultSeo?.description || '';
    const defaultKeywords = settings.defaultSeo?.keywords || '';
    const defaultImage = settings.defaultSeo?.ogImage;

    const finalTitle = title ? `${title} | ${siteName}` : defaultTitle;
    const finalDescription = description || defaultDescription;
    const finalKeywords = keywords || defaultKeywords;
    const finalImage = image || defaultImage;

    return (
        <Helmet key={finalTitle}>
            <html lang={language === 'ar' ? 'ar' : language === 'ku' ? 'ku' : 'en'} dir={dir} />
            <title>{finalTitle}</title>
            <meta name="description" content={finalDescription} />
            <meta name="keywords" content={finalKeywords} />

            {/* Open Graph / Facebook */}
            <meta property="og:type" content="website" />
            <meta property="og:title" content={finalTitle} />
            <meta property="og:description" content={finalDescription} />
            {finalImage && <meta property="og:image" content={finalImage} />}

            {/* Twitter */}
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:title" content={finalTitle} />
            <meta property="twitter:description" content={finalDescription} />
            {finalImage && <meta property="twitter:image" content={finalImage} />}
        </Helmet>
    );
};

export default Seo;