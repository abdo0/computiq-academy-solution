import React, { useEffect, useRef, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useTranslation } from '../contexts/TranslationProvider';
import { dataService } from '../services/dataService';
import { 
    Star, PlayCircle, Users, Clock, Award, Infinity as InfinityIcon, BookOpen,
    Globe, Share2, ShoppingCart, CheckCircle2, ChevronDown, ChevronUp, User, Loader2, X, Lock
} from 'lucide-react';
import { toast } from 'react-toastify';
import AppLink from './common/AppLink';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';
import { useCurrency } from '../utils/currency';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';
import { useAppNavigate } from '../hooks/useAppNavigate';

const CourseDetailsPage: React.FC = () => {
    const { slug } = useParams<{ slug: string }>();
    const { __, t } = useTranslation();
    const { addToCart, isInCart } = useCart();
    const { user } = useAuth();
    const { formatAmount } = useCurrency();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const appNavigate = useAppNavigate();

    const [course, setCourse] = useState<any>(() => initialBootstrap?.course ?? null);
    const [loading, setLoading] = useState(() => !initialBootstrap?.course);
    const [activeTab, setActiveTab] = useState(1);
    const [openModules, setOpenModules] = useState<number[]>([0, 1]);
    const [isAdding, setIsAdding] = useState(false);
    const [reviews, setReviews] = useState<any[]>(() => initialBootstrap?.course?.reviews || []);
    const [reviewsMeta, setReviewsMeta] = useState(() => ({ current_page: 1, last_page: 1, total: initialBootstrap?.course?.reviews?.length || 0 }));
    const [reviewsLoading, setReviewsLoading] = useState(false);
    const [reviewsLoaded, setReviewsLoaded] = useState(false);
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewComment, setReviewComment] = useState('');
    const [reviewSubmitting, setReviewSubmitting] = useState(false);
    const [selectedPreviewLessonId, setSelectedPreviewLessonId] = useState<number | null>(null);
    const [lessonPreview, setLessonPreview] = useState<any | null>(null);
    const [lessonPreviewCache, setLessonPreviewCache] = useState<Record<number, any>>({});
    const [lessonPreviewLoading, setLessonPreviewLoading] = useState(false);
    const [lessonPreviewError, setLessonPreviewError] = useState<string | null>(null);
    const [isPreviewModalOpen, setIsPreviewModalOpen] = useState(false);
    const [isTrailerModalOpen, setIsTrailerModalOpen] = useState(false);
    const [isTrailerModalPriming, setIsTrailerModalPriming] = useState(false);
    const previewRequestRef = useRef(0);

    const handleAddToCart = async () => {
        if (course?.id && isEnrolled) {
            await appNavigate(`/learn/${course.slug}`);
            return;
        }

        if (course?.id && !isInCart(course.id) && !isAdding) {
            setIsAdding(true);
            await addToCart(course.id);
            setIsAdding(false);
        } else if (course?.id && isInCart(course.id)) {
            await appNavigate('/cart');
        }
    };



    useEffect(() => {
        if (!slug) return;

        if (initialBootstrap?.course) {
            setCourse(initialBootstrap.course);
            setReviews(initialBootstrap.course.reviews || []);
            setReviewsMeta({
                current_page: 1,
                last_page: 1,
                total: initialBootstrap.course.review_count || initialBootstrap.course.reviews?.length || 0,
            });
            setReviewsLoaded(false);
            setLoading(false);
            return;
        }

        setLoading(true);
        dataService.getCourseBySlug(slug).then((data) => {
            setCourse(data);
            setReviews(data?.reviews || []);
            setReviewsMeta({ current_page: 1, last_page: 1, total: data?.review_count || data?.reviews?.length || 0 });
            setReviewsLoaded(false);
            setLoading(false);
        });
    }, [initialBootstrap, slug]);

    useEffect(() => {
        setSelectedPreviewLessonId(null);
        setLessonPreview(null);
        setLessonPreviewCache({});
        setLessonPreviewLoading(false);
        setLessonPreviewError(null);
        setIsPreviewModalOpen(false);
        setIsTrailerModalOpen(false);
        setIsTrailerModalPriming(false);
        previewRequestRef.current = 0;
    }, [slug]);

    useEffect(() => {
        if (!isPreviewModalOpen && !isTrailerModalOpen) {
            document.body.style.removeProperty('overflow');
            return;
        }

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        const handleEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setIsPreviewModalOpen(false);
                setIsTrailerModalOpen(false);
                setLessonPreviewError(null);
            }
        };

        window.addEventListener('keydown', handleEscape);

        return () => {
            document.body.style.overflow = previousOverflow;
            window.removeEventListener('keydown', handleEscape);
        };
    }, [isPreviewModalOpen, isTrailerModalOpen]);

    useEffect(() => {
        if (!isTrailerModalOpen) {
            setIsTrailerModalPriming(false);
            return;
        }

        setIsTrailerModalPriming(true);
        const timer = window.setTimeout(() => {
            setIsTrailerModalPriming(false);
        }, 160);

        return () => {
            window.clearTimeout(timer);
        };
    }, [isTrailerModalOpen]);

    useEffect(() => {
        if (!slug || activeTab !== 3 || reviewsLoaded || reviewsLoading) {
            return;
        }

        let isMounted = true;
        setReviewsLoading(true);

        dataService.getCourseReviews(slug)
            .then((response) => {
                if (!isMounted) {
                    return;
                }

                setReviews(response?.data || []);
                setReviewsMeta(response?.meta || { current_page: 1, last_page: 1, total: 0 });
                setReviewsLoaded(true);
            })
            .finally(() => {
                if (isMounted) {
                    setReviewsLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
    }, [activeTab, reviewsLoaded, reviewsLoading, slug]);

    const toggleModule = (index: number) => {
        setOpenModules(prev => 
            prev.includes(index) ? prev.filter(i => i !== index) : [...prev, index]
        );
    };

    const formatDuration = (minutes: number) => {
        const safeMinutes = Math.max(0, Math.floor(minutes || 0));
        const hours = Math.floor(safeMinutes / 60);
        const remainingMinutes = safeMinutes % 60;
        const parts: string[] = [];

        if (hours > 0) {
            parts.push(`${hours} ${__('hr')}`);
        }

        if (remainingMinutes > 0 || parts.length === 0) {
            parts.push(`${remainingMinutes} ${__('min')}`);
        }

        return parts.join(' ');
    };

    const buildAutoplayEmbedUrl = (url?: string | null) => {
        if (!url) {
            return '';
        }

        try {
            const autoplayUrl = new URL(url);
            autoplayUrl.searchParams.set('autoplay', '1');

            if (autoplayUrl.hostname.includes('youtube.com')) {
                autoplayUrl.searchParams.set('rel', '0');
            }

            return autoplayUrl.toString();
        } catch {
            return url;
        }
    };

    const deliveryTypeLabel = course?.delivery_type === 'onsite'
        ? __('On-site')
        : course?.delivery_type === 'hybrid'
            ? __('Hybrid')
            : __('Online');
    const deliveryTypeChipClass = course?.delivery_type === 'onsite'
        ? 'bg-amber-500 text-amber-950'
        : course?.delivery_type === 'hybrid'
            ? 'bg-sky-400 text-sky-950'
            : 'bg-emerald-400 text-emerald-950';

    const loadReviewPage = async (page: number) => {
        if (!slug || reviewsLoading) {
            return;
        }

        setReviewsLoading(true);
        try {
            const response = await dataService.getCourseReviews(slug, page);
            const nextReviews = response?.data || [];
            setReviews((current) => page === 1
                ? nextReviews
                : [...current, ...nextReviews.filter((review: any) => !current.some((existing) => existing.id === review.id))]);
            setReviewsMeta(response?.meta || { current_page: 1, last_page: 1, total: 0 });
            setReviewsLoaded(true);
        } finally {
            setReviewsLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-[#f8fafc] dark:bg-gray-950">
                <Loader2 className="w-10 h-10 text-brand-600 animate-spin" />
            </div>
        );
    }

    if (!course) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-[#f8fafc] dark:bg-gray-950">
                <div className="text-center">
                    <h2 className="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                        {__('Course Not Found')}
                    </h2>
                    <AppLink to="/" className="text-brand-600 font-bold mt-4 inline-block">
                        {__('Go Home')}
                    </AppLink>
                </div>
            </div>
        );
    }

    const reviewsCount = course.review_count || reviewsMeta.total || 0;
    const averageRating = Number(course.rating || 0);
    const tabs = [
        { id: 'overview', label: __('Overview') },
        { id: 'content', label: __('Course Content') },
        { id: 'instructor', label: __('Instructor') },
        {
            id: 'reviews',
            label: __('Reviews'),
            rating: averageRating.toFixed(1),
            count: reviewsCount,
        },
    ];

    const totalLessons = course.modules?.reduce((sum: number, m: any) => sum + (m.lessons_count || m.lessons?.length || 0), 0) || 0;
    const totalDurationMin = course.modules?.reduce((sum: number, m: any) => sum + (m.duration_minutes || 0), 0) || 0;
    const freeLessons = course.modules?.reduce((sum: number, m: any) => 
        sum + (m.lessons?.filter((l: any) => l.is_free)?.length || 0), 0) || 0;
    const hasFreePreviewLessons = Boolean(course.modules?.some((module: any) =>
        module.lessons?.some((lesson: any) => lesson.is_preview_available ?? lesson.is_free)
    ));

    const instructorSlug = course.instructor?.slug;
    const isEnrolled = Boolean(user?.purchasedCourseIds?.includes(course.id));
    const canSubmitReview = Boolean(user && isEnrolled);
    const hasPromoVideo = Boolean(course?.has_promo_video && course?.promo_video);

    const handleReviewSubmit = async () => {
        if (!slug || !canSubmitReview || reviewSubmitting) {
            return;
        }

        setReviewSubmitting(true);

        const result = await dataService.submitCourseReview(slug, reviewRating, reviewComment);

        if (result.success) {
            setCourse((current: any) => current ? ({
                ...current,
                rating: result.data?.course_summary?.rating ?? current.rating,
                review_count: result.data?.course_summary?.review_count ?? current.review_count,
            }) : current);
            setReviewComment('');
            setReviewRating(5);
            toast.success(result.message || __('Review submitted successfully.'));
            await loadReviewPage(1);
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }

        setReviewSubmitting(false);
    };

    const handleLessonPreview = async (lesson: any) => {
        const isPreviewAvailable = Boolean(lesson?.is_preview_available ?? lesson?.is_free);

        if (!slug || !lesson?.id || !isPreviewAvailable) {
            return;
        }

        setSelectedPreviewLessonId(lesson.id);
        setLessonPreviewError(null);
        setIsPreviewModalOpen(true);

        if (lessonPreviewCache[lesson.id]) {
            setLessonPreview(lessonPreviewCache[lesson.id]);
            return;
        }

        const requestId = previewRequestRef.current + 1;
        previewRequestRef.current = requestId;
        setLessonPreview(null);
        setLessonPreviewLoading(true);

        const payload = await dataService.getCourseLessonPreview(slug, lesson.id);

        if (previewRequestRef.current !== requestId) {
            return;
        }

        if (payload) {
            setLessonPreview(payload);
            setLessonPreviewCache((current) => ({
                ...current,
                [lesson.id]: payload,
            }));
        } else {
            setLessonPreview(null);
            setLessonPreviewError(__('Preview unavailable'));
        }

        setLessonPreviewLoading(false);
    };

    const closePreviewModal = () => {
        setIsPreviewModalOpen(false);
        setLessonPreviewError(null);
    };

    const openTrailerModal = () => {
        if (!hasPromoVideo) {
            return;
        }

        setIsTrailerModalOpen(true);
    };

    const closeTrailerModal = () => {
        setIsTrailerModalOpen(false);
    };

    return (
        <div className="bg-[#f8fafc] dark:bg-gray-950 min-h-screen pb-20">
            {/* Header / Hero Section */}
            <div className="bg-[#0b1021] dark:bg-slate-900 text-white min-h-[300px] md:min-h-[360px] relative mt-1 lg:mt-6 max-w-screen-2xl mx-auto rounded-none lg:rounded-md overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-r from-brand-900/40 to-transparent pointer-events-none"></div>
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-12 py-12 md:py-16 h-full flex items-center relative z-10">
                    <div className="w-full lg:w-[60%]">
                        <h1 className="text-3xl md:text-4xl lg:text-5xl font-black mb-4 leading-tight">
                            {t(course.title)}
                        </h1>
                        <p className="text-gray-300 text-sm md:text-base leading-relaxed mb-8 max-w-2xl">
                            {t(course.short_description)}
                        </p>
                        
                        <div className="flex flex-wrap items-center gap-4 text-sm font-medium">
                            {course.is_best_seller && (
                                <span className="bg-[#fcd34d] text-amber-900 px-3 py-1 rounded-sm font-bold text-xs uppercase tracking-wider">
                                    {__('Best Seller')}
                                </span>
                            )}
                            {course.is_live && (
                                <span className="bg-red-500 text-white px-3 py-1 rounded-sm font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                    {__('Live')}
                                </span>
                            )}
                            <span className={`${deliveryTypeChipClass} px-3 py-1 rounded-sm font-bold text-xs uppercase tracking-wider`}>
                                {deliveryTypeLabel}
                            </span>
                            <div className="flex items-center gap-1.5 border border-white/20 px-3 py-1 rounded-sm bg-white/5">
                                <Star className="w-4 h-4 text-[#fcd34d] fill-[#fcd34d]" />
                                <span>{course.rating?.toFixed(1)}</span>
                            </div>
                            <div className="flex items-center gap-1.5 border border-white/20 px-3 py-1 rounded-sm bg-white/5">
                                <Users className="w-4 h-4" />
                                <span>{course.students_count?.toLocaleString()} {__('Learners')}</span>
                            </div>
                        </div>

                        <div className="mt-8 pt-6 border-t border-white/10 flex items-center gap-2 text-sm text-gray-300">
                            {__('By')}{' '}
                            <AppLink 
                                to={instructorSlug ? `/instructors/${instructorSlug}` : '#'}
                                className="text-white font-bold underline decoration-brand-500 underline-offset-4 cursor-pointer hover:text-brand-400 transition-colors inline-block"
                            >
                                {t(course.instructor?.name)}
                            </AppLink>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Layout */}
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                <div className="flex flex-col lg:flex-row gap-8 lg:gap-12 relative items-start">
                    
                    {/* Left Column - Content */}
                    <div className="w-full lg:w-[65%] order-2 lg:order-1 flex flex-col gap-12 pb-12">
                        
                        {/* Horizontal Tabs */}
                        <div className="flex items-center gap-3 border-b border-gray-200 dark:border-gray-800 overflow-x-auto scrollbar-hide">
                            {tabs.map((tab, index) => (
                                <button
                                    key={tab.id}
                                    onClick={() => setActiveTab(index)}
                                    className={`group relative flex items-center gap-2 whitespace-nowrap rounded-t-2xl px-3 py-3 text-sm font-bold transition-all ${
                                        activeTab === index
                                            ? 'text-brand-600 dark:text-brand-400'
                                            : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                                    }`}
                                >
                                    <span>{tab.label}</span>
                                    {'rating' in tab ? (
                                        <span
                                            className={`inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[11px] font-black leading-none ${
                                                activeTab === index
                                                    ? 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300'
                                                    : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300'
                                            }`}
                                        >
                                            <Star className="h-3.5 w-3.5 fill-current" />
                                            <span>{tab.rating}</span>
                                            <span className="text-gray-400 dark:text-gray-500">|</span>
                                            <span>{tab.count}</span>
                                        </span>
                                    ) : null}
                                    {activeTab === index && (
                                        <div className="absolute bottom-0 left-3 right-3 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-t-full"></div>
                                    )}
                                </button>
                            ))}
                        </div>

                        {/* Overview Section */}
                        {activeTab === 0 && (
                            <div id="overview" className="scroll-mt-24">
                                <h2 className="text-xl font-bold text-brand-600 dark:text-brand-400 mb-4 tracking-tight">
                                    {__('Overview')}
                                </h2>
                                <p className="text-sm md:text-base text-gray-700 dark:text-gray-300 leading-loose whitespace-pre-line">
                                    {t(course.description)}
                                </p>
                            </div>
                        )}

                        {/* Course Content Section */}
                        {activeTab === 1 && (
                            <div id="course-content" className="scroll-mt-24">
                                <h2 className="text-xl font-bold text-brand-600 dark:text-brand-400 mb-4 tracking-tight">
                                    {__('Course Content')}
                                </h2>

                                <div className="flex items-center gap-6 mb-6 text-sm font-bold text-gray-600 dark:text-gray-400">
                                    <div className="flex items-center gap-2"><PlayCircle className="w-4 h-4 text-brand-500"/> {totalLessons} {__('Lessons')}</div>
                                    {freeLessons > 0 && (
                                        <div className="flex items-center gap-2"><Award className="w-4 h-4 text-brand-500"/> {freeLessons} {__('Free Lessons')}</div>
                                    )}
                                    <div className="flex items-center gap-2"><Clock className="w-4 h-4 text-brand-500"/> {formatDuration(totalDurationMin)}</div>
                                </div>

                                <div className="flex flex-col gap-3">
                                    {course.modules?.map((module: any, index: number) => {
                                        const isOpen = openModules.includes(index);
                                        return (
                                            <div key={module.id || index} className="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-md overflow-hidden transition-all shadow-sm">
                                                <button 
                                                    onClick={() => toggleModule(index)}
                                                    className="w-full flex items-center justify-between p-4 md:p-5 text-start hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                                                >
                                                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 flex-1 pr-4">
                                                        <span className="font-bold text-sm md:text-base text-gray-900 dark:text-white">{t(module.title)}</span>
                                                        <div className="flex items-center gap-3 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                                            <PlayCircle className="w-4 h-4"/>
                                                            <span>{formatDuration(module.duration_minutes || 0)}</span>
                                                            <span className="hidden sm:inline">•</span>
                                                            <span>{module.lessons_count || module.lessons?.length || 0} {__('Lessons')}</span>
                                                        </div>
                                                    </div>
                                                    {isOpen ? <ChevronUp className="w-5 h-5 text-gray-400 shrink-0"/> : <ChevronDown className="w-5 h-5 text-gray-400 shrink-0"/>}
                                                </button>
                                                
                                                {isOpen && module.lessons?.length > 0 && (
                                                    <div className="p-4 md:p-5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                                                        <div className="flex flex-col gap-3">
                                                            {module.lessons.map((lesson: any, lIdx: number) => (
                                                                <button
                                                                    key={lesson.id || lIdx}
                                                                    type="button"
                                                                    onClick={() => void handleLessonPreview(lesson)}
                                                                    disabled={!(lesson.is_preview_available ?? lesson.is_free)}
                                                                    className={`flex flex-col gap-2 rounded-md border p-3 text-start transition-all sm:flex-row sm:items-center sm:justify-between ${
                                                                        selectedPreviewLessonId === lesson.id
                                                                            ? 'border-brand-200 bg-brand-50 dark:border-brand-800 dark:bg-brand-900/20'
                                                                            : 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800'
                                                                    } ${
                                                                        (lesson.is_preview_available ?? lesson.is_free)
                                                                            ? 'cursor-pointer hover:border-brand-200 hover:bg-brand-50/50 dark:hover:border-brand-800 dark:hover:bg-brand-900/10'
                                                                            : 'cursor-default'
                                                                    }`}
                                                                >
                                                                    <div className="flex items-center gap-3">
                                                                        {(lesson.is_preview_available ?? lesson.is_free) ? (
                                                                            <PlayCircle className="w-4 h-4 text-brand-600" />
                                                                        ) : (
                                                                            <Lock className="w-4 h-4 text-amber-500" />
                                                                        )}
                                                                        <div className="flex min-w-0 flex-col gap-1">
                                                                            <span className={`text-sm font-medium ${(lesson.is_preview_available ?? lesson.is_free) ? 'text-brand-700 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300'}`}>
                                                                                {t(lesson.title)}
                                                                            </span>
                                                                            {(lesson.is_preview_available ?? lesson.is_free) ? (
                                                                                <span className="text-[11px] font-bold text-brand-600 dark:text-brand-300">
                                                                                    {__('Preview this lesson')}
                                                                                </span>
                                                                            ) : null}
                                                                        </div>
                                                                    </div>
                                                                    <div className="flex items-center gap-2 self-end sm:self-auto">
                                                                        {lesson.is_free && (
                                                                            <span className="rounded-xl bg-emerald-100 px-2.5 py-1 text-[10px] font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                                                                {__('Free Preview')}
                                                                            </span>
                                                                        )}
                                                                        {!(lesson.is_preview_available ?? lesson.is_free) && (
                                                                            <span className="inline-flex items-center gap-1 rounded-xl bg-amber-100 px-2.5 py-1 text-[10px] font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                                                                <Lock className="h-3 w-3" />
                                                                                <span>{__('Locked')}</span>
                                                                            </span>
                                                                        )}
                                                                        <span className="text-xs font-bold text-gray-400">{formatDuration(lesson.duration_minutes)}</span>
                                                                    </div>
                                                                </button>
                                                            ))}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        )
                                    })}
                                </div>
                            </div>
                        )}

                        {/* Instructor */}
                        {activeTab === 2 && course.instructor && (
                            <div id="instructor" className="scroll-mt-24">
                                <h2 className="text-xl font-bold text-brand-600 dark:text-brand-400 mb-6 tracking-tight">
                                    {__('Instructor')}
                                </h2>
                                <div className="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-md p-6 md:p-8 flex flex-col md:flex-row gap-8 shadow-sm">
                                    <AppLink to={`/instructors/${course.instructor.slug}`} className="shrink-0 w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-gray-50 dark:border-gray-800 overflow-hidden mx-auto md:mx-0 shadow-lg block hover:opacity-90 transition-opacity">
                                        <img src={course.instructor.image} className="w-full h-full object-cover" alt={t(course.instructor.name)} />
                                    </AppLink>
                                    <div className="flex flex-col w-full text-center md:text-start">
                                        <AppLink to={`/instructors/${course.instructor.slug}`} className="inline-block hover:text-brand-700 transition-colors w-max mx-auto md:mx-0">
                                            <h3 className="text-xl font-bold text-brand-600 dark:text-brand-400">{t(course.instructor.name)}</h3>
                                        </AppLink>
                                        <p className="text-sm font-bold text-gray-700 dark:text-gray-300 mt-2 leading-relaxed">
                                            {t(course.instructor.title)}
                                        </p>
                                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed whitespace-pre-line">
                                            {t(course.instructor.bio)}
                                        </p>
                                        
                                        <div className="flex flex-wrap items-center justify-center md:justify-start gap-6 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                                            <div className="flex items-center gap-2">
                                                <PlayCircle className="w-5 h-5 text-brand-500" />
                                                <span className="text-sm font-bold text-gray-700 dark:text-gray-200">{course.instructor.courses_count} {__('Courses')}</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Users className="w-5 h-5 text-brand-500" />
                                                <span className="text-sm font-bold text-gray-700 dark:text-gray-200">{course.instructor.total_students?.toLocaleString()} {__('Learners')}</span>
                                            </div>
                                        </div>
                                        
                                        {instructorSlug && (
                                            <div className="mt-6 flex justify-center md:justify-start">
                                                <AppLink 
                                                    to={`/instructors/${instructorSlug}`}
                                                    className="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-8 rounded-md text-sm transition-colors shadow-md active:scale-95 w-full md:w-auto inline-block text-center"
                                                >
                                                    {__('Profile Page')}
                                                </AppLink>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Reviews */}
                        {activeTab === 3 && (
                            <div id="reviews" className="scroll-mt-24">
                                <h2 className="text-xl font-bold text-brand-600 dark:text-brand-400 mb-6 tracking-tight">
                                    {__('Student Reviews')} ({reviewsCount} • {averageRating.toFixed(1)}★)
                                </h2>

                                <div className="mb-6 rounded-md border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                    {canSubmitReview ? (
                                        <div className="space-y-4">
                                            <div>
                                                <p className="text-sm font-bold text-gray-900 dark:text-white mb-3">{__('Share your feedback')}</p>
                                                <div className="flex items-center gap-2">
                                                    {[1, 2, 3, 4, 5].map((value) => (
                                                        <button
                                                            key={value}
                                                            type="button"
                                                            onClick={() => setReviewRating(value)}
                                                            className="text-amber-400 hover:scale-105 transition-transform"
                                                            aria-label={`${value} ${__('stars')}`}
                                                        >
                                                            <Star className={`w-6 h-6 ${value <= reviewRating ? 'fill-current' : ''}`} />
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                            <textarea
                                                value={reviewComment}
                                                onChange={(e) => setReviewComment(e.target.value)}
                                                rows={4}
                                                placeholder={__('Tell other students what stood out in this course')}
                                                className="w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition-all focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                            <button
                                                type="button"
                                                onClick={handleReviewSubmit}
                                                disabled={reviewSubmitting}
                                                className="inline-flex items-center gap-2 rounded-md bg-brand-600 px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70"
                                            >
                                                {reviewSubmitting ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
                                                <span>{reviewSubmitting ? __('Submitting...') : __('Submit review')}</span>
                                            </button>
                                        </div>
                                    ) : user ? (
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {__('Enroll in this course to leave a review.')}
                                        </p>
                                    ) : (
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {__('Log in and enroll to leave a review.')}
                                        </p>
                                    )}
                                </div>

                                {reviewsLoading && reviews.length === 0 ? (
                                    <div className="flex items-center justify-center py-10">
                                        <Loader2 className="w-6 h-6 animate-spin text-brand-600" />
                                    </div>
                                ) : reviews.length > 0 ? (
                                    <div className="flex flex-col gap-0 border-t border-gray-100 dark:border-gray-800">
                                    {reviews.map((review: any, idx: number) => (
                                        <div key={review.id || idx} className="py-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-start">
                                            <div className="flex items-start gap-4">
                                                <div className="w-12 h-12 rounded-full overflow-hidden bg-blue-50 dark:bg-blue-900/20 text-brand-600 flex items-center justify-center font-bold text-lg shrink-0">
                                                    {review.user_image ? (
                                                        <img src={review.user_image} className="w-full h-full object-cover" alt={review.user_name} />
                                                    ) : (
                                                        <User className="w-6 h-6" />
                                                    )}
                                                </div>
                                                <div className="flex flex-col gap-1.5 mt-1">
                                                    <span className="font-bold text-sm text-gray-800 dark:text-gray-200">{review.user_name}</span>
                                                    <div className="flex text-[#fcd34d]">
                                                        {[...Array(5)].map((_, i) => (
                                                            <Star key={i} className={`w-4 h-4 ${i < Math.round(review.rating) ? 'fill-current' : 'text-gray-200 dark:text-gray-700'}`} />
                                                        ))}
                                                    </div>
                                                    {review.comment && (
                                                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">{review.comment}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <span className="text-xs font-bold text-gray-400 mt-2 shrink-0">{review.created_at}</span>
                                        </div>
                                    ))}
                                    </div>
                                ) : (
                                    <div className="rounded-md border border-dashed border-gray-200 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        {__('No reviews yet.')}
                                    </div>
                                )}

                                {reviewsMeta.current_page < reviewsMeta.last_page ? (
                                    <div className="mt-6 flex justify-center">
                                        <button
                                            type="button"
                                            onClick={() => void loadReviewPage(reviewsMeta.current_page + 1)}
                                            disabled={reviewsLoading}
                                            className="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-70 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                                        >
                                            {reviewsLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
                                            <span>{__('Load more reviews')}</span>
                                        </button>
                                    </div>
                                ) : null}
                            </div>
                        )}

                    </div>

                    {/* Right Column - Sticky Sidebar */}
                    <div className="w-full lg:w-[35%] order-1 lg:order-2 flex flex-col pt-0 lg:pt-0 relative lg:sticky lg:top-24 mt-0 lg:-mt-48 z-20">
                        <div className="bg-white dark:bg-gray-900 rounded-md overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-800 p-1.5 flex flex-col">
                            
                            {/* Top Badge Overlay */}
                            {course.is_live && (
                                <div className="absolute top-6 left-6 z-10">
                                    <span className="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-md shadow-sm flex items-center gap-1.5">
                                        <span className="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                        {__('Interactive Live')}
                                    </span>
                                </div>
                            )}

                            {/* Video / Image container */}
                            <div className="relative aspect-video w-full rounded-[1.75rem] overflow-hidden bg-gray-100 dark:bg-gray-800 group">
                                <img src={course.image} alt={t(course.title)} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                {hasPromoVideo ? (
                                    <button
                                        type="button"
                                        onClick={openTrailerModal}
                                        className="absolute inset-0 bg-black/20 transition-colors group-hover:bg-black/40 flex items-center justify-center cursor-pointer"
                                        aria-label={__('Watch trailer')}
                                    >
                                        <div className="w-16 h-16 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border border-white/40 shadow-xl group-hover:scale-110 transition-transform">
                                            <PlayCircle className="w-8 h-8 text-white fill-white" />
                                        </div>
                                    </button>
                                ) : null}
                            </div>

                            {/* Purchase Info */}
                            <div className="p-6 md:p-8 flex flex-col gap-6">
                                <div className="flex items-baseline gap-2">
                                    <span className="text-3xl font-black text-brand-600">{formatAmount(course.price)}</span>
                                    {course.old_price && (
                                        <span className="text-lg font-bold text-gray-400 line-through">{formatAmount(course.old_price)}</span>
                                    )}
                                </div>
                                
                                <div className="flex gap-3">
                                    <button
                                        onClick={handleAddToCart}
                                        className={`flex-1 font-bold py-3.5 px-4 rounded-md text-base transition-colors shadow-lg active:scale-95 ${
                                            isEnrolled
                                                ? 'bg-emerald-600/90 hover:bg-emerald-700 text-white shadow-emerald-600/20'
                                                : 'bg-brand-600 hover:bg-brand-700 text-white shadow-brand-600/20'
                                        }`}
                                    >
                                        {isEnrolled
                                            ? __('Continue Learning')
                                            : isInCart(course.id)
                                                ? __('Go to Cart')
                                                : __('Enroll Now')}
                                    </button>
                                    <button
                                        onClick={handleAddToCart}
                                        disabled={isAdding || isEnrolled}
                                        className={`w-14 h-[52px] shrink-0 border-2 flex items-center justify-center rounded-md transition-colors active:scale-95 ${
                                            isEnrolled
                                                ? 'border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                                                : isInCart(course.id)
                                                ? 'border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                                                : 'border-brand-100 dark:border-gray-700 text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-gray-800'
                                        } ${isAdding ? 'opacity-75 cursor-not-allowed' : ''}`}
                                        aria-label={isEnrolled ? __('Enrolled') : isInCart(course.id) ? __('In Cart') : __('Add to Cart')}
                                    >
                                        {isAdding ? <Loader2 className="w-5 h-5 animate-spin" /> : 
                                         (isEnrolled || isInCart(course.id)) ? <CheckCircle2 className="w-5 h-5" /> : <ShoppingCart className="w-5 h-5" />}
                                    </button>
                                </div>

                                <div className="pt-6 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-4">
                                    <p className="font-bold text-gray-900 dark:text-white mb-1">{__('This course contains:')}</p>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><User className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{__('By:')}</span>
                                        <AppLink 
                                            to={instructorSlug ? `/instructors/${instructorSlug}` : '#'}
                                            className="font-bold text-brand-600 underline decoration-brand-200 underline-offset-4 cursor-pointer hover:text-brand-700 transition-colors"
                                        >
                                            {t(course.instructor?.name)}
                                        </AppLink>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><Clock className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{__('Duration:')}</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">{course.duration_hours} {__('hours')}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><PlayCircle className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{__('Lessons:')}</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">{totalLessons} {__('lessons')}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><Award className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{__('Accredited Certificate of Completion')}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><InfinityIcon className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{__('Lifetime Access')}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><Globe className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{__('Delivery:')}</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">{deliveryTypeLabel}</span>
                                    </div>
                                </div>

                                <button className="mt-4 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-center gap-2 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors w-full group">
                                    <Share2 className="w-4 h-4 transition-transform group-hover:scale-110" />
                                    <span>{__('Share Course')}</span>
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            {hasFreePreviewLessons && isPreviewModalOpen && (
                <div className="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="lesson-preview-title">
                    <div className="flex min-h-screen items-end justify-center p-4 text-center sm:items-center sm:p-6">
                        <div
                            className="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity"
                            aria-hidden="true"
                            onClick={closePreviewModal}
                        />

                        <div className="relative z-10 w-full max-w-5xl overflow-hidden rounded-[1.75rem] border border-gray-200 bg-white text-start shadow-2xl dark:border-gray-800 dark:bg-gray-900 rtl:text-right ltr:text-left">
                            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                                <div className="flex min-w-0 flex-wrap items-center gap-3">
                                    <span className="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                        {__('Free Preview')}
                                    </span>
                                    <span className="truncate text-sm font-bold text-gray-500 dark:text-gray-400">
                                        {lessonPreview?.module?.title ? t(lessonPreview.module.title) : __('Loading preview...')}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    onClick={closePreviewModal}
                                    className="rounded-full bg-gray-50 p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                                    aria-label={__('Close preview')}
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <div className="max-h-[85vh] overflow-y-auto">
                                <div className="relative aspect-video overflow-hidden bg-gray-950">
                                    {lessonPreviewLoading ? (
                                        <div className="h-full w-full animate-pulse">
                                            <div className="h-full w-full bg-gradient-to-br from-gray-800 via-gray-900 to-black" />
                                        </div>
                                    ) : lessonPreview?.lesson?.video?.source_type === 'upload' ? (
                                        <video
                                            key={`preview-upload-${lessonPreview.lesson.id}`}
                                            controls
                                            className="h-full w-full"
                                            src={lessonPreview.lesson.video.url}
                                        />
                                    ) : lessonPreview?.lesson?.video?.source_type === 'embed' ? (
                                        <iframe
                                            key={`preview-embed-${lessonPreview.lesson.id}`}
                                            src={lessonPreview.lesson.video.embed_url}
                                            className="h-full w-full"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowFullScreen
                                            title={t(lessonPreview.lesson.title)}
                                        />
                                    ) : lessonPreviewError ? (
                                        <div className="flex h-full flex-col items-center justify-center gap-3 px-6 text-center text-white">
                                            <BookOpen className="h-10 w-10 text-brand-300" />
                                            <p className="text-base font-bold">{lessonPreviewError}</p>
                                        </div>
                                    ) : (
                                        <div className="flex h-full flex-col items-center justify-center gap-3 px-6 text-center text-white">
                                            <PlayCircle className="h-12 w-12 text-brand-300" />
                                            <p className="text-lg font-bold">{__('Loading preview...')}</p>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-4 p-5 sm:p-6">
                                    {lessonPreviewLoading ? (
                                        <div className="animate-pulse space-y-4">
                                            <div className="flex flex-wrap items-center gap-3">
                                                <div className="h-7 w-24 rounded-full bg-emerald-100/70 dark:bg-emerald-500/10" />
                                                <div className="h-4 w-40 rounded bg-gray-200 dark:bg-gray-800" />
                                                <div className="h-7 w-24 rounded-full bg-gray-200 dark:bg-gray-800" />
                                            </div>
                                            <div className="h-8 w-2/3 rounded bg-gray-200 dark:bg-gray-800" />
                                            <div className="space-y-2">
                                                <div className="h-4 w-full rounded bg-gray-200 dark:bg-gray-800" />
                                                <div className="h-4 w-5/6 rounded bg-gray-200 dark:bg-gray-800" />
                                                <div className="h-4 w-2/3 rounded bg-gray-200 dark:bg-gray-800" />
                                            </div>
                                        </div>
                                    ) : (
                                        <>
                                            <div className="flex flex-wrap items-center gap-3">
                                                {lessonPreview?.lesson ? (
                                                    <span className="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs font-bold text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                        <Clock className="h-3.5 w-3.5 text-brand-500" />
                                                        {formatDuration(lessonPreview.lesson.duration_minutes)}
                                                    </span>
                                                ) : null}
                                            </div>

                                            <h3 id="lesson-preview-title" className="text-xl font-black text-gray-900 dark:text-white sm:text-3xl">
                                                {lessonPreview?.lesson ? t(lessonPreview.lesson.title) : __('Preview this lesson')}
                                            </h3>

                                            <p className="text-sm leading-relaxed text-gray-500 dark:text-gray-400 sm:text-base">
                                                {lessonPreview?.lesson?.description
                                                    ? t(lessonPreview.lesson.description)
                                                    : lessonPreviewError || __('This preview is available before enrollment.')}
                                            </p>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {isTrailerModalOpen && (
                <div className="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="course-trailer-title">
                    <div className="flex min-h-screen items-end justify-center p-4 text-center sm:items-center sm:p-6">
                        <div
                            className="fixed inset-0 bg-gray-950/70 backdrop-blur-sm transition-opacity"
                            aria-hidden="true"
                            onClick={closeTrailerModal}
                        />

                        <div className="relative z-10 w-full max-w-5xl overflow-hidden rounded-[1.75rem] border border-gray-200 bg-white text-start shadow-2xl dark:border-gray-800 dark:bg-gray-900 rtl:text-right ltr:text-left">
                            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
                                <div className="flex min-w-0 flex-wrap items-center gap-3">
                                    <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-[11px] font-black text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                        {__('Watch trailer')}
                                    </span>
                                    <span className="truncate text-sm font-bold text-gray-500 dark:text-gray-400">
                                        {t(course.title)}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    onClick={closeTrailerModal}
                                    className="rounded-full bg-gray-50 p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                                    aria-label={__('Close preview')}
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <div className="max-h-[85vh] overflow-y-auto">
                                <div className="relative aspect-video overflow-hidden bg-gray-950">
                                    {isTrailerModalPriming ? (
                                        <div className="h-full w-full animate-pulse">
                                            <div className="h-full w-full bg-gradient-to-br from-gray-800 via-gray-900 to-black" />
                                        </div>
                                    ) : course?.promo_video?.source_type === 'upload' ? (
                                        <video
                                            key={`course-trailer-upload-${course.id}`}
                                            controls
                                            autoPlay
                                            className="h-full w-full"
                                            src={course.promo_video.url}
                                        />
                                    ) : course?.promo_video?.source_type === 'embed' ? (
                                        <iframe
                                            key={`course-trailer-embed-${course.id}`}
                                            src={buildAutoplayEmbedUrl(course.promo_video.embed_url)}
                                            className="h-full w-full"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowFullScreen
                                            title={__('Course trailer')}
                                        />
                                    ) : (
                                        <div className="flex h-full flex-col items-center justify-center gap-3 px-6 text-center text-white">
                                            <BookOpen className="h-10 w-10 text-brand-300" />
                                            <p className="text-base font-bold">{__('Trailer unavailable')}</p>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-4 p-5 sm:p-6">
                                    {isTrailerModalPriming ? (
                                        <div className="animate-pulse space-y-4">
                                            <div className="flex flex-wrap items-center gap-3">
                                                <div className="h-7 w-28 rounded-full bg-brand-100/70 dark:bg-brand-500/10" />
                                                <div className="h-4 w-40 rounded bg-gray-200 dark:bg-gray-800" />
                                            </div>
                                            <div className="h-8 w-2/3 rounded bg-gray-200 dark:bg-gray-800" />
                                            <div className="space-y-2">
                                                <div className="h-4 w-full rounded bg-gray-200 dark:bg-gray-800" />
                                                <div className="h-4 w-5/6 rounded bg-gray-200 dark:bg-gray-800" />
                                            </div>
                                        </div>
                                    ) : (
                                        <>
                                            <h3 id="course-trailer-title" className="text-xl font-black text-gray-900 dark:text-white sm:text-3xl">
                                                {__('Course trailer')}
                                            </h3>

                                            <p className="text-sm leading-relaxed text-gray-500 dark:text-gray-400 sm:text-base">
                                                {course?.short_description ? t(course.short_description) : __('Trailer unavailable')}
                                            </p>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default CourseDetailsPage;
