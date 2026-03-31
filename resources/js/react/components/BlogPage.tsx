import React, { useState, useEffect } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { dataService } from '../services/dataService';
import { BlogPost } from '../types';
import BlogCard, { BlogCardSkeleton } from './BlogCard';
import { useTranslation } from '../contexts/TranslationProvider';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const BlogPage: React.FC = () => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const initialPosts = initialBootstrap?.posts || [];
    const initialPageData = initialBootstrap?.pageInfo || null;
    const [posts, setPosts] = useState<BlogPost[]>(() => initialPosts);
    const [pageData, setPageData] = useState<any>(() => initialPageData);
    const [isLoading, setIsLoading] = useState(() => initialPosts.length === 0 && !initialPageData);

    useEffect(() => {
        if (initialPosts.length > 0 || initialPageData) {
            return;
        }

        const loadData = async () => {
            try {
                const [postsData, pageInfo] = await Promise.all([
                    dataService.getBlogPosts(),
                    dataService.getDynamicPage('blog').catch(() => null)
                ]);
                setPosts(postsData);
                setPageData(pageInfo);
            } catch (error) {
                console.error("Failed to fetch data", error);
            } finally {
                setIsLoading(false);
            }
        };
        loadData();
    }, [initialPageData, initialPosts.length, language]);



    return (
        <div className="bg-[#fcfdfd] dark:bg-gray-950 min-h-screen py-12 pb-24">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Enhanced Page Header */}
                <div className="text-center mb-16 max-w-3xl mx-auto flex flex-col items-center">
                    {isLoading ? (
                        <>
                            <div className="h-12 bg-gray-200 dark:bg-gray-800 rounded-sm w-64 mb-6 animate-pulse"></div>
                            <div className="h-5 bg-gray-200 dark:bg-gray-800 rounded-sm w-full max-w-2xl mb-3 animate-pulse"></div>
                            <div className="h-5 bg-gray-200 dark:bg-gray-800 rounded-sm w-4/5 max-w-xl animate-pulse mb-8"></div>
                        </>
                    ) : (
                        <>
                            <h1 className="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-6 tracking-tight">
                                {pageData?.title?.[language] || pageData?.title?.['en'] || __('Blog title', 'Our Blog & Updates')}
                            </h1>
                            <p className="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                                {pageData?.meta_description?.[language] || pageData?.meta_description?.['en'] || __('Blog subtitle', 'Stay informed with the latest news, success stories, and updates from our community and our courses.')}
                            </p>
                        </>
                    )}

                    {/* Optional: Filter categories could be added here in the future to further enrich the page */}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    {isLoading ? (
                        <>
                            <BlogCardSkeleton />
                            <BlogCardSkeleton />
                            <BlogCardSkeleton />
                            <BlogCardSkeleton />
                            <BlogCardSkeleton />
                            <BlogCardSkeleton />
                        </>
                    ) : posts.length > 0 ? (
                        posts.map(post => <BlogCard key={post.id} post={post} />)
                    ) : (
                        <div className="col-span-full py-20 text-center">
                            <p className="text-gray-500 dark:text-gray-400 text-lg">{__('Blog empty', 'No articles found at the moment.')}</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default BlogPage;
