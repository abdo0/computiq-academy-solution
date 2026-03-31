import React from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { Heart } from 'lucide-react';
import { useAppNavigate } from '../../hooks/useAppNavigate';
import { HomeSection } from '../../types';
import { useTranslation } from '../../contexts/TranslationProvider';

interface BottomStatsProps {
    sectionData?: HomeSection;
    isLoading?: boolean;
}

const BottomStats: React.FC<BottomStatsProps> = ({ sectionData, isLoading = false }) => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();

    const langKey = language as 'en' | 'ar' | 'ku';
    const sectionTitle = sectionData?.title?.[langKey] || sectionData?.title?.['en'] || __('Trusted title', "Trusted by thousands of people & charities");
    const sectionDesc = sectionData?.description?.[langKey] || sectionData?.description?.['en'] || __('Trusted desc', "Join our community of students and instructors who are making the world a better place, one course at a time.");

    const stats = [
        { value: sectionData?.extra_data?.stat_1_val || "600K+", label: sectionData?.extra_data?.stat_1_label?.[langKey] || sectionData?.extra_data?.stat_1_label?.['en'] || "Students" },
        { value: sectionData?.extra_data?.stat_2_val || "12M+", label: sectionData?.extra_data?.stat_2_label?.[langKey] || sectionData?.extra_data?.stat_2_label?.['en'] || "Revenue" },
        { value: sectionData?.extra_data?.stat_3_val || "1500+", label: sectionData?.extra_data?.stat_3_label?.[langKey] || sectionData?.extra_data?.stat_3_label?.['en'] || "Courses" },
        { value: sectionData?.extra_data?.stat_4_val || "150+", label: sectionData?.extra_data?.stat_4_label?.[langKey] || sectionData?.extra_data?.stat_4_label?.['en'] || "Countries" }
    ];

    return (
        <section className="bg-[#10b981] dark:bg-emerald-800 py-12 md:py-16 text-white relative overflow-hidden transition-colors duration-200">

            {/* Background decorations */}
            <div className="absolute top-0 right-0 -mt-10 -mr-10 opacity-10 rtl:left-0 rtl:right-auto rtl:-ml-10 rtl:-mr-0">
                <Heart size={200} className="text-white fill-white transform rotate-12" />
            </div>
            <div className="absolute bottom-0 left-0 -mb-10 -ml-10 opacity-10 rtl:right-0 rtl:left-auto rtl:-mr-10 rtl:-ml-0">
                <Heart size={150} className="text-white fill-white transform -rotate-12" />
            </div>

            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div className="flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-8">

                    {/* Text and CTA Area */}
                    <div className="w-full lg:w-1/2 text-center lg:text-start lg:rtl:text-right">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4 transition-colors">
                            {sectionTitle}
                        </h2>
                        <p className="text-emerald-50 dark:text-emerald-100 text-lg mb-8 max-w-xl mx-auto lg:mx-0 transition-colors">
                            {sectionDesc}
                        </p>
                        <button
                            onClick={() => navigate('/courses')}
                            className="bg-white text-emerald-600 dark:bg-gray-900 dark:text-emerald-400 font-bold px-8 py-3 rounded-sm hover:bg-emerald-50 dark:hover:bg-gray-800 transition-colors shadow-lg hover:shadow-xl w-full sm:w-auto">
                            {__('Hero cta projects', "Start a Campaign Now")}
                        </button>
                    </div>

                    {/* Stats Grid */}
                    <div className="w-full lg:w-1/2">
                        <div className="grid grid-cols-2 gap-4 md:gap-6">
                            {isLoading ? (
                                <>
                                    {[1, 2, 3, 4].map(idx => (
                                        <div key={idx} className="bg-white/10 dark:bg-black/20 backdrop-blur-sm p-6 rounded-sm text-center border border-white/20 dark:border-white/10 animate-pulse">
                                            <div className="h-10 w-24 bg-white/30 rounded-sm mx-auto mb-2"></div>
                                            <div className="h-5 w-16 bg-white/20 rounded-sm mx-auto"></div>
                                        </div>
                                    ))}
                                </>
                            ) : (
                                stats.map((stat, idx) => (
                                    <div key={idx} className="bg-white/10 dark:bg-black/20 backdrop-blur-sm p-6 rounded-sm text-center border border-white/20 dark:border-white/10 transition-colors duration-200">
                                        <div className="text-3xl md:text-4xl font-extrabold mb-2 transition-colors">{stat.value}</div>
                                        <div className="text-emerald-50 dark:text-emerald-100 text-sm md:text-base font-medium transition-colors">{stat.label}</div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                </div>
            </div>
        </section>
    );
};

export default BottomStats;
