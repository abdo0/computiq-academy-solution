import React from 'react';
import { useParams } from 'react-router-dom';
import AppLink from './common/AppLink';
import { useAppNavigate } from '../hooks/useAppNavigate';
import { BlogPost } from '../types';
import { useLanguage } from '../contexts/LanguageContext';
import { Calendar, User, ArrowUpRight } from 'lucide-react';
import { useTranslation } from '../contexts/TranslationProvider';

interface BlogCardProps {
    post: BlogPost;
}

const BlogCard: React.FC<BlogCardProps> = ({ post }) => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();

    const getPostContent = () => {
        // Fallback for localized fields if needed (similarly to how it was in BlogPage)
        return { title: post.title, excerpt: post.excerpt };
    };

    const { title, excerpt } = getPostContent();

    return (
        <div className="flex flex-col bg-white dark:bg-gray-900/80 rounded-sm overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-2xl border border-gray-100 dark:border-gray-800 transition-all duration-500 group h-[460px]">
            {/* Image Area */}
            <AppLink to={`/blog/${post.slug}`} className="block overflow-hidden rounded-t-sm relative group">
                <div className="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-emerald-500/10 to-transparent mix-blend-overlay z-0"></div>
                <img
                    src={post.imageUrl || post.featuredImageUrl || '/images/SVG/5.svg'}
                    alt={title}
                    className="w-full h-full object-cover transform group-hover:scale-110 group-hover:-rotate-1 transition-all duration-700 ease-out"
                    onError={(e) => {
                        e.currentTarget.src = '/images/SVG/5.svg';
                    }}
                />

                {/* Gradient Overlays */}
                <div className="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent"></div>
                <div className="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors duration-500"></div>

                {/* Floating Top Elements */}
                <div className="absolute top-4 left-4 right-4 flex justify-between items-start z-10 pointer-events-none">
                    <div className="bg-white/20 backdrop-blur-md rounded-sm px-3 py-1.5 text-xs font-bold text-white shadow-sm border border-white/20 uppercase tracking-widest pointer-events-auto flex items-center gap-1.5">
                        <Calendar size={12} className="opacity-80" /> {post.date}
                    </div>
                </div>

                {/* Title inside image for consistency with CampaignCard */}
                <div className="absolute bottom-0 inset-x-0 p-4 z-20">
                    <h3 className="text-xl font-black text-white leading-tight line-clamp-2 drop-shadow-md group-hover:text-emerald-300 transition-colors">
                        {title}
                    </h3>
                </div>
            </AppLink>

            {/* Content Area */}
            <div className="flex-1 p-5 lg:p-6 flex flex-col justify-between relative bg-white dark:bg-gray-900">
                <div className="mb-6">
                    <div className="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-widest">
                        <User size={14} className="text-emerald-500" />
                        <span>{post.author}</span>
                    </div>

                    <p className="text-gray-500 dark:text-gray-400 text-sm leading-relaxed line-clamp-3">
                        {excerpt ? excerpt.replace(/<[^>]*>?/gm, '') : ''}
                    </p>
                </div>

                {/* Action Button */}
                <div className="flex mt-auto">
                    <button
                        onClick={() => navigate(`/blog/${post.slug}`)}
                        className="w-full bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-800 hover:from-emerald-50 hover:to-emerald-50 dark:hover:from-emerald-900/30 dark:hover:to-emerald-900/20 text-gray-700 dark:text-gray-200 hover:text-emerald-600 dark:hover:text-emerald-400 border border-gray-200 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-700 py-3 rounded-sm font-bold text-sm transition-all flex items-center justify-center gap-2 group/btn shadow-sm"
                    >
                        {__('Blog read more', 'Read More')}
                        <ArrowUpRight size={18} className="rtl:rotate-270 opacity-60 group-hover/btn:opacity-100 group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5 transition-transform" />
                    </button>
                </div>
            </div>
        </div>
    );
};

export const BlogCardSkeleton = () => (
    <div className="flex flex-col bg-white dark:bg-gray-900/80 rounded-sm overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800 h-[460px] animate-pulse">
        {/* Image Area Skeleton */}
        <div className="relative h-64 bg-gray-200 dark:bg-gray-800 shrink-0 w-full overflow-hidden">
            <div className="absolute top-4 left-4 right-4 flex justify-between items-start z-10">
                <div className="h-6 w-28 bg-gray-300 dark:bg-gray-700/50 rounded-sm"></div>
            </div>

            <div className="absolute bottom-0 inset-x-0 p-4 z-20 space-y-2">
                <div className="h-6 w-full bg-gray-300 dark:bg-gray-700/50 rounded-sm"></div>
                <div className="h-6 w-2/3 bg-gray-300 dark:bg-gray-700/50 rounded-sm"></div>
            </div>
        </div>

        {/* Content Area Skeleton */}
        <div className="flex-1 p-5 lg:p-6 flex flex-col justify-between bg-white dark:bg-gray-900">
            <div className="mb-6 space-y-3">
                <div className="flex gap-2 items-center">
                    <div className="h-4 w-4 rounded-full bg-gray-200 dark:bg-gray-800"></div>
                    <div className="h-3 w-20 bg-gray-200 dark:bg-gray-800 rounded-sm"></div>
                </div>
                <div className="h-3 w-full bg-gray-200 dark:bg-gray-800 rounded-sm"></div>
                <div className="h-3 w-full bg-gray-200 dark:bg-gray-800 rounded-sm"></div>
                <div className="h-3 w-4/5 bg-gray-200 dark:bg-gray-800 rounded-sm"></div>
            </div>

            {/* Action Buttons Skeleton */}
            <div className="flex mt-auto">
                <div className="w-full h-11 bg-gray-200 dark:bg-gray-800 rounded-sm"></div>
            </div>
        </div>
    </div>
);

export default BlogCard;
