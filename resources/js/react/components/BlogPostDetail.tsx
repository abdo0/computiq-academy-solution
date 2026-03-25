import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import AppLink from './common/AppLink';
import { useAppNavigate } from '../hooks/useAppNavigate';
import { useLanguage } from '../contexts/LanguageContext';
import { Calendar, User, ArrowLeft, ArrowRight, Share2 } from 'lucide-react';
import { dataService } from '../services/dataService';
import { BlogPost } from '../types';
import { useTranslation } from '../contexts/TranslationProvider';

const BlogPostDetail: React.FC = () => {
    const { slug } = useParams<{ slug: string }>();
    const navigate = useAppNavigate();
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const [post, setPost] = useState<BlogPost | undefined>(undefined);
    const [recentPosts, setRecentPosts] = useState<BlogPost[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        if (!slug) return;
        const loadPost = async () => {
            try {
                const [result, recentResult] = await Promise.all([
                    dataService.getBlogPostBySlug(slug),
                    dataService.getBlogPosts({ per_page: 4 })
                ]);
                if (result) {
                    setPost(result.data);
                }
                if (recentResult) {
                    // Filter out current post
                    setRecentPosts(recentResult.filter(p => p.slug !== slug).slice(0, 3));
                }
            } catch (error) {
                console.error("Failed to load post", error);
            } finally {
                setIsLoading(false);
            }
        };
        loadPost();
    }, [slug]);

    const SkeletonDetail = () => (
        <div className="bg-white dark:bg-gray-900 min-h-screen pb-20 animate-pulse">
            <div className="h-[400px] w-full bg-gray-200 dark:bg-gray-700 relative">
                <div className="absolute bottom-0 left-0 right-0 p-8 max-w-4xl mx-auto space-y-4">
                    <div className="h-10 bg-gray-300 dark:bg-gray-600 rounded-sm w-28"></div>
                    <div className="h-12 bg-gray-300 dark:bg-gray-600 rounded-sm w-3/4"></div>
                    <div className="flex gap-4">
                        <div className="h-6 bg-gray-300 dark:bg-gray-600 rounded-sm w-32"></div>
                        <div className="h-6 bg-gray-300 dark:bg-gray-600 rounded-sm w-32"></div>
                    </div>
                </div>
            </div>
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                    <div className="lg:col-span-8 space-y-6">
                        <div className="bg-white dark:bg-gray-800 rounded-sm p-6 shadow-sm">
                            <div className="h-4 bg-gray-200 dark:bg-gray-600 rounded-sm w-full mb-4"></div>
                            <div className="h-4 bg-gray-200 dark:bg-gray-600 rounded-sm w-full mb-4"></div>
                            <div className="h-4 bg-gray-200 dark:bg-gray-600 rounded-sm w-5/6 mb-4"></div>
                            <div className="h-64 bg-gray-200 dark:bg-gray-600 rounded-sm my-8"></div>
                            <div className="h-4 bg-gray-200 dark:bg-gray-600 rounded-sm w-full mb-4"></div>
                            <div className="h-4 bg-gray-200 dark:bg-gray-600 rounded-sm w-3/4"></div>
                        </div>
                    </div>
                    <div className="lg:col-span-4">
                        <div className="bg-gray-100 dark:bg-gray-800/50 rounded-sm p-6 h-[400px]"></div>
                    </div>
                </div>
            </div>
        </div>
    );

    if (isLoading) {
        return <SkeletonDetail />;
    }

    if (!post) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                <div className="text-center">
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-4">{__('Blog not found')}</h2>
                    <button
                        onClick={() => navigate('/blog')}
                        className="text-brand-600 dark:text-brand-400 font-bold hover:underline"
                    >
                        {__('Blog back')}
                    </button>
                </div>
            </div>
        );
    }

    const getContent = () => {
        return { title: post.title, content: post.content, excerpt: post.excerpt };
    };

    const { title, content, excerpt } = getContent();

    return (
        <div className="bg-white dark:bg-gray-900 min-h-screen pb-20">
            {/* Hero Image */}
            <div className="relative h-[400px] w-full">
                <img
                    src={post.imageUrl || post.featuredImageUrl || '/images/SVG/5.svg'}
                    alt={title}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                        e.currentTarget.src = '/images/SVG/5.svg';
                    }}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <div className="absolute bottom-0 left-0 right-0 p-8 max-w-4xl mx-auto">
                    <button
                        onClick={() => navigate('/blog')}
                        className="text-white/80 hover:text-white mb-6 flex items-center gap-2 transition-colors text-sm font-bold bg-black/20 backdrop-blur-sm px-4 py-2 rounded-sm w-fit"
                    >
                        {language === 'ar' || language === 'ku' ? <ArrowRight size={16} /> : <ArrowLeft size={16} />}
                        {__('Blog back')}
                    </button>
                    <h1 className="text-3xl md:text-5xl font-extrabold text-white mb-4 leading-tight shadow-sm">
                        {title}
                    </h1>
                    <div className="flex items-center text-white/90 gap-6 text-sm md:text-base font-medium">
                        <span className="flex items-center gap-2"><Calendar size={18} /> {post.date}</span>
                        <span className="flex items-center gap-2"><User size={18} /> {post.author}</span>
                    </div>
                </div>
            </div>

            {/* Content */}
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                    {/* Main Article */}
                    <div className="lg:col-span-8">
                        <div className="bg-white dark:bg-gray-800 rounded-sm p-6 sm:p-8 lg:p-10 shadow-sm border border-gray-100 dark:border-gray-700">
                            {/* Content Image */}
                            {post.contentImageUrl && (
                                <div className="mb-8 rounded-sm overflow-hidden">
                                    <img
                                        src={post.contentImageUrl}
                                        alt={title as string}
                                        className="w-full h-auto object-cover"
                                        onError={(e) => {
                                            e.currentTarget.src = '/images/SVG/6.svg';
                                        }}
                                    />
                                </div>
                            )}

                            <div
                                className="prose prose-lg max-w-none text-gray-800 dark:text-gray-200 leading-loose whitespace-pre-line prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-brand-600 dark:prose-a:text-brand-400"
                                dangerouslySetInnerHTML={{ __html: content as string }}
                            />

                            <div className="mt-12 pt-8 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <button
                                    onClick={() => navigate('/blog')}
                                    className="text-brand-600 dark:text-brand-400 font-bold hover:text-brand-700 dark:hover:text-brand-300 flex items-center gap-2"
                                >
                                    {language === 'ar' || language === 'ku' ? <ArrowRight size={20} /> : <ArrowLeft size={20} />}
                                    {__('Blog back')}
                                </button>

                                <button
                                    onClick={() => navigator.clipboard.writeText(window.location.href)}
                                    className="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 px-6 py-2.5 rounded-sm font-bold text-sm flex items-center gap-2 transition-colors"
                                >
                                    <Share2 size={16} />
                                    {__('Blog share')}
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="lg:col-span-4">
                        <div className="bg-gray-50 dark:bg-gray-800/50 rounded-sm p-6 border border-gray-100 dark:border-gray-700 sticky top-24">
                            <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                {__('Blog recent articles')}
                            </h3>

                            {recentPosts.length > 0 ? (
                                <div className="flex flex-col gap-6">
                                    {recentPosts.map(recentPost => {
                                        const rTitle = recentPost.title;
                                        return (
                                            <div key={recentPost.id} className="group">
                                                <AppLink to={`/blog/${recentPost.slug}`} className="flex gap-4 items-center">
                                                    <div className="w-24 h-24 rounded-sm overflow-hidden shrink-0 bg-gray-200 dark:bg-gray-700">
                                                        <img
                                                            src={recentPost.imageUrl || recentPost.featuredImageUrl || '/images/SVG/5.svg'}
                                                            alt={rTitle as string}
                                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                                            onError={(e) => {
                                                                e.currentTarget.src = '/images/SVG/5.svg';
                                                            }}
                                                        />
                                                    </div>
                                                    <div className="flex flex-col justify-center">
                                                        <div className="text-xs text-brand-600 dark:text-brand-400 font-bold mb-1.5 flex items-center gap-1.5">
                                                            <Calendar size={12} /> {recentPost.date}
                                                        </div>
                                                        <h4 className="text-sm font-bold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors line-clamp-2 leading-snug">
                                                            {rTitle as string}
                                                        </h4>
                                                    </div>
                                                </AppLink>
                                            </div>
                                        );
                                    })}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {__('Blog empty')}
                                </p>
                            )}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    );
};

export default BlogPostDetail;