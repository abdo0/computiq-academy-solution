import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { dataService } from '../services/dataService';
import CourseCard from './home/CourseCard';
import { Search, BookOpen, GraduationCap, Users } from 'lucide-react';
import AppLink from './common/AppLink';
import Seo from './Seo';

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

const InstructorCardSkeleton = () => (
    <div className="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 animate-pulse flex items-center gap-4">
        <div className="w-14 h-14 rounded-full bg-gray-200 dark:bg-gray-700 shrink-0" />
        <div className="flex-1">
            <div className="h-5 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-2"></div>
            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
        </div>
    </div>
);

const SearchPage: React.FC = () => {
    const [searchParams] = useSearchParams();
    const query = searchParams.get('q') || '';
    const { language, dir } = useLanguage();
    const { __, t } = useTranslation();

    const [courses, setCourses] = useState<any[]>([]);
    const [instructors, setInstructors] = useState<any[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [coursesTotal, setCoursesTotal] = useState(0);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMore, setHasMore] = useState(false);
    const [isLoadingMore, setIsLoadingMore] = useState(false);

    useEffect(() => {
        if (!query || query.length < 2) {
            setCourses([]);
            setInstructors([]);
            setCoursesTotal(0);
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        setCurrentPage(1);

        dataService.searchGlobal(query, 1).then((data: any) => {
            setCourses(data.courses?.data || []);
            setInstructors(data.instructors || []);
            setCoursesTotal(data.courses?.meta?.total || 0);
            setHasMore((data.courses?.meta?.current_page || 1) < (data.courses?.meta?.last_page || 1));
        }).finally(() => setIsLoading(false));
    }, [query]);

    const loadMore = async () => {
        if (isLoadingMore || !hasMore) return;
        setIsLoadingMore(true);
        const nextPage = currentPage + 1;
        try {
            const data = await dataService.searchGlobal(query, nextPage);
            setCourses(prev => [...prev, ...(data.courses?.data || [])]);
            setCurrentPage(nextPage);
            setHasMore(nextPage < (data.courses?.meta?.last_page || 1));
        } finally {
            setIsLoadingMore(false);
        }
    };

    const totalResults = coursesTotal + instructors.length;

    return (
        <>
            <Seo pageType="search" />

            <div className="min-h-screen">
                {/* Hero Header */}
                <div className="bg-gradient-to-br from-brand-600 via-brand-700 to-brand-800 dark:from-gray-800 dark:via-gray-900 dark:to-gray-950 py-12 sm:py-16">
                    <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                <Search className="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h1 className="text-2xl sm:text-3xl font-bold text-white">
                                    {__('Search Results')}
                                </h1>
                            </div>
                        </div>
                        {query && (
                            <p className="text-brand-100 dark:text-gray-400 text-lg mt-2">
                                {__('Search results for')}: <span className="font-bold text-white">"{query}"</span>
                                {!isLoading && (
                                    <span className="text-brand-200 dark:text-gray-500 text-sm mx-2">
                                        ({totalResults} {__('results')})
                                    </span>
                                )}
                            </p>
                        )}
                    </div>
                </div>

                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
                    {/* No query state */}
                    {(!query || query.length < 2) && !isLoading && (
                        <div className="flex flex-col items-center justify-center py-20 text-center">
                            <div className="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-6 ring-4 ring-gray-50 dark:ring-gray-700">
                                <Search className="w-10 h-10 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">{__('Enter a search term')}</h2>
                            <p className="text-gray-500 dark:text-gray-400 max-w-md">{__('Search for courses, instructors, and more')}</p>
                        </div>
                    )}

                    {/* Loading State */}
                    {isLoading && (
                        <div>
                            {/* Instructor skeletons */}
                            <div className="mb-10">
                                <div className="h-7 bg-gray-200 dark:bg-gray-700 rounded w-40 mb-4 animate-pulse"></div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {[1, 2, 3].map(i => <InstructorCardSkeleton key={i} />)}
                                </div>
                            </div>
                            {/* Course skeletons */}
                            <div className="h-7 bg-gray-200 dark:bg-gray-700 rounded w-40 mb-4 animate-pulse"></div>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                {[1, 2, 3, 4, 5, 6, 7, 8].map(i => <CourseCardSkeleton key={i} />)}
                            </div>
                        </div>
                    )}

                    {/* No results */}
                    {!isLoading && query.length >= 2 && totalResults === 0 && (
                        <div className="flex flex-col items-center justify-center py-20 text-center">
                            <div className="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-6 ring-4 ring-gray-50 dark:ring-gray-700">
                                <Search className="w-10 h-10 text-gray-400 dark:text-gray-500" />
                            </div>
                            <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {__('No results found')}
                            </h2>
                            <p className="text-gray-500 dark:text-gray-400 max-w-md mb-8">
                                {__('No results found for')} "{query}". {__('Try a different search term.')}
                            </p>
                            <AppLink
                                to="/courses"
                                className="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-bold hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-500/25"
                            >
                                <BookOpen className="w-5 h-5" />
                                {__('Browse Courses')}
                            </AppLink>
                        </div>
                    )}

                    {/* Results */}
                    {!isLoading && totalResults > 0 && (
                        <div className="space-y-12">
                            {/* Instructors Section */}
                            {instructors.length > 0 && (
                                <section>
                                    <div className="flex items-center gap-3 mb-6">
                                        <div className="w-10 h-10 rounded-lg bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                            <Users className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">{__('Instructors')}</h2>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{instructors.length} {__('results')}</p>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        {instructors.map((instructor: any) => (
                                            <AppLink
                                                key={instructor.id}
                                                to={`/instructors/${instructor.slug}`}
                                                className="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/50 p-5 flex items-center gap-4 hover:shadow-lg hover:border-brand-200 dark:hover:border-brand-700 transition-all duration-300 group"
                                            >
                                                <div className="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden shrink-0 ring-2 ring-gray-100 dark:ring-gray-600 group-hover:ring-brand-200 dark:group-hover:ring-brand-600 transition-colors">
                                                    {instructor.image ? (
                                                        <img src={instructor.image} alt={t(instructor.name)} className="w-full h-full object-cover" />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 text-xl font-bold">
                                                            {(t(instructor.name) || '?').charAt(0)}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="font-bold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors truncate">
                                                        {t(instructor.name)}
                                                    </h3>
                                                    {instructor.title && (
                                                        <p className="text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5">{t(instructor.title)}</p>
                                                    )}
                                                    <p className="text-xs text-brand-600 dark:text-brand-400 mt-1 flex items-center gap-1">
                                                        <GraduationCap className="w-3.5 h-3.5" />
                                                        {instructor.courses_count} {__('Courses')}
                                                    </p>
                                                </div>
                                            </AppLink>
                                        ))}
                                    </div>
                                </section>
                            )}

                            {/* Courses Section */}
                            {courses.length > 0 && (
                                <section>
                                    <div className="flex items-center gap-3 mb-6">
                                        <div className="w-10 h-10 rounded-lg bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                            <BookOpen className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">{__('Courses')}</h2>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{coursesTotal} {__('results')}</p>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                        {courses.map((course: any) => (
                                            <CourseCard
                                                key={course.id}
                                                courseId={course.id}
                                                image={course.image?.startsWith('http') || course.image?.startsWith('/assets/') ? course.image : `/storage/${course.image}`}
                                                badge={course.is_best_seller ? __('Best Seller') : (course.is_live ? __('Live') : undefined)}
                                                badgeColor={course.is_live ? 'bg-red-500' : 'bg-brand-600'}
                                                title={t(course.title)}
                                                instructor={course.instructor ? t(course.instructor.name) : ''}
                                                instructorImage={course.instructor?.image}
                                                instructorSlug={course.instructor?.slug}
                                                rating={course.rating}
                                                reviewCount={course.review_count}
                                                hours={course.duration_hours}
                                                students={course.students_count}
                                                price={course.price?.toString()}
                                                oldPrice={course.old_price?.toString()}
                                                link={`/courses/${course.slug}`}
                                            />
                                        ))}
                                    </div>

                                    {/* Load More */}
                                    {hasMore && (
                                        <div className="flex justify-center mt-10">
                                            <button
                                                onClick={loadMore}
                                                disabled={isLoadingMore}
                                                className="px-8 py-3.5 bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 border-2 border-brand-200 dark:border-brand-800 rounded-xl font-bold hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all disabled:opacity-50"
                                            >
                                                {isLoadingMore ? (
                                                    <span className="flex items-center gap-2">
                                                        <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                                        </svg>
                                                        {__('Loading...')}
                                                    </span>
                                                ) : __('Load More')}
                                            </button>
                                        </div>
                                    )}
                                </section>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
};

export default SearchPage;
