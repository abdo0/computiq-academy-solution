import React, { useState, useEffect } from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { dataService } from '../../services/dataService';
import { HomeSection } from '../../types';
import { useSettings } from '../../contexts/SettingsContext';
import { Star } from 'lucide-react';
import { useTranslation } from '../../contexts/TranslationProvider';

interface SimpleStepsProps {
    sectionData?: HomeSection;
    data?: any[];
    readyToStartData?: HomeSection;
    onStartClick?: () => void;
    isLoading?: boolean;
}

const SimpleSteps: React.FC<SimpleStepsProps> = ({ sectionData, data: propData, readyToStartData, onStartClick, isLoading: propLoading }) => {
    const { language, dir } = useLanguage();
    const { __ } = useTranslation();
    const { heroContent, isLoading: isSettingsLoading } = useSettings();
    const [steps, setSteps] = useState<any[]>([]);
    const [isLoading, setIsLoading] = useState(propLoading ?? true);

    useEffect(() => {
        if (propData !== undefined) {
            setSteps(propData);
            setIsLoading(false);
            return;
        }
        setIsLoading(true);
        // If propData is not provided, and getWorkingSteps is removed,
        // the component will remain in a loading state.
        // This implies that 'data' prop is now mandatory for this component to display steps.
    }, [propData, language]);


    const getTitle = (step: any) => {
        return step.title;
    };

    const getDescription = (step: any) => {
        return step.description;
    };

    const isComponentLoading = propLoading !== undefined ? propLoading : isLoading;

    if (isComponentLoading || isSettingsLoading) {
        return (
            <section className="bg-[#212832] py-20 text-white animate-pulse">
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="h-8 bg-gray-700 w-64 mx-auto rounded-sm mb-4"></div>
                </div>
            </section>
        );
    }

    if (steps.length === 0) return null;

    const langKey = language as keyof typeof sectionData.title;
    const sectionTitle = (sectionData?.title && sectionData.title[langKey]) ? sectionData.title[langKey] : "";
    const sectionDesc = (sectionData?.description && sectionData.description[langKey]) ? sectionData.description[langKey] : "";

    // The image shows a dark section #222933 approx
    return (
        <section className="bg-[#212832] py-20 text-white relative">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

                {/* Section Title */}
                {(sectionTitle || sectionDesc) && (
                    <div className="text-center mb-16 relative z-10">
                        {sectionTitle && <h2 className="text-3xl sm:text-4xl font-black mb-4 tracking-tight">{sectionTitle}</h2>}
                        {sectionDesc && <p className="text-gray-400 max-w-2xl mx-auto text-lg">{sectionDesc}</p>}
                    </div>
                )}

                {/* Horizontal Timeline Layout */}
                <div className="relative mb-8 pt-4 px-4">
                    {/* Golden connecting line */}
                    <div className="hidden md:block absolute top-[43px] left-[10%] right-[10%] h-[2px] bg-[#deb853] z-0 opacity-80"></div>

                    <div className="grid grid-cols-1 md:grid-cols-4 gap-x-8 gap-y-12 relative z-10 w-full">
                        {steps.map((step) => (
                            <div key={step.id} className="flex flex-col items-center text-center">
                                {/* Number Circle */}
                                <div className="w-[52px] h-[52px] bg-[#313c4e] rounded-full flex items-center justify-center text-[#0ee896] font-bold text-xl mb-6 shadow-md z-10 transition-transform hover:scale-105 mx-auto">
                                    {step.step_number}
                                </div>

                                {/* Texts */}
                                <h3 className="text-[1.35rem] font-bold mb-3 text-white tracking-wide">{getTitle(step)}</h3>
                                <p className="text-gray-400 text-[0.95rem] leading-7 px-2">
                                    {getDescription(step)}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Integration of Ready to Start */}
                {(!isComponentLoading && !isSettingsLoading && (readyToStartData || onStartClick)) && (
                    <div className="pt-10 pb-4 text-center mt-4">
                        <button
                            onClick={onStartClick}
                            className="inline-block px-12 py-4 text-xl font-bold rounded-sm text-white bg-[#5f4cee] hover:bg-[#4b3aca] transition-colors shadow-lg shadow-[#5f4cee]/20"
                        >
                            {heroContent?.cta_text?.[language] || heroContent?.cta_text?.['en'] || "Start a Fundraiser Now"}
                        </button>

                        <div className="flex flex-col items-center justify-center gap-3 mt-8">
                            <div className="flex items-center gap-1.5 text-[#0ee896]">
                                <Star fill="currentColor" strokeWidth={0} size={22} />
                                <Star fill="currentColor" strokeWidth={0} size={22} />
                                <Star fill="currentColor" strokeWidth={0} size={22} />
                                <Star fill="currentColor" strokeWidth={0} size={22} />
                                <Star fill="currentColor" strokeWidth={0} size={22} />
                            </div>
                            <div className="absolute top-4 start-4 bg-white/90 backdrop-blur-sm text-brand-600 text-xs font-bold px-3 py-1.5 rounded-xl dark:bg-gray-800/90 dark:text-brand-400">
                                {readyToStartData?.extra_data?.total_creators_text?.[language as 'en' | 'ar' | 'ku'] || ""}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
};

export default SimpleSteps;
