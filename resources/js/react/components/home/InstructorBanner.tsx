import React from 'react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { useLanguage } from '../../contexts/LanguageContext';
import { useAppNavigate } from '../../hooks/useAppNavigate';
import { HomeSection } from '../../types';

interface InstructorBannerProps {
  sectionData?: HomeSection;
}

const InstructorBanner: React.FC<InstructorBannerProps> = ({ sectionData }) => {
  const { language } = useLanguage();
  const navigate = useAppNavigate();
  const isRTL = language === 'ar' || language === 'ku';

  const title = sectionData?.title?.[language as 'en'|'ar'|'ku'] || sectionData?.title?.['en'] || 'شارك خبرتك مع آلاف المتعلمين...';
  const desc = sectionData?.description?.[language as 'en'|'ar'|'ku'] || sectionData?.description?.['en'] || 'حيث إن منصة الوصول ليست مجرد مسار للتعلم، بل مسار للمدربين';
  const cta = sectionData?.extra_data?.cta_text?.[language as 'en'|'ar'|'ku'] || sectionData?.extra_data?.cta_text?.['en'] || 'انضم لنا الان';
  let dynamicImage = sectionData?.extra_data?.image || null;
  if (dynamicImage && !dynamicImage.startsWith('http') && !dynamicImage.startsWith('/assets/')) {
    dynamicImage = `/storage/${dynamicImage}`;
  }

  return (
    <section className="py-0 relative bg-white dark:bg-slate-950">
      <div className="bg-[#0b1021] dark:bg-[#0b1021] relative overflow-hidden">
        {/* Soft background glow to match the screenshot's dark blue tone */}
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
          <div className="absolute top-0 end-1/4 w-[500px] h-[500px] bg-brand-900/20 rounded-full blur-[100px]"></div>
        </div>

        <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative grid grid-cols-1 lg:grid-cols-2 lg:gap-10 pb-0">
          
          {/* Text Content */}
          <div className="text-center lg:text-start pt-16 lg:py-24 z-10 flex flex-col justify-center">
            <h2 className="text-3xl lg:text-4xl xl:text-5xl font-black text-white mb-6 leading-[1.3]">
              {title}
            </h2>
            <p className="text-gray-300 text-sm lg:text-lg mb-10 max-w-lg mx-auto lg:mx-0 font-medium">
              {desc}
            </p>
            <div className="flex justify-center lg:justify-start">
              <button
                onClick={() => navigate('/contact')}
                className="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-12 rounded-md transition-all shadow-lg active:scale-95"
              >
                {cta}
              </button>
            </div>
          </div>

          {/* Image */}
          <div className="flex justify-center lg:justify-end items-end pt-10 lg:pt-0">
            {dynamicImage && (
            <div className="relative w-full max-w-md lg:max-w-lg">
              <img
                src={dynamicImage}
                alt="Instructor"
                className="w-full h-auto object-contain object-bottom drop-shadow-2xl"
                style={{ WebkitMaskImage: 'linear-gradient(to top, black 80%, transparent 100%)', maskImage: 'linear-gradient(to top, black 80%, transparent 100%)' }}
              />
            </div>
            )}
          </div>
          
        </div>
      </div>
    </section>
  );
};

export default InstructorBanner;
