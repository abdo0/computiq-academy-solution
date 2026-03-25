import React from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { useTranslation } from '../../contexts/TranslationProvider';

interface SponsorsProps {
  sponsorsData?: {
    partners: any[];
    employment: any[];
  };
  isLoading?: boolean;
}

const Sponsors: React.FC<SponsorsProps> = ({ sponsorsData, isLoading }) => {
  const { language } = useLanguage();
  const { partners = [], employment = [] } = sponsorsData || {};

  if (isLoading) {
    return (
      <>
        <section className="relative flex flex-col items-center justify-start w-full max-w-full h-full pt-16 pb-8 gap-y-10 overflow-x-hidden overflow-hidden bg-white dark:bg-slate-950 border-b border-gray-100 dark:border-slate-800">
          <div className="flex flex-col items-center space-y-4 w-full">
            <div className="w-[45px] h-[8px] bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-48 animate-pulse"></div>
            <div className="h-4 bg-gray-100 dark:bg-gray-800 rounded w-64 md:w-96 animate-pulse"></div>
          </div>
          <div className="w-full max-w-full overflow-hidden flex justify-center py-4" dir="ltr">
            <div className="flex gap-4 sm:gap-6 justify-center w-full flex-wrap sm:flex-nowrap">
              {[1, 2, 3, 4, 5, 6].map(i => (
                <div key={i} className={`flex-shrink-0 w-[120px] sm:w-[160px] h-[60px] sm:h-[80px] bg-gray-100 dark:bg-gray-800 rounded-md animate-pulse ${i > 3 ? 'hidden sm:block' : ''} ${i > 4 ? 'hidden md:block' : ''}`}></div>
              ))}
            </div>
          </div>
        </section>
        <section className="relative flex flex-col items-center justify-start w-full max-w-full h-full pt-16 pb-16 gap-y-10 overflow-x-hidden overflow-hidden bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-800">
          <div className="flex flex-col items-center space-y-4 w-full">
            <div className="w-[45px] h-[8px] bg-gray-200 dark:bg-gray-700 rounded-full animate-pulse"></div>
            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-64 animate-pulse"></div>
            <div className="h-4 bg-gray-100 dark:bg-gray-800 rounded w-64 md:w-96 animate-pulse"></div>
          </div>
          <div className="w-full max-w-full overflow-hidden flex justify-center py-4" dir="ltr">
            <div className="flex gap-4 sm:gap-6 justify-center w-full flex-wrap sm:flex-nowrap">
              {[1, 2, 3, 4, 5, 6].map(i => (
                <div key={i} className={`flex-shrink-0 w-[120px] sm:w-[160px] h-[60px] sm:h-[80px] bg-gray-200 dark:bg-gray-700 rounded-md animate-pulse ${i > 3 ? 'hidden sm:block' : ''} ${i > 4 ? 'hidden md:block' : ''}`}></div>
              ))}
            </div>
          </div>
        </section>
      </>
    );
  }

  if (!partners.length && !employment.length) return null;

  return (
    <>
      <style>{`
        @keyframes marquee {
          0% { transform: translateX(0%); }
          100% { transform: translateX(-50%); }
        }
        @keyframes marquee-reverse {
          0% { transform: translateX(-50%); }
          100% { transform: translateX(0%); }
        }
        .animate-marquee {
          animation: marquee 60s linear infinite;
        }
        .animate-marquee-reverse {
          animation: marquee-reverse 120s linear infinite;
        }
        .animate-marquee:hover, .animate-marquee-reverse:hover {
          animation-play-state: paused;
        }
      `}</style>
      
      {partners.length > 0 && (
      <section className="relative flex flex-col items-center justify-start w-full h-full pt-16 pb-8 gap-y-10 overflow-hidden bg-white dark:bg-slate-950 border-b border-gray-100 dark:border-slate-800">
        <p className="flex flex-col items-center justify-center text-2xl font-bold md:text-4xl w-full md:w-[50%] text-center space-y-2 text-gray-900 dark:text-white">
          <span className="w-[45px] h-[8px] bg-brand-600 rounded-full mb-2"></span>
          <span className="text-center">{language === 'ar' ? 'شركاؤنا' : language === 'ku' ? 'هاوبەشەکانمان' : 'Our Partners'}</span>
          <span className="text-base font-medium md:text-xl text-gray-500 dark:text-gray-400 max-w-2xl px-4 mt-4">
            {language === 'ar' 
              ? 'تمكين الشعب العراقي من خلال برامج عالية الجودة وفرص حقيقية، بالتعاون مع الشركاء' 
              : language === 'ku' 
              ? 'بەهێزکردنی گەلی عێراق لە ڕێگەی بەرنامەی کوالێتی بەرز و دەرفەتی ڕاستەقینەوە، بە هاوکاری لەگەڵ هاوبەشەکان'
              : 'Empowering people through high-quality programs and real opportunities, in collaboration with partners'}
          </span>
        </p>
        
        <div className="w-full max-w-full overflow-hidden relative group" dir="ltr">
          <div className="flex animate-marquee gap-6 w-max pl-6 flex-nowrap">
            {/* Double the array for seamless infinite scrolling */}
            {[...partners, ...partners].map((sponsor, index) => (
              <div key={`${sponsor.id}-${index}`} className="flex-shrink-0 flex items-center justify-center w-[160px] h-[80px] p-2 transition-all duration-300 group/item hover:scale-105">
                <img 
                  src={`/storage/${sponsor.image}`} 
                  alt={sponsor.name} 
                  className="w-full h-full object-contain max-h-[70px] mix-blend-multiply dark:mix-blend-normal grayscale group-hover/item:grayscale-0 opacity-70 group-hover/item:opacity-100 transition-all duration-300" 
                  loading="lazy"
                />
              </div>
            ))}
          </div>
        </div>
      </section>
      )}

      {employment.length > 0 && (
      <section className="relative flex flex-col items-center justify-start w-full h-full pt-16 pb-16 gap-y-10 overflow-hidden bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-800">
        <p className="flex flex-col items-center justify-center text-2xl font-bold md:text-4xl w-full md:w-[50%] text-center space-y-2 text-gray-900 dark:text-white">
          <span className="w-[45px] h-[8px] bg-brand-600 rounded-full mb-2"></span>
          <span className="text-center">{language === 'ar' ? 'خدمات التوظيف' : language === 'ku' ? 'خزمەتگوزاریەکانی دامەزراندن' : 'Employment Services'}</span>
          <span className="text-base font-medium md:text-xl text-gray-500 dark:text-gray-400 max-w-2xl px-4 mt-4">
            {language === 'ar' 
              ? 'ربط الخريجين بأفضل فرص العمل والتدريب مع الشركاء الرائدين' 
              : language === 'ku' 
              ? 'بەستنەوەی دەرچووان بە باشترین دەرفەتەکانی کار و مەشق لەگەڵ هاوبەشە پێشەنگەکان'
              : 'Connecting graduates with the best job and training opportunities with leading partners'}
          </span>
        </p>
        
        <div className="w-full max-w-full overflow-hidden relative group" dir="ltr">
          <div className="flex animate-marquee-reverse gap-6 w-max pl-6 flex-nowrap">
            {/* Double the array for seamless infinite scrolling */}
            {[...employment, ...employment].map((sponsor, index) => (
              <div key={`${sponsor.id}-${index}`} className="flex-shrink-0 flex items-center justify-center w-[160px] h-[80px] p-2 transition-all duration-300 group/item hover:scale-105">
                <img 
                  src={`/storage/${sponsor.image}`} 
                  alt={sponsor.name} 
                  className="w-full h-full object-contain max-h-[70px] mix-blend-multiply dark:mix-blend-normal grayscale group-hover/item:grayscale-0 opacity-70 group-hover/item:opacity-100 transition-all duration-300" 
                  loading="lazy"
                />
              </div>
            ))}
          </div>
        </div>
      </section>
      )}
    </>
  );
};

export default Sponsors;
