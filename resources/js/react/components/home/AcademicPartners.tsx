import React from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { HomeSection } from '../../types';
import { useTranslation } from '../../contexts/TranslationProvider';

interface AcademicPartnersProps {
  isLoading?: boolean;
  sectionData?: HomeSection;
}

const AcademicPartners: React.FC<AcademicPartnersProps> = ({ isLoading = false, sectionData }) => {
  const { language } = useLanguage();
  const { __ } = useTranslation();

  // Partner logos - using placeholder brand names with styled text
  const fallbackPartners = [
    { name: 'Drake', style: 'font-serif font-bold text-2xl' },
    { name: 'IMO', style: 'font-black text-xl tracking-wider' },
    { name: 'Ministry', style: 'font-medium text-lg tracking-wide' },
    { name: 'PSI', style: 'font-black text-xl' },
    { name: 'DCAS', style: 'font-bold text-lg tracking-widest' },
    { name: 'NAC', style: 'font-black text-2xl italic' },
    { name: 'Academy', style: 'font-serif font-bold text-xl' },
  ];

  if (isLoading) {
    return (
      <section className="py-12 bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
          <div className="h-5 w-40 bg-gray-200 dark:bg-gray-700 rounded mx-auto mb-8 animate-pulse"></div>
          <div className="flex items-center justify-center gap-12 flex-wrap">
            {[1,2,3,4,5,6].map(i => (
              <div key={i} className="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  const title = sectionData?.title?.[language as 'en'|'ar'|'ku'] || sectionData?.title?.['en'] || __('Academic partners title');
  const logos = sectionData?.extra_data?.logos || [];

  return (
    <section className="py-12 lg:py-16 bg-white dark:bg-gray-900 border-y border-gray-100 dark:border-gray-800">
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <h3 className="text-center text-lg font-bold text-gray-900 dark:text-white mb-10">
          {title}
        </h3>
        <div className="flex items-center justify-center gap-8 lg:gap-16 flex-wrap">
          {logos.length > 0 ? (
            logos.map((logo: string, idx: number) => (
              <img 
                key={idx} 
                src={logo} 
                alt={`Partner ${idx + 1}`} 
                className="h-10 lg:h-12 object-contain grayscale hover:grayscale-0 transition-all opacity-60 hover:opacity-100" 
              />
            ))
          ) : (
            fallbackPartners.map((partner, idx) => (
              <div
                key={idx}
                className="text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors cursor-default select-none"
              >
                <span className={partner.style}>{partner.name}</span>
              </div>
            ))
          )}
        </div>
      </div>
    </section>
  );
};

export default AcademicPartners;
