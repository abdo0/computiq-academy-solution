import React from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { useSettings } from '../contexts/SettingsContext';
import { Stat, HomeSection } from '../types';
import { useTranslation } from '../contexts/TranslationProvider';
import { useAppNavigate } from '../hooks/useAppNavigate';

interface HeroProps {
  sectionData?: HomeSection;
  stats?: Stat[];
  isLoading?: boolean;
}

const Hero: React.FC<HeroProps> = ({ sectionData, isLoading = false }) => {
  const { language } = useLanguage();
  const { __, t } = useTranslation();
  const { heroContent, isLoading: isSettingsLoading } = useSettings();
  const navigate = useAppNavigate();



  const title = t(sectionData?.title) || t(heroContent?.title) || __('Hero title');
  const subtitle = t(sectionData?.description) || t(heroContent?.subtitle) || __('Hero subtitle');

  if (isLoading || isSettingsLoading) {
    return (
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-pulse">
        <div className="w-full h-[400px] md:h-[500px] bg-gray-200 dark:bg-gray-800 rounded-md"></div>
      </div>
    );
  }

  const bgImage = sectionData?.extra_data?.background_image 
    ? `/storage/${sectionData.extra_data.background_image}` 
    : (heroContent?.background_image ? `/storage/${heroContent.background_image}` : "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1200&auto=format&fit=crop");

  return (
    <section className="bg-white dark:bg-gray-900 pt-8 pb-12">
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Inset Banner Container */}
        <div 
          className="relative w-full rounded-md overflow-hidden shadow-2xl min-h-[400px] md:min-h-[500px] flex items-center bg-gray-900"
        >
          {/* Background Image (Covering the whole card) */}
          <div className="absolute inset-0">
            <img 
              src={bgImage} 
              alt={title} 
              className="w-full h-full object-cover opacity-90"
            />
          </div>

          {/* Overlay Gradient (Fades from white/glass on the right (RTL start) to transparent on left) */}
          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/80 to-white dark:via-gray-900/80 dark:to-gray-900 rtl:bg-gradient-to-l"></div>
          
          {/* Content Card (Positioned on the Start side) */}
          <div className="relative z-10 w-full md:w-1/2 lg:w-[45%] p-8 md:p-12 lg:p-16 flex flex-col justify-center">
            
            {/* Title */}
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 dark:text-white leading-[1.2] mb-4 tracking-tight">
              {title}
            </h1>
            
            {/* Subtitle */}
            <p className="text-base md:text-lg text-gray-700 dark:text-gray-300 font-bold leading-relaxed mb-8">
              {subtitle}
            </p>
            
            {/* Buttons */}
            <div className="flex flex-col sm:flex-row gap-4 items-center">
              <button 
                onClick={() => navigate('/courses')}
                className="w-full sm:w-auto bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-brand-500/30 transition-all text-center"
              >
                {t(sectionData?.extra_data?.cta_text) || t(heroContent?.cta_text) || __('Hero cta')}
              </button>
              
              <button 
                onClick={() => navigate('/about')}
                className="w-full sm:w-auto bg-white/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800 font-bold py-3.5 px-8 rounded-xl transition-all text-center shadow-sm backdrop-blur-sm"
              >
                {t(sectionData?.extra_data?.secondary_cta_text) || __('Hero secondary cta')}
              </button>
            </div>
          </div>
        </div>

      </div>
    </section>
  );
};

export default Hero;