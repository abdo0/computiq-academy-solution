import React, { useEffect, useState } from 'react';
import { dataService } from '../services/dataService';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

interface CmsPagePayload {
  slug: string;
  title: Record<string, string> | string;
  content: Record<string, string> | string;
  meta_title?: string | null;
  meta_description?: string | null;
}

const GuidePage: React.FC = () => {
  const { language } = useLanguage();
  const { __ } = useTranslation();
  const initialBootstrap = useCurrentRouteBootstrap<any>();
  const [page, setPage] = useState<CmsPagePayload | null>(() => initialBootstrap?.page ?? null);
  const [loading, setLoading] = useState(() => !initialBootstrap?.page);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;

    const load = async () => {
      if (initialBootstrap?.page) {
        setPage(initialBootstrap.page);
        setLoading(false);
        return;
      }

      setLoading(true);
      setError(null);

      // Expect a CMS page with slug "guide"
      const result = await dataService.getPage('guide');

      if (!isMounted) return;

      if (!result) {
        setError('Page not found');
      } else {
        setPage(result);
      }

      setLoading(false);
    };

    load();

    return () => {
      isMounted = false;
    };
  }, [initialBootstrap]);

  const resolveLocalized = (value: CmsPagePayload['title']): string => {
    if (!value) return '';
    if (typeof value === 'string') return value;

    return value[language] || value.en || value.ar || '';
  };

  if (loading) {
    return (
      <div className="bg-white dark:bg-gray-900 min-h-screen pb-20">
        {/* Header skeleton */}
        <div className="bg-brand-900 py-16 sm:py-20 relative overflow-hidden">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="h-10 sm:h-12 md:h-14 w-2/3 sm:w-1/2 bg-white/20 rounded-sm animate-pulse" />
          </div>
        </div>

        {/* Content skeleton */}
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-4">
          <div className="space-y-3">
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded-sm animate-pulse" />
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded-sm animate-pulse w-11/12" />
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded-sm animate-pulse w-10/12" />
          </div>
          <div className="space-y-3 mt-4">
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded-sm animate-pulse w-9/12" />
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded-sm animate-pulse" />
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded-sm animate-pulse w-5/6" />
          </div>
        </div>
      </div>
    );
  }

  if (error || !page) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <p className="text-gray-500 dark:text-gray-300 text-lg">{error || 'Page not found'}</p>
      </div>
    );
  }

  const title = resolveLocalized(page.title);
  const contentHtml = resolveLocalized(page.content);

  return (
    <div className="bg-white dark:bg-gray-900 min-h-screen pb-20">
      {/* Header from CMS */}
      <div className="bg-brand-900 py-16 sm:py-20 relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="w-full h-full bg-gradient-to-br from-brand-500/40 via-brand-700/40 to-brand-900/60" />
        </div>
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white">
            {title}
          </h1>
        </div>
      </div>

      {/* Content from CMS */}
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div
          className="prose prose-lg max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-brand-600 dark:prose-a:text-brand-400 prose-img:rounded-sm prose-img:shadow-lg"
          dangerouslySetInnerHTML={{ __html: contentHtml }}
        />
      </div>
    </div>
  );
};

export default GuidePage;


