import { useEffect } from 'react';
import { useTranslation } from '../contexts/TranslationProvider';

interface SEOProps {
    title?: string;
    description?: string;
    keywords?: string;
    ogTitle?: string;
    ogDescription?: string;
    ogImage?: string;
    ogUrl?: string;
    twitterCard?: 'summary' | 'summary_large_image' | 'app' | 'player';
    canonical?: string;
    structuredData?: object;
    articlePublishedTime?: string;
    articleModifiedTime?: string;
    articleAuthor?: string;
    articleSection?: string;
}

/**
 * Modern SEO hook for React 19
 * Uses native DOM manipulation for better performance and compatibility
 */
export const useSEO = ({
    title,
    description,
    keywords,
    ogTitle,
    ogDescription,
    ogImage,
    ogUrl,
    twitterCard = 'summary_large_image',
    canonical,
    structuredData,
    articlePublishedTime,
    articleModifiedTime,
    articleAuthor,
    articleSection,
}: SEOProps) => {
    useEffect(() => {
        // Update document title immediately
        if (title) {
            document.title = title;
            console.log('📝 [SEO] Title updated to:', title);
        }

        // Helper function to update or create meta tags
        const updateMetaTag = (property: string, content: string, nameAttr: 'name' | 'property' = 'name') => {
            if (!content) return;

            let element = document.querySelector(`meta[${nameAttr}="${property}"]`);

            if (!element) {
                element = document.createElement('Meta');
                element.setAttribute(nameAttr, property);
                document.head.appendChild(element);
            }

            element.setAttribute('content', content);
        };

        // Update or create link tags
        const updateLinkTag = (rel: string, href: string) => {
            if (!href) return;

            let element = document.querySelector(`link[rel="${rel}"]`) as HTMLLinkElement;

            if (!element) {
                element = document.createElement('Link');
                element.setAttribute('rel', rel);
                document.head.appendChild(element);
            }

            element.href = href;
        };

        // Standard meta tags
        if (description) {
            updateMetaTag('description', description);
        }

        if (keywords) {
            updateMetaTag('keywords', keywords);
        }

        // Open Graph tags
        if (ogTitle || title) {
            updateMetaTag('og:title', ogTitle || title || '', 'property');
        }

        if (ogDescription || description) {
            updateMetaTag('og:description', ogDescription || description || '', 'property');
        }

        if (ogImage) {
            updateMetaTag('og:image', ogImage, 'property');
        }

        if (ogUrl) {
            updateMetaTag('og:url', ogUrl, 'property');
        }

        updateMetaTag('og:type', 'website', 'property');

        // Twitter Card tags
        updateMetaTag('twitter:card', twitterCard);

        if (ogTitle || title) {
            updateMetaTag('twitter:title', ogTitle || title || '');
        }

        if (ogDescription || description) {
            updateMetaTag('twitter:description', ogDescription || description || '');
        }

        if (ogImage) {
            updateMetaTag('twitter:image', ogImage);
        }

        // Canonical URL
        if (canonical) {
            updateLinkTag('canonical', canonical);
        }

        // Article-specific meta tags
        if (articlePublishedTime) {
            updateMetaTag('article:published_time', articlePublishedTime, 'property');
        }

        if (articleModifiedTime) {
            updateMetaTag('article:modified_time', articleModifiedTime, 'property');
        }

        if (articleAuthor) {
            updateMetaTag('article:author', articleAuthor, 'property');
        }

        if (articleSection) {
            updateMetaTag('article:section', articleSection, 'property');
        }

        // Structured Data (JSON-LD)
        if (structuredData) {
            // Remove existing structured data scripts
            const existingScripts = document.querySelectorAll('script[type="application/ld+json"]');
            existingScripts.forEach((script) => {
                try {
                    const data = JSON.parse(script.textContent || '{}');
                    // Only remove if it's our structured data (has @context)
                    if (data['@context'] === 'https://schema.org') {
                        script.remove();
                    }
                } catch (e) {
                    // If parsing fails, remove it anyway
                    script.remove();
                }
            });

            // Add new structured data
            const script = document.createElement('Script');
            script.type = 'application/ld+json';
            script.textContent = JSON.stringify(structuredData);
            document.head.appendChild(script);
        }

        // Cleanup function
        return () => {
            // Cleanup structured data on unmount if needed
            if (structuredData) {
                const scripts = document.querySelectorAll('script[type="application/ld+json"]');
                scripts.forEach((script) => {
                    try {
                        const data = JSON.parse(script.textContent || '{}');
                        if (data['@context'] === 'https://schema.org') {
                            script.remove();
                        }
                    } catch (e) {
                        // Ignore parsing errors
                    }
                });
            }
        };
    }, [
        title,
        description,
        keywords,
        ogTitle,
        ogDescription,
        ogImage,
        ogUrl,
        twitterCard,
        canonical,
        structuredData,
        articlePublishedTime,
        articleModifiedTime,
        articleAuthor,
        articleSection,
    ]);
};
