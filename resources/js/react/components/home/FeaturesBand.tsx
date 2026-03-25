import React from 'react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { useLanguage } from '../../contexts/LanguageContext';
import { HomeSection } from '../../types';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useAppNavigate } from '../../hooks/useAppNavigate';

interface CategoryCardsProps {
    sectionData?: HomeSection;
    isLoading?: boolean;
}

const FeaturesBand: React.FC<CategoryCardsProps> = ({ sectionData, isLoading = false }) => {
    const { language } = useLanguage();
    const { __, t } = useTranslation();
    const navigate = useAppNavigate();
    const isRTL = language === 'ar' || language === 'ku';

    // Using images that match the screenshot's concept of desk/laptops/books
    const defaultCategories = [
        { 
            image: "https://images.unsplash.com/photo-1434493789847-2f02dc6cf6be?q=80&w=400&auto=format&fit=crop", 
            title: __('Cat personal dev') || 'التطوير الذاتي',
            count: 11074,
            link: '/courses'
        },
        { 
            image: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=400&auto=format&fit=crop", 
            title: __('Cat engineering') || 'هندسة',
            count: 12240,
            link: '/courses'
        },
        { 
            image: "https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=400&auto=format&fit=crop", 
            title: __('Cat business') || 'تأسيس بزنس',
            count: 535,
            link: '/courses'
        },
    ];

    const apiCategories = sectionData?.extra_data?.categories?.map((cat: any) => ({
        image: cat.image,
        title: t(cat.title),
        count: cat.count,
        link: cat.link || '/courses'
    }));

    const categoriesToDisplay = apiCategories || defaultCategories;

    const mainTitle = sectionData?.title?.[language as 'en' | 'ar' | 'ku'] || sectionData?.title?.['en'] || 'تعلم وطور أهم المهارات الشخصية والمهنية';
    const subTitle = sectionData?.description?.[language as 'en' | 'ar' | 'ku'] || sectionData?.description?.['en'] || 'عدد كبير من التخصصات بانتظارك';

    if (isLoading) {
        return (
        <section className="py-16 bg-white dark:bg-slate-950">
            <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col sm:flex-row items-baseline justify-between mb-10 gap-4">
                    <div>
                        <div className="w-[45px] h-[8px] bg-brand-600 rounded-full mb-4 animate-pulse"></div>
                        <div className="h-8 bg-gray-200 dark:bg-slate-800 rounded w-64 animate-pulse"></div>
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    {[1, 2, 3].map((idx) => (
                        <div key={idx} className="bg-gray-100 dark:bg-slate-800 rounded-md h-[300px] animate-pulse"></div>
                        ))}
                    </div>
                </div>
            </section>
        );
    }

    return (
        <section className="py-16 lg:py-20 bg-white dark:bg-slate-950">
            <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
                
                {/* Header Row */}
                <div className="flex flex-col sm:flex-row items-baseline justify-between mb-10 gap-4">
                    <div>
                        <div className="w-[45px] h-[8px] bg-brand-600 rounded-full mb-4"></div>
                        <h2 className="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            {mainTitle}
                        </h2>
                        {subTitle && (
                            <p className="text-base text-gray-500 dark:text-gray-400 font-medium mt-2 max-w-2xl">
                                {subTitle}
                            </p>
                        )}
                    </div>
                </div>

                {/* 3 Large Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    {categoriesToDisplay.map((cat: any, idx: number) => {
                        const imageSrc = cat.image?.startsWith('http') || cat.image?.startsWith('data:') 
                                            ? cat.image 
                                            : `/storage/${cat.image}`;
                        return (
                            <div 
                                key={idx}
                                onClick={() => navigate(cat.link)}
                                className="group relative rounded-md overflow-hidden cursor-pointer min-h-[300px] lg:min-h-[340px] flex flex-col justify-end p-6 lg:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.12)] transition-all duration-500 hover:-translate-y-1.5 border border-transparent hover:border-white/20"
                            >
                                {/* Background Image layer */}
                                <div className="absolute inset-0 z-0 bg-gray-200 dark:bg-slate-800">
                                    <img 
                                        src={imageSrc} 
                                        alt={cat.title} 
                                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                        loading="lazy"
                                        onError={(e) => {
                                            (e.target as HTMLImageElement).src = 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=400&auto=format&fit=crop';
                                        }}
                                    />
                                    {/* Heavy vignette gradient for text legibility */}
                                    <div className="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-black/10 group-hover:from-brand-950/90 transition-colors duration-500"></div>
                                </div>
                                
                                {/* Bottom Content Area */}
                                <div className="relative z-10 flex items-center justify-between w-full">
                                    <div className="text-start pe-4">
                                        <h3 className="text-xl lg:text-2xl font-black text-white mb-2 drop-shadow-md group-hover:text-brand-300 transition-colors">
                                            {cat.title}
                                        </h3>
                                        <p className="text-sm font-bold text-gray-300 drop-shadow-md bg-white/10 backdrop-blur-md px-3 py-1 rounded-xl inline-block">
                                            {cat.count} {__('Course')}
                                        </p>
                                    </div>
                                    <div className="w-12 h-12 flex-shrink-0 rounded-full bg-white/20 backdrop-blur-md border border-white/20 flex items-center justify-center text-white group-hover:bg-brand-600 group-hover:border-brand-500 transition-all duration-300 shadow-lg group-hover:shadow-brand-600/50 group-hover:scale-110">
                                        {isRTL ? <ArrowLeft size={20} className="group-hover:-translate-x-1 transition-transform" /> : <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />}
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

            </div>
        </section>
    );
};

export default FeaturesBand;
