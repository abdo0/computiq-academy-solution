import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { useTranslation } from '../contexts/TranslationProvider';
import { useLanguage } from '../contexts/LanguageContext';
import { dataService } from '../services/dataService';
import Seo from './Seo';
import AppLink from './common/AppLink';
import { Compass, ArrowRight, ArrowLeft, BookOpen, Clock, Users, PlayCircle, CheckCircle2, Trophy, Briefcase, GraduationCap, Star, Zap, Target, Award, ShieldCheck } from 'lucide-react';
import * as LucideIcons from 'lucide-react';
import { motion } from 'framer-motion';

const PathDetailPage: React.FC = () => {
    const { slug } = useParams<{ slug: string }>();
    const { __ } = useTranslation();
    const { dir } = useLanguage();
    
    const [path, setPath] = useState<any>(null);
    const [suggestedPaths, setSuggestedPaths] = useState<any[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const fetchPath = async () => {
            if (!slug) return;
            try {
                setIsLoading(true);
                window.scrollTo(0, 0);
                const [pathData, allPathsData] = await Promise.all([
                    dataService.getPathBySlug(slug),
                    dataService.getPaths()
                ]);
                
                setPath(pathData);
                const pathsList = allPathsData?.data || [];
                const otherPaths = pathsList.filter((p: any) => p.slug !== slug);
                setSuggestedPaths(otherPaths.slice(0, 3));
            } catch (error) {
                console.error("Failed to fetch path details", error);
            } finally {
                setIsLoading(false);
            }
        };

        fetchPath();
    }, [slug]);

    // ─── Skeleton Loading State ────────────────────────────────────
    if (isLoading) {
        return (
            <div className="bg-[#fcfdfd] dark:bg-slate-950 min-h-screen pb-16">
                {/* Skeleton Cover */}
                <div className="w-full h-[260px] md:h-[340px] bg-gray-200 dark:bg-slate-800 animate-pulse relative">
                    <div className="absolute inset-0 bg-gradient-to-t from-[#fcfdfd] dark:from-slate-950 to-transparent" />
                    <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 h-full flex items-end pb-10 relative z-10">
                        <div className="w-full max-w-3xl">
                            <div className="w-14 h-14 rounded-lg bg-gray-300 dark:bg-slate-700 mb-4" />
                            <div className="h-8 bg-gray-300 dark:bg-slate-700 rounded w-2/3 mb-3" />
                            <div className="h-4 bg-gray-300 dark:bg-slate-700 rounded w-full mb-2" />
                            <div className="h-4 bg-gray-300 dark:bg-slate-700 rounded w-4/5 mb-6" />
                            <div className="flex gap-3">
                                <div className="h-9 bg-gray-300 dark:bg-slate-700 rounded-lg w-28" />
                                <div className="h-9 bg-gray-300 dark:bg-slate-700 rounded-lg w-28" />
                            </div>
                        </div>
                    </div>
                </div>
                {/* Skeleton Body */}
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                    <div className="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6">
                        <div className="space-y-4">
                            <div className="h-7 bg-gray-200 dark:bg-slate-800 rounded w-40 mb-4 animate-pulse" />
                            {[1, 2, 3, 4].map(i => (
                                <div key={i} className="flex items-center gap-4 p-4 bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800 animate-pulse">
                                    <div className="w-9 h-9 rounded-full bg-gray-200 dark:bg-slate-800 shrink-0" />
                                    <div className="w-16 h-10 rounded bg-gray-200 dark:bg-slate-800 shrink-0" />
                                    <div className="flex-1 space-y-2">
                                        <div className="h-4 bg-gray-200 dark:bg-slate-800 rounded w-3/4" />
                                        <div className="h-3 bg-gray-200 dark:bg-slate-800 rounded w-1/2" />
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="space-y-4">
                            <div className="h-48 bg-gray-100 dark:bg-slate-800/50 rounded-lg animate-pulse" />
                            <div className="h-48 bg-gray-100 dark:bg-slate-800/50 rounded-lg animate-pulse" />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // ─── Not Found State ───────────────────────────────────────────
    if (!path) {
        return (
            <div className="min-h-screen bg-[#fcfdfd] dark:bg-slate-950 py-20 px-4">
                <div className="max-w-2xl mx-auto text-center">
                    <div className="w-16 h-16 bg-gray-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-5">
                        <Compass className="w-8 h-8 text-gray-400" />
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        {__('Path not found')}
                    </h1>
                    <p className="text-gray-500 dark:text-gray-400 mb-6 text-sm">
                        {__('The learning path you are looking for does not exist or has been removed.')}
                    </p>
                    <AppLink
                        to="/paths"
                        className="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg transition-colors text-sm"
                    >
                        {dir === 'rtl' ? <ArrowRight className="w-4 h-4" /> : <ArrowLeft className="w-4 h-4" />}
                        <span>{__('Back to paths')}</span>
                    </AppLink>
                </div>
            </div>
        );
    }

    const title = path.title?.[dir === 'rtl' ? 'ar' : 'en'] || path.title?.en || '';
    const description = path.description?.[dir === 'rtl' ? 'ar' : 'en'] || path.description?.en || '';
    const themeColor = path.color || '#3b82f6';
    const IconComponent = (path.icon && (LucideIcons as any)[path.icon]) ? (LucideIcons as any)[path.icon] : Compass;

    const totalCourses = path.courses?.length || 0;
    const totalStudents = path.courses?.reduce((sum: number, c: any) => sum + (c.students_count || 0), 0) || 0;

    // ─── Benefits / What You'll Learn ──────────────────────────────
    const benefits = [
        { icon: Target, text: __('Structured learning from beginner to advanced') },
        { icon: Zap, text: __('Hands-on projects and practical exercises') },
        { icon: Users, text: __('Learn from industry experts') },
        { icon: Clock, text: __('Learn at your own pace') },
        { icon: ShieldCheck, text: __('Lifetime access to course materials') },
        { icon: Star, text: __('Updated content with latest technologies') },
    ];

    // ─── Career Outcomes ───────────────────────────────────────────
    const careerOutcomes = [
        __('Build real-world applications from scratch'),
        __('Apply for professional developer positions'),
        __('Work as a freelancer on development projects'),
        __('Contribute to open source projects'),
    ];

    return (
        <div className="bg-[#fcfdfd] dark:bg-slate-950 min-h-screen pb-16">
            <Seo 
                title={`${title} - ${__('Learning paths')}`}
                description={description}
                image={path.image}
            />

            {/* ─── Cover Hero ──────────────────────────────────────── */}
            <div className="relative pt-20 pb-16 px-4 overflow-hidden min-h-[300px] md:min-h-[380px] flex items-center bg-gray-900">
                <div className="absolute inset-0">
                    <img 
                        src={path.image || "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=2000&auto=format&fit=crop"} 
                        alt={title} 
                        className="w-full h-full object-cover opacity-50"
                    />
                </div>
                <div className="absolute inset-0 bg-gradient-to-t from-[#fcfdfd] dark:from-slate-950 via-gray-900/70 to-gray-900/50" />
                
                <div className="max-w-screen-2xl mx-auto w-full relative z-10 pt-8">
                    {/* Breadcrumb */}
                    <nav className="flex items-center gap-2 text-xs text-gray-400 mb-6 font-medium">
                        <AppLink to="/" className="hover:text-white transition-colors">{__('Home')}</AppLink>
                        <span>/</span>
                        <AppLink to="/paths" className="hover:text-white transition-colors">{__('Learning paths')}</AppLink>
                        <span>/</span>
                        <span className="text-white">{title}</span>
                    </nav>

                    <div className="max-w-4xl">
                        <div 
                            className="w-14 h-14 rounded-lg flex items-center justify-center mb-5 shadow-lg border border-white/20 backdrop-blur-sm"
                            style={{ backgroundColor: themeColor, color: '#fff' }}
                        >
                            <IconComponent className="w-7 h-7" />
                        </div>
                        
                        <h1 className="text-3xl md:text-4xl lg:text-5xl font-black text-white mb-4 leading-tight">
                            {title}
                        </h1>
                        
                        <p className="text-base md:text-lg text-gray-300 leading-relaxed max-w-3xl mb-6">
                            {description}
                        </p>

                        <div className="flex flex-wrap items-center gap-3 text-sm font-bold">
                            <div className="flex items-center gap-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm shadow px-4 py-2 rounded-lg text-gray-900 dark:text-white">
                                <BookOpen className="w-4 h-4 text-brand-600 dark:text-brand-400" />
                                <span>{totalCourses} {__('Courses')}</span>
                            </div>
                            {path.estimated_hours > 0 && (
                                <div className="flex items-center gap-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm shadow px-4 py-2 rounded-lg text-gray-900 dark:text-white">
                                    <Clock className="w-4 h-4 text-amber-600 dark:text-amber-500" />
                                    <span>{path.estimated_hours} {__('Hours')}</span>
                                </div>
                            )}
                            {totalStudents > 0 && (
                                <div className="flex items-center gap-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm shadow px-4 py-2 rounded-lg text-gray-900 dark:text-white">
                                    <Users className="w-4 h-4 text-emerald-600 dark:text-emerald-500" />
                                    <span>{totalStudents.toLocaleString()} {__('Students')}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* ─── Main Content ────────────────────────────────────── */}
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 relative z-20">
                <div className="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6 items-start">
                    
                    {/* ─── Left Column ─────────────────────────────── */}
                    <div className="space-y-6">

                        {/* Benefits / What You'll Learn */}
                        <div className="bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800 p-5">
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <CheckCircle2 className="w-5 h-5 text-emerald-500" />
                                {__('What you will learn')}
                            </h2>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {benefits.map((b, i) => (
                                    <div key={i} className="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300">
                                        <b.icon className="w-4 h-4 mt-0.5 text-brand-500 shrink-0" />
                                        <span>{b.text}</span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Path Curriculum */}
                        <div>
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <PlayCircle className="w-5 h-5 text-brand-500" />
                                {__('Path curriculum')}
                                <span className="text-xs font-normal text-gray-400 dark:text-gray-500 ms-1">
                                    ({totalCourses} {__('Courses')})
                                </span>
                            </h2>

                            <div className="space-y-2">
                                {path.courses?.map((course: any, index: number) => {
                                    const courseTitle = course.title?.[dir === 'rtl' ? 'ar' : 'en'] || course.title?.en || '';
                                    const categoryName = course.category?.name?.[dir === 'rtl' ? 'ar' : 'en'] || course.category?.name?.en || '';

                                    return (
                                        <motion.div
                                            key={course.id}
                                            initial={{ opacity: 0, y: 10 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            transition={{ duration: 0.25, delay: index * 0.05 }}
                                        >
                                            <AppLink
                                                to={`/courses/${course.slug}`}
                                                className="group flex items-center gap-4 p-3 bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800 hover:border-brand-200 dark:hover:border-brand-800/50 hover:shadow-sm transition-all"
                                            >
                                                {/* Step Number */}
                                                <div 
                                                    className="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                                                    style={{ backgroundColor: `${themeColor}18`, color: themeColor }}
                                                >
                                                    {index + 1}
                                                </div>

                                                {/* Thumbnail */}
                                                <div className="w-16 h-11 rounded overflow-hidden shrink-0 bg-gray-100 dark:bg-slate-800">
                                                    <img 
                                                        src={course.image || '/images/placeholder.jpg'} 
                                                        alt={courseTitle}
                                                        className="w-full h-full object-cover"
                                                    />
                                                </div>

                                                {/* Course Info */}
                                                <div className="flex-1 min-w-0">
                                                    <h3 className="text-sm font-bold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors truncate">
                                                        {courseTitle}
                                                    </h3>
                                                    <div className="flex items-center gap-3 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                        {categoryName && <span>{categoryName}</span>}
                                                        {course.duration_hours > 0 && (
                                                            <span className="flex items-center gap-1">
                                                                <Clock className="w-3 h-3" />
                                                                {course.duration_hours} {__('Hours')}
                                                            </span>
                                                        )}
                                                        {course.students_count > 0 && (
                                                            <span className="flex items-center gap-1">
                                                                <Users className="w-3 h-3" />
                                                                {course.students_count}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>

                                                {/* Arrow */}
                                                <div className="text-gray-300 dark:text-gray-600 group-hover:text-brand-500 transition-colors shrink-0">
                                                    {dir === 'rtl' ? <ArrowLeft className="w-4 h-4" /> : <ArrowRight className="w-4 h-4" />}
                                                </div>
                                            </AppLink>
                                        </motion.div>
                                    );
                                })}

                                {(!path.courses || path.courses.length === 0) && (
                                    <div className="text-center py-8 bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800">
                                        <BookOpen className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                                        <p className="text-sm font-medium text-gray-500 dark:text-gray-400">{__('No courses yet')}</p>
                                        <p className="text-xs text-gray-400 dark:text-gray-500 mt-1">{__('This path is currently being updated.')}</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Career Outcomes */}
                        <div className="bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800 p-5">
                            <h2 className="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <Briefcase className="w-5 h-5 text-indigo-500" />
                                {__('Career outcomes')}
                            </h2>
                            <div className="space-y-2.5">
                                {careerOutcomes.map((outcome, i) => (
                                    <div key={i} className="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300">
                                        <CheckCircle2 className="w-4 h-4 mt-0.5 text-indigo-500 shrink-0" />
                                        <span>{outcome}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* ─── Right Sidebar ────────────────────────────── */}
                    <div className="space-y-4 lg:sticky lg:top-24">

                        {/* Path Stats Card */}
                        <div className="bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800 p-5">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white mb-4">{__('Path overview')}</h3>
                            <div className="space-y-3">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <BookOpen className="w-4 h-4" /> {__('Courses')}
                                    </span>
                                    <span className="font-bold text-gray-900 dark:text-white">{totalCourses}</span>
                                </div>
                                {path.estimated_hours > 0 && (
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                            <Clock className="w-4 h-4" /> {__('Total duration')}
                                        </span>
                                        <span className="font-bold text-gray-900 dark:text-white">{path.estimated_hours} {__('Hours')}</span>
                                    </div>
                                )}
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <Users className="w-4 h-4" /> {__('Students')}
                                    </span>
                                    <span className="font-bold text-gray-900 dark:text-white">{totalStudents.toLocaleString()}</span>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <GraduationCap className="w-4 h-4" /> {__('Level')}
                                    </span>
                                    <span className="font-bold text-gray-900 dark:text-white">{__('Beginner to Advanced')}</span>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <Award className="w-4 h-4" /> {__('Certificate')}
                                    </span>
                                    <span className="font-bold text-emerald-600 dark:text-emerald-400">{__('Included')}</span>
                                </div>
                            </div>
                        </div>

                        {/* Certificate Banner */}
                        <div className="rounded-lg overflow-hidden border border-gray-100 dark:border-slate-800" style={{ background: `linear-gradient(135deg, ${themeColor}10, ${themeColor}05)` }}>
                            <div className="p-5">
                                <div className="flex items-center gap-3 mb-3">
                                    <div className="w-10 h-10 rounded-lg flex items-center justify-center" style={{ backgroundColor: `${themeColor}20`, color: themeColor }}>
                                        <Trophy className="w-5 h-5" />
                                    </div>
                                    <h3 className="text-base font-bold text-gray-900 dark:text-white">{__('Earn a certificate')}</h3>
                                </div>
                                <p className="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {__('Complete all courses in this path to earn a professional certificate that validates your skills.')}
                                </p>
                            </div>
                        </div>

                        {/* Suggested Paths */}
                        {suggestedPaths.length > 0 && (
                            <div className="bg-white dark:bg-slate-900 rounded-lg border border-gray-100 dark:border-slate-800 p-5">
                                <h3 className="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <Compass className="w-4 h-4 text-brand-500" />
                                    {__('Suggested Paths')}
                                </h3>
                                <div className="space-y-3">
                                    {suggestedPaths.map((suggestedPath) => {
                                        const suggestedIcon = (suggestedPath.icon && (LucideIcons as any)[suggestedPath.icon]) 
                                            ? (LucideIcons as any)[suggestedPath.icon] 
                                            : Compass;
                                        const suggestedColor = suggestedPath.color || '#3b82f6';
                                        const suggestedTitle = suggestedPath.title?.[dir === 'rtl' ? 'ar' : 'en'] || suggestedPath.title?.en || '';
                                        
                                        return (
                                            <AppLink 
                                                key={suggestedPath.id}
                                                to={`/paths/${suggestedPath.slug}`}
                                                className="group flex gap-3 p-3 rounded-lg border border-gray-100 dark:border-slate-800 hover:border-brand-200 dark:hover:border-brand-800/50 hover:shadow-sm transition-all bg-gray-50/50 dark:bg-slate-800/30"
                                            >
                                                <div 
                                                    className="w-10 h-10 shrink-0 rounded-lg flex items-center justify-center transition-transform group-hover:scale-105 duration-300"
                                                    style={{ backgroundColor: `${suggestedColor}15`, color: suggestedColor }}
                                                >
                                                    {React.createElement(suggestedIcon, { className: "w-5 h-5" })}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <h4 className="font-bold text-gray-900 dark:text-white text-sm group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors line-clamp-1">
                                                        {suggestedTitle}
                                                    </h4>
                                                    <div className="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        <span className="flex items-center gap-1">
                                                            <BookOpen className="w-3 h-3" />
                                                            {suggestedPath.courses_count || 0} {__('Courses')}
                                                        </span>
                                                        {suggestedPath.estimated_hours > 0 && (
                                                            <span className="flex items-center gap-1">
                                                                <Clock className="w-3 h-3" />
                                                                {suggestedPath.estimated_hours} {__('Hours')}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </AppLink>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </div>

                </div>
            </div>
        </div>
    );
};

export default PathDetailPage;
