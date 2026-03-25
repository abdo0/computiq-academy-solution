import React, { useState, useEffect } from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { Star } from 'lucide-react';
import { dataService } from '../../services/dataService';
import { Testimonial, HomeSection } from '../../types';
import { useTranslation } from '../../contexts/TranslationProvider';

interface ReviewsProps {
    sectionData?: HomeSection;
    data?: Testimonial[];
    isLoading?: boolean;
}

const Reviews: React.FC<ReviewsProps> = ({ sectionData, data: propData, isLoading: propLoading }) => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const [reviews, setReviews] = useState<Testimonial[]>([]);
    const [isLoading, setIsLoading] = useState(propLoading ?? true);

    useEffect(() => {
        if (propData !== undefined) {
            setReviews(propData);
            setIsLoading(false);
            return;
        }
        setIsLoading(true);
        dataService.getTestimonials().then(data => {
            setReviews(data);
            setIsLoading(false);
        });
    }, [propData, language]);


    const getName = (rev: Testimonial) => {
        if (language === 'en' && rev.nameEn) return rev.nameEn;
        if (language === 'ku' && rev.nameKu) return rev.nameKu;
        return rev.name;
    };

    const getComment = (rev: Testimonial) => {
        if (language === 'en' && rev.commentEn) return rev.commentEn;
        if (language === 'ku' && rev.commentKu) return rev.commentKu;
        return rev.comment;
    };

    const isComponentLoading = propLoading !== undefined ? propLoading : isLoading;

    if (isComponentLoading) {
        return (
            <section className="py-20 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 animate-pulse">
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="h-8 bg-gray-200 dark:bg-gray-700 w-64 mx-auto rounded-sm mb-4"></div>
                </div>
            </section>
        );
    }

    if (reviews.length === 0) return null;

    // Calculate average rating
    const avgRating = reviews.length > 0
        ? (reviews.reduce((acc, rev) => acc + (rev.rating || 5), 0) / reviews.length).toFixed(1)
        : "5.0";

    const langKey = language as keyof typeof sectionData.title;
    const sectionTitle = (sectionData?.title && sectionData.title[langKey]) ? sectionData.title[langKey] : __('Reviews title', "Reviews That Inspire Us");
    const sectionDesc = (sectionData?.description && sectionData.description[langKey]) ? sectionData.description[langKey] : __('Reviews desc', "Read stories from people who raised help through our platform.");

    return (
        <section className="py-20 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 transition-colors duration-200">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 transition-colors">
                        {sectionTitle}
                    </h2>
                    <p className="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto transition-colors">
                        {sectionDesc}
                    </p>
                </div>

                <div className="flex flex-col lg:flex-row gap-12 items-center lg:items-start lg:rtl:flex-row-reverse">

                    {/* Overall Rating side */}
                    <div className="w-full lg:w-1/4 text-center lg:text-start lg:rtl:text-right flex flex-col items-center lg:items-start lg:rtl:items-end">
                        <div className="flex gap-2 items-center justify-center lg:justify-start lg:rtl:justify-end mb-4">
                            <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center font-bold text-blue-600 dark:text-blue-400 text-xl transition-colors">G</div>
                            <div className="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center transition-colors">
                                <Star className="text-emerald-600 dark:text-emerald-400 fill-emerald-600 dark:fill-emerald-400 block transition-colors" size={20} />
                            </div>
                        </div>
                        <div className="flex gap-1 mb-2">
                            {[...Array(Math.round(parseFloat(avgRating)))].map((_, i) => (
                                <Star key={i} className="text-yellow-400 fill-yellow-400" size={24} />
                            ))}
                        </div>
                        <div className="text-sm text-gray-700 dark:text-gray-300 font-medium transition-colors">{avgRating}/5 TrustScore</div>
                        <div className="text-xs text-gray-500 dark:text-gray-400 mt-1 transition-colors">{__('Reviews stats pre', 'Based on')} {reviews.length} {__('Reviews stats post', 'reviews')}</div>
                    </div>

                    {/* Cards side */}
                    <div className="w-full lg:w-3/4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        {reviews.slice(0, 3).map((rev) => (
                            <div key={rev.id} className="bg-white dark:bg-gray-900 p-6 rounded-sm shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col transition-colors duration-200">
                                <div className="flex gap-1 mb-4">
                                    {[...Array(rev.rating || 5)].map((_, i) => (
                                        <Star key={i} className="text-yellow-400 fill-yellow-400" size={16} />
                                    ))}
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed italic mb-6 flex-1 transition-colors">
                                    "{getComment(rev)}"
                                </p>
                                <div className="text-sm font-semibold text-gray-800 dark:text-gray-200 transition-colors">
                                    - {getName(rev)}
                                </div>
                            </div>
                        ))}
                    </div>

                </div>
            </div>
        </section>
    );
};

export default Reviews;
