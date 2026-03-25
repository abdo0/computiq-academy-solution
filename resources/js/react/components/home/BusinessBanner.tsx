import React from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { useAppNavigate } from '../../hooks/useAppNavigate';
import { HomeSection } from '../../types';

interface BusinessBannerProps {
  sectionData?: HomeSection;
}

const BusinessBanner: React.FC<BusinessBannerProps> = ({ sectionData }) => {
  const { language } = useLanguage();
  const navigate = useAppNavigate();
  const isRTL = language === 'ar' || language === 'ku';

  const title = sectionData?.title?.[language as 'en'|'ar'|'ku'] || sectionData?.title?.['en'] || 'طور فريقك مع Computiq Business';
  const desc = sectionData?.description?.[language as 'en'|'ar'|'ku'] || sectionData?.description?.['en'] || 'منصة الوصول ليس مجرد منصة للتعلم بل هي شريكك الأفضل.';
  const cta = sectionData?.extra_data?.cta_text?.[language as 'en'|'ar'|'ku'] || sectionData?.extra_data?.cta_text?.['en'] || 'ابدأ الان';

  return (
    <section className="py-16 lg:py-20 bg-white dark:bg-slate-950">
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Banner Card */}
        <div className="bg-[#eef2ff] dark:bg-gray-800 rounded-md p-8 lg:p-14 relative overflow-hidden flex flex-col lg:flex-row items-center gap-10">
          
          {/* Decorative faint background shapes */}
          <div className="absolute inset-0 pointer-events-none overflow-hidden">
            <div className="absolute -top-24 -end-24 w-64 h-64 bg-brand-200/40 dark:bg-brand-900/20 rounded-full blur-3xl"></div>
            <div className="absolute -bottom-24 -start-24 w-64 h-64 bg-indigo-200/40 dark:bg-indigo-900/20 rounded-full blur-3xl"></div>
          </div>

          {/* Text Content */}
          <div className="relative z-10 lg:w-1/2 text-center lg:text-start flex flex-col items-center lg:items-start justify-center">
            
            <h2 className="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white mb-4 leading-tight">
              {title}
            </h2>
            
            <p className="text-gray-600 dark:text-gray-300 text-sm lg:text-base mb-8 max-w-sm font-medium leading-relaxed">
              {desc}
            </p>
            
            <button
              onClick={() => navigate('/contact')}
              className="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-12 rounded-md transition-all shadow-md active:scale-95"
            >
              {cta}
            </button>
            
          </div>

          {/* Partner Logos Grid */}
          <div className="relative z-10 lg:w-1/2 flex items-center justify-center lg:justify-end">
             <div className="grid grid-cols-2 gap-4 lg:gap-6 w-full max-w-md">
                {/* Simulated partner logos using styled boxes corresponding to the specific block on the left */}
                <div className="bg-white dark:bg-gray-700 h-20 rounded-md shadow-sm flex items-center justify-center font-bold text-gray-400">
                   كفو
                </div>
                <div className="bg-white dark:bg-gray-700 h-20 rounded-md shadow-sm flex items-center gap-2 justify-center font-bold text-gray-500">
                   <span className="w-5 h-5 rounded-full bg-brand-500"></span> RTG
                </div>
                <div className="bg-white dark:bg-gray-700 h-20 rounded-md shadow-sm flex items-center justify-center font-bold text-gray-400">
                   <div className="flex gap-1">
                      <span className="w-3 h-3 bg-blue-500"></span>
                      <span className="w-3 h-3 bg-red-500"></span>
                      <span className="w-3 h-3 bg-yellow-500"></span>
                   </div>
                </div>
                <div className="bg-white dark:bg-gray-700 h-20 rounded-md shadow-sm flex items-center justify-center font-bold text-gray-400">
                   {/* Placeholder for remaining logo */}
                   <span className="text-indigo-900 font-black">CompuTiq</span>
                </div>
             </div>
          </div>

        </div>

      </div>
    </section>
  );
};

export default BusinessBanner;
