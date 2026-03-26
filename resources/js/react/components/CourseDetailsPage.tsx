import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { dataService } from '../services/dataService';
import { 
    Star, PlayCircle, Users, Clock, Award, Infinity as InfinityIcon, 
    Globe, Share2, ShoppingCart, CheckCircle2, ChevronDown, ChevronUp, User, ChevronLeft, ChevronRight, Loader2
} from 'lucide-react';
import AppLink from './common/AppLink';
import { useCart } from '../contexts/CartContext';

const CourseDetailsPage: React.FC = () => {
    const { slug } = useParams<{ slug: string }>();
    const navigate = useNavigate();
    const { language } = useLanguage();
    const { __, t } = useTranslation();
    const isRTL = language === 'ar' || language === 'ku';
    const { addToCart, isInCart } = useCart();

    const [course, setCourse] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState(1);
    const [openModules, setOpenModules] = useState<number[]>([0, 1]);



    useEffect(() => {
        if (!slug) return;
        setLoading(true);
        dataService.getCourseBySlug(slug).then((data) => {
            setCourse(data);
            setLoading(false);
        });
    }, [slug]);

    const toggleModule = (index: number) => {
        setOpenModules(prev => 
            prev.includes(index) ? prev.filter(i => i !== index) : [...prev, index]
        );
    };

    const formatDuration = (minutes: number) => {
        if (minutes >= 60) {
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            return m > 0 ? `${h}h ${m}min` : `${h}h`;
        }
        return `${minutes} min`;
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
                        {isRTL ? 'الدورة غير موجودة' : 'Course Not Found'}
                    </h2>
                    <AppLink to="/" className="text-brand-600 font-bold mt-4 inline-block">
                        {isRTL ? 'العودة للرئيسية' : 'Go Home'}
                    </AppLink>
                </div>
            </div>
        );
    }

    const tabs = [
        isRTL ? 'نظرة عامة' : 'Overview',
        isRTL ? 'محتوى الدورة' : 'Course Content',
        isRTL ? 'المدرب' : 'Instructor',
        isRTL ? 'التقييمات' : 'Reviews',
    ];

    const totalLessons = course.modules?.reduce((sum: number, m: any) => sum + (m.lessons_count || m.lessons?.length || 0), 0) || 0;
    const totalDurationMin = course.modules?.reduce((sum: number, m: any) => sum + (m.duration_minutes || 0), 0) || 0;
    const freeLessons = course.modules?.reduce((sum: number, m: any) => 
        sum + (m.lessons?.filter((l: any) => l.is_free)?.length || 0), 0) || 0;

    const instructorSlug = course.instructor?.slug;

    return (
        <div className="bg-[#f8fafc] dark:bg-gray-950 min-h-screen pb-20">
            {/* Header / Hero Section */}
            <div className="bg-[#0b1021] dark:bg-slate-900 text-white min-h-[300px] md:min-h-[360px] relative mt-1 lg:mt-6 max-w-screen-2xl mx-auto rounded-none lg:rounded-md overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-r from-brand-900/40 to-transparent pointer-events-none"></div>
                <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-12 py-12 md:py-16 h-full flex items-center relative z-10">
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
                                    {isRTL ? 'الأكثر مبيعاً' : 'Best Seller'}
                                </span>
                            )}
                            {course.is_live && (
                                <span className="bg-red-500 text-white px-3 py-1 rounded-sm font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <span className="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                    {isRTL ? 'مباشر' : 'LIVE'}
                                </span>
                            )}
                            <div className="flex items-center gap-1.5 border border-white/20 px-3 py-1 rounded-sm bg-white/5">
                                <Star className="w-4 h-4 text-[#fcd34d] fill-[#fcd34d]" />
                                <span>{course.rating?.toFixed(1)}</span>
                            </div>
                            <div className="flex items-center gap-1.5 border border-white/20 px-3 py-1 rounded-sm bg-white/5">
                                <Users className="w-4 h-4" />
                                <span>{course.students_count?.toLocaleString()} {isRTL ? 'طالب' : 'Learners'}</span>
                            </div>
                        </div>

                        <div className="mt-8 pt-6 border-t border-white/10 flex items-center gap-2 text-sm text-gray-300">
                            {isRTL ? 'بواسطة' : 'Made by'}{' '}
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
                        <div className="flex items-center gap-6 border-b border-gray-200 dark:border-gray-800 overflow-x-auto scrollbar-hide">
                            {tabs.map((tab, index) => (
                                <button
                                    key={tab}
                                    onClick={() => setActiveTab(index)}
                                    className={`whitespace-nowrap pb-4 text-sm font-bold transition-colors relative ${activeTab === index ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'}`}
                                >
                                    {tab}
                                    {activeTab === index && (
                                        <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-600 dark:bg-brand-400 rounded-t-full"></div>
                                    )}
                                </button>
                            ))}
                        </div>

                        {/* Overview Section */}
                        {activeTab === 0 && (
                            <div id="overview" className="scroll-mt-24">
                                <h2 className="text-xl font-bold text-brand-600 dark:text-brand-400 mb-4 tracking-tight">
                                    {isRTL ? 'نظرة عامة' : 'Overview'}
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
                                    {isRTL ? 'محتوى الدورة' : 'Course Content'}
                                </h2>
                                
                                <div className="flex items-center gap-6 mb-6 text-sm font-bold text-gray-600 dark:text-gray-400">
                                    <div className="flex items-center gap-2"><PlayCircle className="w-4 h-4 text-brand-500"/> {totalLessons} {isRTL ? 'درس' : 'Lessons'}</div>
                                    {freeLessons > 0 && (
                                        <div className="flex items-center gap-2"><Award className="w-4 h-4 text-brand-500"/> {freeLessons} {isRTL ? 'دروس مجانية' : 'Free Lessons'}</div>
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
                                                            <span>{module.lessons_count || module.lessons?.length || 0} {isRTL ? 'درس' : 'Lessons'}</span>
                                                        </div>
                                                    </div>
                                                    {isOpen ? <ChevronUp className="w-5 h-5 text-gray-400 shrink-0"/> : <ChevronDown className="w-5 h-5 text-gray-400 shrink-0"/>}
                                                </button>
                                                
                                                {isOpen && module.lessons?.length > 0 && (
                                                    <div className="p-4 md:p-5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                                                        <div className="flex flex-col gap-3">
                                                            {module.lessons.map((lesson: any, lIdx: number) => (
                                                                <div key={lesson.id || lIdx} className="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-md gap-2">
                                                                    <div className="flex items-center gap-3">
                                                                        <PlayCircle className={`w-4 h-4 ${lesson.is_free ? 'text-brand-600' : 'text-gray-400'}`}/>
                                                                        <span className={`text-sm font-medium ${lesson.is_free ? 'text-brand-700 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300'}`}>
                                                                            {t(lesson.title)}
                                                                        </span>
                                                                        {lesson.is_free && (
                                                                            <span className="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-xl">
                                                                                {isRTL ? 'مجاني' : 'FREE'}
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                    <span className="text-xs font-bold text-gray-400">{formatDuration(lesson.duration_minutes)}</span>
                                                                </div>
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
                                    {isRTL ? 'المدرب' : 'Instructor'}
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
                                                <span className="text-sm font-bold text-gray-700 dark:text-gray-200">{course.instructor.courses_count} {isRTL ? 'دورة تدريبية' : 'Courses'}</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Users className="w-5 h-5 text-brand-500" />
                                                <span className="text-sm font-bold text-gray-700 dark:text-gray-200">{course.instructor.total_students?.toLocaleString()} {isRTL ? 'طالب' : 'Learners'}</span>
                                            </div>
                                        </div>
                                        
                                        {instructorSlug && (
                                            <div className="mt-6 flex justify-center md:justify-start">
                                                <AppLink 
                                                    to={`/instructors/${instructorSlug}`}
                                                    className="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-8 rounded-md text-sm transition-colors shadow-md active:scale-95 w-full md:w-auto inline-block text-center"
                                                >
                                                    {isRTL ? 'صفحة المدرب' : 'Profile Page'}
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
                                    {isRTL ? 'تقييمات الطلاب' : "Student Reviews"} ({course.reviews?.length || 0})
                                </h2>
                                <div className="flex flex-col gap-0 border-t border-gray-100 dark:border-gray-800">
                                    {course.reviews?.map((review: any, idx: number) => (
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
                                        {isRTL ? 'بث مباشر' : 'Interactive Live'}
                                    </span>
                                </div>
                            )}

                            {/* Video / Image container */}
                            <div className="relative aspect-video w-full rounded-[1.75rem] overflow-hidden bg-gray-100 dark:bg-gray-800 group">
                                <img src={course.image} alt={t(course.title)} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center cursor-pointer">
                                    <div className="w-16 h-16 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border border-white/40 shadow-xl group-hover:scale-110 transition-transform">
                                        <PlayCircle className="w-8 h-8 text-white fill-white" />
                                    </div>
                                </div>
                            </div>

                            {/* Purchase Info */}
                            <div className="p-6 md:p-8 flex flex-col gap-6">
                                <div className="flex items-baseline gap-2">
                                    <span className="text-3xl font-black text-brand-600">{course.price}</span>
                                    {course.old_price && (
                                        <span className="text-lg font-bold text-gray-400 line-through">{course.old_price}</span>
                                    )}
                                    <span className="text-xl font-bold text-brand-600">{isRTL ? 'د.ع' : 'IQD'}</span>
                                </div>
                                
                                <div className="flex gap-3">
                                    <button className="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-4 rounded-md text-base transition-colors shadow-lg shadow-brand-600/20 active:scale-95">
                                        {isRTL ? 'سجّل الآن' : 'Enroll Now'}
                                    </button>
                                    <button
                                        onClick={() => addToCart(course.id)}
                                        className={`w-14 h-[52px] shrink-0 border-2 flex items-center justify-center rounded-md transition-colors active:scale-95 ${
                                            isInCart(course.id)
                                                ? 'border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                                                : 'border-brand-100 dark:border-gray-700 text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-gray-800'
                                        }`}
                                    >
                                        <ShoppingCart className="w-5 h-5" />
                                    </button>
                                </div>

                                <div className="pt-6 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-4">
                                    <p className="font-bold text-gray-900 dark:text-white mb-1">{isRTL ? 'تحتوي هذه الدورة على:' : 'This course contains:'}</p>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><User className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{isRTL ? 'المدرب:' : 'By:'}</span>
                                        <AppLink 
                                            to={instructorSlug ? `/instructors/${instructorSlug}` : '#'}
                                            className="font-bold text-brand-600 underline decoration-brand-200 underline-offset-4 cursor-pointer hover:text-brand-700 transition-colors"
                                        >
                                            {t(course.instructor?.name)}
                                        </AppLink>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><Clock className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{isRTL ? 'المدة:' : 'Duration:'}</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">{course.duration_hours} {isRTL ? 'ساعة' : 'hours'}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><PlayCircle className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{isRTL ? 'الدروس:' : 'Lessons:'}</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">{totalLessons} {isRTL ? 'درس' : 'lessons'}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><Award className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{isRTL ? 'شهادة إتمام معتمدة' : 'Accredited Certificate of Completion'}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><InfinityIcon className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{isRTL ? 'وصول مدى الحياة' : 'Lifetime Access'}</span>
                                    </div>
                                    <div className="flex items-center gap-3 text-sm">
                                        <span className="w-5 flex justify-center"><Globe className="w-4 h-4 text-gray-500" strokeWidth={2.5} /></span>
                                        <span className="text-gray-500">{isRTL ? 'اللغة:' : 'Language:'}</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">{isRTL ? 'العربية' : 'Arabic'}</span>
                                    </div>
                                </div>

                                <button className="mt-4 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-center gap-2 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors w-full group">
                                    <Share2 className="w-4 h-4 transition-transform group-hover:scale-110" />
                                    <span>{isRTL ? 'مشاركة الدورة' : 'Share Course'}</span>
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    );
};

export default CourseDetailsPage;
