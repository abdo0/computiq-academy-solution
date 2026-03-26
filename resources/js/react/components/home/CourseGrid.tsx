import React, { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import CourseCard from './CourseCard';
import { useLanguage } from '../../contexts/LanguageContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { HomeSection } from '../../types';
import { useAppNavigate } from '../../hooks/useAppNavigate';

interface CourseGridProps {
  sectionData?: HomeSection;
  isLoading?: boolean;
  title?: string;
  subtitle?: string;
  courses?: any[];
  tabs?: any[];
  showTabs?: boolean;
}

const CourseGrid: React.FC<CourseGridProps> = ({
  sectionData,
  isLoading = false,
  title: propTitle,
  subtitle: propSubtitle,
  courses: propCourses,
  tabs: propTabs,
  showTabs = true,
}) => {
  const { language } = useLanguage();
  const { __, t } = useTranslation();
  const [activeTab, setActiveTab] = useState(0);
  const navigate = useAppNavigate();

  const langKey = language as 'en' | 'ar' | 'ku';
  const isRTL = language === 'ar' || language === 'ku';

  const sectionTitle = propTitle || sectionData?.title?.[langKey] || sectionData?.title?.['en'] || __('Discover courses title');
  const sectionSubtitle = propSubtitle || sectionData?.description?.[langKey] || sectionData?.description?.['en'] || '';

  const tabs = propTabs || sectionData?.extra_data?.tabs || [
    { label: { ar: 'كل التخصصات', en: 'All Categories', ku: 'هەموو' }, slug: 'all' },
    { label: { ar: 'تطوير الذات', en: 'Self Dev', ku: 'تطوير الذات' }, slug: 'self-dev' },
    { label: { ar: 'الأعمال والإدارة', en: 'Business', ku: 'الأعمال' }, slug: 'business' },
    { label: { ar: 'التكنولوجيا', en: 'Technology', ku: 'التكنولوجيا' }, slug: 'tech' },
    { label: { ar: 'التصوير', en: 'Photography', ku: 'التصوير' }, slug: 'photo' },
    { label: { ar: 'الفنون والتصميم', en: 'Arts & Design', ku: 'الفنون' }, slug: 'art' },
    { label: { ar: 'اللغات', en: 'Languages', ku: 'اللغات' }, slug: 'lang' },
  ];

  // Demo courses matching the screenshot as closely as possible
  const demoCourses = [
    {
      image: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop',
      badge: 'مسار متكامل',
      title: 'البرمجة بلغة جافا، من الصفر للاحتراف',
      instructor: 'جون دانيال',
      instructorImage: 'https://i.pravatar.cc/150?img=11',
      rating: 4.8,
      reviewCount: 1250,
      hours: 42,
      students: 8500,
      price: '500',
      oldPrice: '750',
      link: '/courses',
    },
    {
      image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=600&auto=format&fit=crop',
      badge: 'دورة مسجلة',
      badgeColor: 'bg-emerald-500',
      title: 'أساسيات علم البيانات المتقدمة',
      instructor: 'سارة خالد',
      instructorImage: 'https://i.pravatar.cc/150?img=5',
      rating: 4.9,
      reviewCount: 870,
      hours: 56,
      students: 5200,
      price: '250',
      oldPrice: '300',
      link: '/courses',
    },
    {
      image: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=600&auto=format&fit=crop',
      badge: 'مباشر',
      badgeColor: 'bg-indigo-600',
      title: 'دليل التسويق الرقمي الشامل 2024',
      instructor: 'أحمد محمود',
      instructorImage: 'https://i.pravatar.cc/150?img=33',
      rating: 4.7,
      reviewCount: 2100,
      hours: 38,
      students: 12000,
      price: '200',
      oldPrice: '400',
      link: '/courses',
    },
    {
      image: 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?q=80&w=600&auto=format&fit=crop',
      badge: 'الأكثر مبيعاً',
      badgeColor: 'bg-amber-500',
      title: 'تطوير تطبيقات الويب باستخدام ريأكت',
      instructor: 'سمير حسن',
      instructorImage: 'https://i.pravatar.cc/150?img=60',
      rating: 4.9,
      reviewCount: 3400,
      hours: 64,
      students: 18000,
      price: '850',
      oldPrice: '1200',
      link: '/courses',
    },
  ];

  const allCourses = propCourses || sectionData?.extra_data?.courses || demoCourses;

  // Filter courses based on active tab
  const activeTabSlug = tabs[activeTab]?.slug || 'all';
  const filteredCourses = activeTabSlug === 'all'
    ? allCourses
    : allCourses.filter((c: any) => c.categorySlug === activeTabSlug);

  const displayCourses = filteredCourses.slice(0, 4);

  const CardSkeleton = () => (
    <div className="bg-white dark:bg-slate-900 rounded-md overflow-hidden border border-gray-100 dark:border-slate-800 animate-pulse flex flex-col h-full">
      <div className="aspect-[16/10] bg-gray-200 dark:bg-slate-800 w-full relative"></div>
      <div className="p-6 flex-1 flex flex-col relative space-y-4">
        <div className="absolute -top-6 start-6 z-20 flex gap-3">
           <div className="w-12 h-12 bg-gray-300 dark:bg-slate-700 rounded-full border-4 border-white dark:border-slate-900"></div>
           <div className="pt-6"><div className="w-20 h-3 bg-gray-200 dark:bg-slate-800 rounded"></div></div>
        </div>
        <div className="pt-8">
           <div className="h-4 bg-gray-200 dark:bg-slate-800 rounded w-full mb-2"></div>
           <div className="h-4 bg-gray-200 dark:bg-slate-800 rounded w-3/4 mb-4"></div>
        </div>
        <div className="flex gap-2">
           <div className="h-6 w-20 bg-gray-200 dark:bg-slate-800 rounded-md"></div>
           <div className="h-6 w-20 bg-gray-200 dark:bg-slate-800 rounded-md"></div>
        </div>
        <div className="flex-1"></div>
        <div className="flex justify-between items-center pt-5 mt-2 border-t border-gray-100 dark:border-slate-800">
          <div className="flex flex-col gap-1">
             <div className="h-3 w-10 bg-gray-200 dark:bg-slate-800 rounded"></div>
             <div className="h-6 w-16 bg-gray-200 dark:bg-slate-800 rounded"></div>
          </div>
          <div className="flex gap-2">
             <div className="h-[38px] w-[38px] bg-gray-200 dark:bg-slate-800 rounded-[14px]"></div>
             <div className="h-[38px] w-24 bg-gray-200 dark:bg-slate-800 rounded-[14px]"></div>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <section className="py-12 lg:py-20 bg-white dark:bg-slate-950 overflow-hidden relative">
      {/* Background Decorative Blur */}
      <div className="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-brand-500/5 blur-3xl pointer-events-none"></div>
      
      <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        {/* Section Header */}
        <div className="flex flex-col sm:flex-row items-baseline justify-between mb-10 gap-4">
          <div>
            <div className="w-[45px] h-[8px] bg-brand-600 rounded-full mb-4"></div>
            <h2 className="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
              {sectionTitle}
            </h2>
            {sectionSubtitle && (
              <p className="text-base text-gray-500 dark:text-gray-400 font-medium mt-2 max-w-2xl">
                {sectionSubtitle}
              </p>
            )}
          </div>

          {/* View All Link (on the left in RTL) */}
          <button 
            onClick={() => navigate('/courses')}
            className="flex items-center gap-1.5 text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 text-sm font-bold transition-colors group"
          >
            <span>{sectionData?.extra_data?.view_all_text?.[langKey] || sectionData?.extra_data?.view_all_text?.['en'] || __('View all courses')}</span>
            {isRTL ? <ChevronLeft className="w-4 h-4 transition-transform group-hover:-translate-x-1" /> : <ChevronRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />}
          </button>
        </div>

        {/* Category Tabs */}
        {showTabs && (
          <div className="flex items-center gap-3 lg:gap-4 mb-10 overflow-x-auto pb-4 scrollbar-hide pt-2">
            {tabs.map((tab: any, idx: number) => {
              const isActive = activeTab === idx;
              return (
                <button
                  key={idx}
                  onClick={() => setActiveTab(idx)}
                  className={`text-sm font-bold whitespace-nowrap transition-all px-6 py-2.5 rounded-md border ${
                    isActive
                      ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20 border-transparent'
                      : 'bg-gray-50 text-gray-600 border-gray-100/50 hover:bg-gray-100 dark:bg-slate-800/50 dark:border-slate-700/50 dark:text-gray-400 dark:hover:bg-slate-800 hover:-translate-y-0.5'
                  }`}
                >
                  {typeof tab === 'string' ? tab : t(tab.label)}
                </button>
              );
            })}
          </div>
        )}

        {/* Course Cards Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {isLoading ? (
            <>
              <CardSkeleton />
              <CardSkeleton />
              <CardSkeleton />
              <CardSkeleton />
            </>
          ) : displayCourses.length > 0 ? (
            displayCourses.map((course: any, idx: number) => (
              <CourseCard key={idx} {...course} />
            ))
          ) : (
            <div className="col-span-full py-12 text-center text-gray-500 dark:text-gray-400 font-medium bg-gray-50 dark:bg-slate-900 rounded-md border border-gray-100 dark:border-slate-800 border-dashed">
              {__('No results') || 'لا توجد دورات في هذا القسم حالياً'}
            </div>
          )}
        </div>

      </div>
    </section>
  );
};

export default CourseGrid;
