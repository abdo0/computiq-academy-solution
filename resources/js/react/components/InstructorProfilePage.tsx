import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { Share2, Users, PlayCircle, Star, Award, Twitter, Linkedin, Globe, CheckCircle2, Loader2 } from 'lucide-react';
import { useLanguage } from '../contexts/LanguageContext';
import { dataService } from '../services/dataService';
import CourseCard from './home/CourseCard';
import AppLink from './common/AppLink';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const InstructorProfilePage: React.FC = () => {
    const { slug } = useParams();
    const { language } = useLanguage();
    const isRTL = language === 'ar' || language === 'ku';
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    
    const [instructor, setInstructor] = useState<any>(() => initialBootstrap?.instructor ?? null);
    const [loading, setLoading] = useState(() => !initialBootstrap?.instructor);
    const [activeTab, setActiveTab] = useState<'about' | 'courses'>('courses');

    const t = (obj: any) => {
        if (!obj) return '';
        if (typeof obj === 'string') return obj;
        return obj[language] || obj['ar'] || obj['en'] || '';
    };

    useEffect(() => {
        if (!slug) return;

        if (initialBootstrap?.instructor) {
            setInstructor(initialBootstrap.instructor);
            setLoading(false);
            return;
        }

        setLoading(true);
        dataService.getInstructorBySlug(slug).then((data) => {
            setInstructor(data);
            setLoading(false);
        });
    }, [initialBootstrap, slug]);

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-[#f8fafc] dark:bg-gray-950">
                <Loader2 className="w-10 h-10 text-brand-600 animate-spin" />
            </div>
        );
    }

    if (!instructor) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-[#f8fafc] dark:bg-gray-950">
                <div className="text-center">
                    <h2 className="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                        {isRTL ? 'المدرب غير موجود' : 'Instructor Not Found'}
                    </h2>
                    <AppLink to="/" className="text-brand-600 font-bold mt-4 inline-block">
                        {isRTL ? 'العودة للرئيسية' : 'Go Home'}
                    </AppLink>
                </div>
            </div>
        );
    }

    const stats = instructor.stats || {};
    const social = instructor.social_links || {};
    const courses = instructor.courses || [];

    return (
        <div className="bg-[#f8fafc] dark:bg-gray-950 min-h-screen pb-20">
            {/* Premium Header Profile Section */}
            <div className="relative max-w-screen-2xl mx-auto pt-6 px-4 md:px-8">
                {/* Cover Photo / Graphic Banner */}
                <div className="w-full h-48 md:h-64 rounded-md bg-gradient-to-r from-brand-900 via-brand-800 to-blue-900 dark:from-slate-900 dark:to-slate-800 overflow-hidden relative shadow-lg">
                    {/* Decorative Background Elements */}
                    <div className="absolute inset-0 opacity-20">
                        <svg className="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern id="hero-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                                    <path d="M0 40L40 0H20L0 20M40 40V20L20 40" stroke="currentColor" strokeWidth="1" fill="none" opacity="0.3"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#hero-pattern)" />
                        </svg>
                    </div>
                </div>

                {/* Profile Card Overlap */}
                <div className="bg-white dark:bg-gray-900 rounded-md p-6 pb-2 sm:p-8 sm:pb-2 mx-4 md:mx-12 -mt-24 md:-mt-32 relative z-10 shadow-xl border border-gray-100 dark:border-gray-800 flex flex-col md:flex-row gap-6 md:gap-10 items-center md:items-end">
                    
                    {/* Large Avatar */}
                    <div className="w-40 h-40 md:w-48 md:h-48 rounded-full border-[6px] border-white dark:border-gray-900 bg-gray-100 dark:bg-gray-800 overflow-hidden shrink-0 shadow-xl relative -mt-20 md:mt-0 md:-mt-16 bg-brand-50">
                        <img src={instructor.image} className="w-full h-full object-cover" alt={t(instructor.name)} />
                    </div>

                    {/* Instructor Info */}
                    <div className="flex-1 text-center md:text-start pb-4">
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 className="text-3xl font-black text-gray-900 dark:text-white flex items-center justify-center md:justify-start gap-2">
                                    {t(instructor.name)}
                                    <CheckCircle2 className="w-6 h-6 text-blue-500" />
                                </h1>
                                <p className="text-gray-600 dark:text-gray-400 font-medium mt-1.5 md:max-w-xl text-sm leading-relaxed">
                                    {t(instructor.title)}
                                </p>
                            </div>

                            {/* Actions & Socials */}
                            <div className="flex flex-col sm:flex-row items-center gap-3">
                                <div className="flex items-center gap-2">
                                    {social.twitter && <a href={social.twitter} className="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-[#1DA1F2] transition-colors"><Twitter className="w-5 h-5" /></a>}
                                    {social.linkedin && <a href={social.linkedin} className="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-[#0A66C2] transition-colors"><Linkedin className="w-5 h-5" /></a>}
                                    {social.website && <a href={social.website} className="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-brand-600 transition-colors"><Globe className="w-5 h-5" /></a>}
                                </div>
                                <button className="flex items-center gap-2 bg-brand-50 hover:bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400 px-5 py-2.5 rounded-md font-bold transition-colors w-full sm:w-auto justify-center">
                                    <Share2 className="w-4 h-4" /> {isRTL ? 'مشاركة' : 'Share'}
                                </button>
                            </div>
                        </div>

                        {/* Stats Bar */}
                        <div className="flex flex-wrap items-center justify-center md:justify-start gap-x-8 gap-y-4 mt-6">
                            <div className="flex items-center gap-2.5">
                                <div className="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600"><Users className="w-5 h-5"/></div>
                                <div className="flex flex-col text-start">
                                    <span className="text-lg font-black text-gray-900 dark:text-white leading-none">{stats.total_students?.toLocaleString() || 0}</span>
                                    <span className="text-xs text-gray-500 font-medium">{isRTL ? 'إجمالي الطلاب' : 'Total Students'}</span>
                                </div>
                            </div>
                            <div className="flex items-center gap-2.5">
                                <div className="w-10 h-10 rounded-full bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center text-brand-600"><PlayCircle className="w-5 h-5"/></div>
                                <div className="flex flex-col text-start">
                                    <span className="text-lg font-black text-gray-900 dark:text-white leading-none">{stats.courses_count || 0}</span>
                                    <span className="text-xs text-gray-500 font-medium">{isRTL ? 'الدورات' : 'Courses'}</span>
                                </div>
                            </div>
                            <div className="flex items-center gap-2.5">
                                <div className="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500"><Star className="w-5 h-5"/></div>
                                <div className="flex flex-col text-start">
                                    <div className="flex items-center gap-1">
                                        <span className="text-lg font-black text-gray-900 dark:text-white leading-none">{stats.average_rating || 0}</span>
                                        <Star className="w-3.5 h-3.5 fill-[#fcd34d] text-[#fcd34d] -mt-0.5" />
                                    </div>
                                    <span className="text-xs text-gray-500 font-medium">{stats.total_reviews?.toLocaleString() || 0} {isRTL ? 'تقييم' : 'Reviews'}</span>
                                </div>
                            </div>
                        </div>
                        
                        {/* Tabs */}
                        <div className="flex gap-6 mt-8 border-t border-gray-100 dark:border-gray-800 pt-4 px-2 overflow-x-auto scrollbar-hide">
                            <button 
                                onClick={() => setActiveTab('courses')}
                                className={`pb-4 text-[15px] font-bold transition-all relative whitespace-nowrap ${activeTab === 'courses' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'}`}
                            >
                                {isRTL ? 'دورات المدرب' : 'Instructor Courses'}
                                {activeTab === 'courses' && <div className="absolute bottom-0 left-0 right-0 h-1 bg-brand-600 dark:bg-brand-400 rounded-t-full"></div>}
                            </button>
                            <button 
                                onClick={() => setActiveTab('about')}
                                className={`pb-4 text-[15px] font-bold transition-all relative whitespace-nowrap ${activeTab === 'about' ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'}`}
                            >
                                {isRTL ? 'عن المدرب' : 'About Instructor'}
                                {activeTab === 'about' && <div className="absolute bottom-0 left-0 right-0 h-1 bg-brand-600 dark:bg-brand-400 rounded-t-full"></div>}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Tab Content */}
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
                
                {activeTab === 'courses' && (
                    <div className="animate-fade-in-up">
                        <div className="flex items-center justify-between mb-8">
                            <h2 className="text-2xl font-black text-gray-900 dark:text-white">
                                {isRTL ? `جميع دورات ${t(instructor.name).split(' ')[0]}` : `All Courses by ${t(instructor.name).split(' ')[0]}`}
                            </h2>
                            <span className="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-4 py-1.5 rounded-xl text-sm font-bold">
                                {courses.length} {isRTL ? 'دورات' : 'Courses'}
                            </span>
                        </div>
                        
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            {courses.map((course: any) => (
                                <CourseCard 
                                    key={course.id} 
                                    image={course.image}
                                    title={t(course.title)}
                                    instructor={t(instructor.name)}
                                    instructorImage={instructor.image}
                                    price={`${course.price}`}
                                    oldPrice={course.old_price ? `${course.old_price}` : undefined}
                                    rating={course.rating}
                                    reviewCount={course.review_count}
                                    hours={course.duration_hours}
                                    students={course.students_count}
                                    badge={course.is_best_seller ? (isRTL ? 'الأكثر مبيعاً' : 'Best Seller') : course.is_live ? (isRTL ? 'مباشر' : 'LIVE') : undefined}
                                    badgeColor={course.is_live ? 'bg-red-500' : undefined}
                                    link={`/courses/${course.slug}`}
                                    courseId={course.id}
                                />
                            ))}
                        </div>
                    </div>
                )}

                {activeTab === 'about' && (
                    <div className="animate-fade-in-up bg-white dark:bg-gray-900 rounded-md p-8 md:p-12 shadow-sm border border-gray-100 dark:border-gray-800 max-w-4xl mx-auto">
                        <h2 className="text-2xl font-black text-brand-600 dark:text-brand-400 mb-6">{isRTL ? 'نبذة عني' : 'About Me'}</h2>
                        <div className="prose prose-lg dark:prose-invert prose-brand max-w-none text-gray-600 dark:text-gray-300">
                            <p className="whitespace-pre-line leading-relaxed">{t(instructor.bio)}</p>
                        </div>
                    </div>
                )}

            </div>
        </div>
    );
};

export default InstructorProfilePage;
