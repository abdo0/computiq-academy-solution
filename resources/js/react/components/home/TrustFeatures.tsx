import React, { useState, useEffect } from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { ShieldCheck, ArrowRightLeft, BadgePercent, HeartHandshake } from 'lucide-react';
import { dataService } from '../../services/dataService';
import { TrustFeature, HomeSection } from '../../types';
import { useTranslation } from '../../contexts/TranslationProvider';

interface TrustFeaturesProps {
    sectionData?: HomeSection;
    data?: TrustFeature[];
    isLoading?: boolean;
}

const TrustFeatures: React.FC<TrustFeaturesProps> = ({ sectionData, data: propData, isLoading: propLoading }) => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const siteName = __('App name', "Us");
    const [features, setFeatures] = useState<TrustFeature[]>([]);
    const [isLoading, setIsLoading] = useState(propLoading ?? true);

    useEffect(() => {
        // If parent already provides data via props, use it directly
        if (propData !== undefined) {
            setFeatures(propData);
            setIsLoading(false);
            return;
        }
        // Otherwise fall back to independent fetch (e.g. when used outside Home.tsx)
        setIsLoading(true);
        dataService.getTrustFeatures().then(data => {
            setFeatures(data);
            setIsLoading(false);
        });
    }, [propData, language]);


    const getTitle = (feat: TrustFeature) => {
        return feat.title;
    };

    const getDescription = (feat: TrustFeature) => {
        return feat.description;
    };

    const getIcon = (iconName?: string) => {
        const className = "w-8 h-8 text-emerald-500 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors";
        switch (iconName) {
            case 'ShieldCheck': return <ShieldCheck className={className} />;
            case 'ArrowRightLeft': return <ArrowRightLeft className={className} />;
            case 'BadgePercent': return <BadgePercent className={className} />;
            case 'HeartHandshake': return <HeartHandshake className={className} />;
            default: return <ShieldCheck className={className} />;
        }
    };

    const isComponentLoading = propLoading !== undefined ? propLoading : isLoading;

    if (isComponentLoading) {
        return (
            <section className="py-24 bg-white dark:bg-gray-900 animate-pulse transition-colors duration-200">
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div className="h-10 bg-gray-200 dark:bg-gray-700 w-64 mx-auto rounded-sm mb-4"></div>
                    <div className="h-4 bg-gray-200 dark:bg-gray-700 w-full max-w-2xl mx-auto rounded-sm mb-16"></div>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {[1, 2, 3, 4].map(idx => (
                            <div key={idx} className="bg-white dark:bg-gray-800 p-8 rounded-sm border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                                <div className="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full mb-6"></div>
                                <div className="h-6 bg-gray-200 dark:bg-gray-700 w-3/4 rounded-sm mb-3"></div>
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 w-full rounded-sm"></div>
                                <div className="h-4 bg-gray-200 dark:bg-gray-700 w-4/5 rounded-sm mt-2"></div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>
        );
    }

    if (features.length === 0) return null;

    const langKey = language as keyof typeof sectionData.title;
    const sectionTitle = (sectionData?.title && sectionData.title[langKey]) ? sectionData.title[langKey] : "";
    const sectionDesc = (sectionData?.description && sectionData.description[langKey]) ? sectionData.description[langKey] : "";

    return (
        <section className="py-24 bg-white dark:bg-gray-900 transition-colors duration-200">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 transition-colors">
                    {sectionTitle}
                </h2>
                <p className="text-gray-600 dark:text-gray-300 mb-16 max-w-2xl mx-auto transition-colors">
                    {sectionDesc}
                </p>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {features.map((feature) => (
                        <div key={feature.id} className="group bg-white dark:bg-gray-800 p-8 rounded-sm border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center flex flex-col items-center">
                            <div className="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-all">
                                {getIcon(feature.icon)}
                            </div>
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-3 transition-colors">{getTitle(feature)}</h3>
                            <p className="text-gray-500 dark:text-gray-400 text-sm leading-relaxed transition-colors">{getDescription(feature)}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default TrustFeatures;
