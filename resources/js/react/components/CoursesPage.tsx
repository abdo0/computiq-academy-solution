import React, { useEffect, useRef, useState } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { dataService } from '../services/dataService';
import CourseCard from './home/CourseCard';
import { Search, Filter, BookOpen } from 'lucide-react';
import Seo from './Seo';
import AppLink from './common/AppLink';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const CourseCardSkeleton = () => (
    <div className="bg-white dark:bg-slate-900 rounded-md overflow-hidden border border-gray-100 dark:border-slate-800 shadow-sm flex flex-col h-full animate-pulse">
        <div className="aspect-[16/10] bg-gray-200 dark:bg-slate-800 w-full" />
        <div className="p-6 flex-1 flex flex-col relative">
            <div className="absolute -top-6 start-6 w-12 h-12 rounded-full bg-gray-300 dark:bg-slate-700 ring-4 ring-white dark:ring-slate-900" />
            <div className="h-4 bg-gray-200 dark:bg-slate-800 rounded w-1/4 mt-8 mb-4"></div>
            <div className="h-6 bg-gray-200 dark:bg-slate-800 rounded w-full mb-2"></div>
            <div className="h-6 bg-gray-200 dark:bg-slate-800 rounded w-2/3 mb-6"></div>
            
            <div className="flex gap-2 mb-6">
                <div className="h-8 bg-gray-200 dark:bg-slate-800 rounded w-20"></div>
                <div className="h-8 bg-gray-200 dark:bg-slate-800 rounded w-20"></div>
            </div>
            
            <div className="flex-1"></div>
            <div className="flex justify-between items-center pt-5 mt-2 border-t border-gray-100 dark:border-slate-800">
                <div className="h-8 bg-gray-200 dark:bg-slate-800 rounded w-24"></div>
                <div className="h-10 bg-gray-200 dark:bg-slate-800 rounded w-32 rounded-[14px]"></div>
            </div>
        </div>
    </div>
);

const CoursesPage: React.FC = () => {
    const { language, dir } = useLanguage();
    const { __ } = useTranslation();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const initialCoursesResponse = initialBootstrap?.courses;
    const initialCourses = initialCoursesResponse?.data || [];
    const initialMeta = initialCoursesResponse?.meta || { current_page: 1, last_page: 1 };
    const initialCategories = initialBootstrap?.categories || [];
    const skipInitialListingFetch = useRef(initialCourses.length > 0);
    
    const [courses, setCourses] = useState<any[]>(() => initialCourses);
    const [categories, setCategories] = useState<any[]>(() => initialCategories);
    const [isLoading, setIsLoading] = useState(() => initialCourses.length === 0);
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    
    // Pagination and Filters State
    const [currentPage, setCurrentPage] = useState(initialMeta.current_page || 1);
    const [hasMore, setHasMore] = useState((initialMeta.current_page || 1) < (initialMeta.last_page || 1));
    const [searchQuery, setSearchQuery] = useState('');
    const [activeCategory, setActiveCategory] = useState<string>('all');
    const [sort, setSort] = useState('newest');

    useEffect(() => {
        if (initialCourses.length === 0 && initialCategories.length === 0) {
            return;
        }

        setCourses(initialCourses);
        setCategories(initialCategories);
        setCurrentPage(initialMeta.current_page || 1);
        setHasMore((initialMeta.current_page || 1) < (initialMeta.last_page || 1));
        setIsLoading(false);
        skipInitialListingFetch.current = true;
    }, [initialCategories, initialCourses, initialMeta.current_page, initialMeta.last_page]);

    useEffect(() => {
        if (initialCategories.length > 0) {
            return;
        }

        // Load categories on mount
        dataService.getCategories().then(cats => setCategories(cats || []));
    }, [initialCategories.length]);

    const fetchCourses = async (page: number, append = false) => {
        try {
            if (!append) setIsLoading(true);
            else setIsLoadingMore(true);

            const params = {
                page,
                per_page: 12,
                search: searchQuery || undefined,
                category: activeCategory !== 'all' ? activeCategory : undefined,
                sort,
            };

            const response = await dataService.getCourses(params);
            
            if (append) {
                setCourses(prev => [...prev, ...(response.data || [])]);
            } else {
                setCourses(response.data || []);
            }
            
            const meta = response.meta || { current_page: 1, last_page: 1 };
            setHasMore(meta.current_page < meta.last_page);
            setCurrentPage(meta.current_page);
            
        } catch (error) {
            console.error("Failed to fetch courses", error);
        } finally {
            setIsLoading(false);
            setIsLoadingMore(false);
        }
    };

    // Refetch when filters change
    useEffect(() => {
        if (skipInitialListingFetch.current) {
            skipInitialListingFetch.current = false;
            return;
        }

        fetchCourses(1, false);
    }, [activeCategory, sort, language]);

    // Handle Search Submit
    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        fetchCourses(1, false);
    };

    const handleLoadMore = () => {
        if (!isLoadingMore && hasMore) {
            fetchCourses(currentPage + 1, true);
        }
    };

    return (
        <div className="bg-[#fcfdfd] dark:bg-slate-950 min-h-screen py-10 pb-24">
            <Seo 
                title={__('Courses')} 
                description={__('Courses description')}
            />

            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                
                {/* Header Section */}
                <div className="mb-12 text-center md:text-start flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 text-sm font-bold mb-4">
                            <BookOpen className="w-4 h-4" />
                            <span>{__('Our courses')}</span>
                        </div>
                        <h1 className="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                            {__('Explore courses')}
                        </h1>
                        <p className="mt-4 text-gray-600 dark:text-gray-400 text-lg max-w-2xl">
                            {__('Explore courses description')}
                        </p>
                    </div>

                    {/* Search Bar */}
                    <form onSubmit={handleSearchSubmit} className="relative w-full md:w-80 group">
                        <div className="absolute inset-y-0 ltr:left-0 rtl:right-0 flex items-center ltr:pl-4 rtl:pr-4 pointer-events-none">
                            <Search className="w-5 h-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                        </div>
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="block w-full ltr:pl-11 ltr:pr-4 rtl:pr-11 rtl:pl-4 py-3.5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-md leading-5 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-shadow shadow-sm"
                            placeholder={__('Search courses')}
                        />
                        <button type="submit" className="hidden"></button>
                    </form>
                </div>

                <div className="flex flex-col lg:flex-row gap-8 items-start">
                    
                    {/* Sidebar / Filters */}
                    <div className="w-full lg:w-64 flex-shrink-0 space-y-8 sticky top-24">
                        
                        {/* Categories Box */}
                        <div className="bg-white dark:bg-slate-900 rounded-md p-5 border border-gray-100 dark:border-slate-800 shadow-sm">
                            <div className="flex items-center gap-2 mb-4">
                                <Filter className="w-5 h-5 text-gray-400" />
                                <h3 className="font-bold text-gray-900 dark:text-white">
                                    {__('Categories')}
                                </h3>
                            </div>
                            <div className="space-y-1">
                                <button
                                    onClick={() => setActiveCategory('all')}
                                    className={`w-full text-start px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                                        activeCategory === 'all' 
                                        ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' 
                                        : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-slate-800'
                                    }`}
                                >
                                    {__('All categories')}
                                </button>
                                {categories.map(cat => (
                                    <button
                                        key={cat.id}
                                        onClick={() => setActiveCategory(cat.slug)}
                                        className={`w-full text-start px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                                            activeCategory === cat.slug 
                                            ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400' 
                                            : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-slate-800'
                                        }`}
                                    >
                                        {cat.name?.[language] || cat.name?.['en']}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Sort Box */}
                        <div className="bg-white dark:bg-slate-900 rounded-md p-5 border border-gray-100 dark:border-slate-800 shadow-sm">
                            <h3 className="font-bold text-gray-900 dark:text-white mb-4">
                                {__('Sort by')}
                            </h3>
                            <div className="space-y-2">
                                {[
                                    { id: 'newest', label: __('Newest') },
                                    { id: 'popular', label: __('Popular') },
                                    { id: 'price_low', label: __('Price low') },
                                    { id: 'price_high', label: __('Price high') }
                                ].map(option => (
                                    <label key={option.id} className="flex items-center gap-3 cursor-pointer group">
                                        <div className="relative flex items-center justify-center w-5 h-5">
                                            <input 
                                                type="radio" 
                                                name="sort" 
                                                value={option.id}
                                                checked={sort === option.id}
                                                onChange={(e) => setSort(e.target.value)}
                                                className="peer appearance-none w-5 h-5 border-2 border-gray-300 dark:border-slate-600 rounded-full checked:border-brand-500 checked:bg-brand-50 transition-colors"
                                            />
                                            <div className="absolute w-2.5 h-2.5 bg-brand-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </div>
                                        <span className="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                            {option.label}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </div>

                    </div>

                    {/* Main Courses Grid */}
                    <div className="flex-1 w-full">
                        {isLoading ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                {[...Array(6)].map((_, i) => <CourseCardSkeleton key={i} />)}
                            </div>
                        ) : courses.length > 0 ? (
                            <>
                                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                    {courses.map(course => (
                                        <CourseCard
                                            key={course.id}
                                            image={course.image}
                                            badge={course.badge || (course.is_live ? __('Live course') : (course.is_best_seller ? __('Best seller') : undefined))}
                                            badgeColor={course.is_live ? 'bg-red-500' : 'bg-brand-500'}
                                            title={course.title?.[language] || course.title?.['en'] || ''}
                                            instructor={course.instructor?.name?.[language] || course.instructor?.name?.['en'] || ''}
                                            instructorImage={course.instructor?.image}
                                            instructorSlug={course.instructor?.slug}
                                            rating={course.rating}
                                            reviewCount={course.review_count}
                                            hours={course.duration_hours}
                                            students={course.students_count}
                                            price={course.price}
                                            oldPrice={course.old_price}
                                            link={`/courses/${course.slug}`}
                                            courseId={course.id}
                                        />
                                    ))}
                                </div>
                                
                                {/* Load More Button */}
                                {hasMore && (
                                    <div className="mt-12 flex justify-center">
                                        <button
                                            onClick={handleLoadMore}
                                            disabled={isLoadingMore}
                                            className="px-8 py-3 bg-white dark:bg-slate-900 border border-brand-200 dark:border-slate-700 text-brand-600 dark:text-brand-400 font-bold rounded-md shadow-sm hover:shadow hover:-translate-y-0.5 transition-all disabled:opacity-70 disabled:pointer-events-none flex items-center gap-2"
                                        >
                                            {isLoadingMore ? (
                                                <>
                                                    <span className="w-5 h-5 border-2 border-brand-600 border-t-transparent rounded-full animate-spin"></span>
                                                    {__('Loading')}
                                                </>
                                            ) : (
                                                __('Load more')
                                            )}
                                        </button>
                                    </div>
                                )}
                            </>
                        ) : (
                            /* Empty State */
                            <div className="bg-white dark:bg-slate-900 rounded-md p-12 text-center border border-gray-100 dark:border-slate-800 shadow-sm flex flex-col items-center justify-center min-h-[400px]">
                                <div className="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                                    <BookOpen className="w-10 h-10 text-gray-400" />
                                </div>
                                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                    {__('No courses found')}
                                </h3>
                                <p className="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-8">
                                    {__('No courses desc')}
                                </p>
                                <button 
                                    onClick={() => {
                                        setSearchQuery('');
                                        setActiveCategory('all');
                                        setSort('Newest');
                                    }}
                                    className="px-6 py-2.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 font-bold rounded-md hover:bg-brand-100 dark:hover:bg-brand-900/40 transition-colors"
                                >
                                    {__('Clear filters')}
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CoursesPage;
