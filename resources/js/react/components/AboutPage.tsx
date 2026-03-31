import React, { useEffect, useState } from 'react';
import { Target, Eye, Heart, ShieldCheck } from 'lucide-react';
import { useLanguage } from '../contexts/LanguageContext';
import { dataService } from '../services/dataService';
import { useTranslation } from '../contexts/TranslationProvider';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const AboutPageSkeleton: React.FC = () => (
    <div className="bg-white dark:bg-gray-900 min-h-screen pb-20 animate-pulse">
        {/* Page Header Skeleton */}
        <div className="bg-brand-900/50 dark:bg-gray-800 py-20 relative overflow-hidden">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center flex flex-col items-center">
                <div className="h-12 bg-white/20 dark:bg-gray-700 rounded-sm w-64 mb-6"></div>
                <div className="h-6 bg-white/20 dark:bg-gray-700 rounded-sm w-full max-w-2xl mx-auto"></div>
                <div className="h-6 bg-white/20 dark:bg-gray-700 rounded-sm w-3/4 max-w-xl mx-auto mt-2"></div>
            </div>
        </div>

        {/* Main Content Skeleton */}
        <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div className="space-y-6">
                <div className="h-6 bg-gray-200 dark:bg-gray-800 rounded-sm w-full"></div>
                <div className="h-6 bg-gray-200 dark:bg-gray-800 rounded-sm w-full"></div>
                <div className="h-6 bg-gray-200 dark:bg-gray-800 rounded-sm w-5/6"></div>
                <div className="h-6 bg-gray-200 dark:bg-gray-800 rounded-sm w-full mt-8"></div>
                <div className="h-6 bg-gray-200 dark:bg-gray-800 rounded-sm w-4/5"></div>
            </div>
        </div>
    </div>
);

const AboutPage: React.FC = () => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const [pageData, setPageData] = useState<any>(() => initialBootstrap?.pageInfo || null);
    const [loading, setLoading] = useState(() => !initialBootstrap?.pageInfo);

    useEffect(() => {
        if (initialBootstrap?.pageInfo) {
            return;
        }

        fetchPageData();
    }, [initialBootstrap]);

    const fetchPageData = async () => {
        setLoading(true);
        try {
            const data = await dataService.getDynamicPage('about-us');
            setPageData(data);
        } catch (error) {
            console.error('Failed to load about page data:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return <AboutPageSkeleton />;
    }

    const title = pageData?.title?.[language] || pageData?.title?.['en'] || __('About title');
    const content = pageData?.content?.[language] || pageData?.content?.['en'];

    return (
        <div className="bg-white dark:bg-gray-900 min-h-screen pb-20">
            {/* Page Header */}
            <div className="bg-brand-900 py-20 relative overflow-hidden">
                <div className="absolute inset-0 opacity-20">
                    <img src="/images/SVG/2.svg" className="w-full h-full object-cover" alt="Background" />
                </div>
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                    <h1 className="text-4xl font-extrabold text-white sm:text-5xl">{title}</h1>
                    <p className="mt-4 text-xl text-brand-100 max-w-2xl mx-auto">
                        {pageData?.meta_description?.[language] || pageData?.meta_description?.['en'] || __('About subtitle')}
                    </p>
                </div>
            </div>

            {/* Main Content */}
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                {content ? (
                    <div
                        className="prose prose-lg max-w-none text-gray-800 dark:text-gray-200 leading-loose prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-brand-600 dark:prose-a:text-brand-400"
                        dangerouslySetInnerHTML={{ __html: content }}
                    />
                ) : (
                    <div className="text-center py-20 text-gray-500 dark:text-gray-400">
                        {__('No data', 'No content available.')}
                    </div>
                )}
            </div>
        </div>
    );
};

export default AboutPage;
