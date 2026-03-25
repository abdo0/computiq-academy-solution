import React from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { HomeSection } from '../../types';
import { useTranslation } from '../../contexts/TranslationProvider';
import AppLink from '../common/AppLink';

interface FeaturedTopicsProps {
    sectionData?: HomeSection;
}

const FeaturedTopics: React.FC<FeaturedTopicsProps> = ({ sectionData }) => {
    const { language } = useLanguage();
    const { __ } = useTranslation();

    const topics = [
        {
            image: "https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?q=80&w=600&auto=format&fit=crop",
            tag: __('Topic relief tag'),
            title: __('Topic relief title'),
            description: __('Topic relief desc'),
            link: "/courses?category=emergency"
        },
        {
            image: "https://images.unsplash.com/photo-1593113568393-559d18fa8d1b?q=80&w=600&auto=format&fit=crop",
            tag: __('Topic ramadan tag'),
            title: __('Topic ramadan title'),
            description: __('Topic ramadan desc'),
            link: "/courses?category=community"
        },
        {
            image: "https://images.unsplash.com/photo-1532629345422-7515f3d16bb0?q=80&w=600&auto=format&fit=crop",
            tag: __('Topic emergency tag'),
            title: __('Topic emergency title'),
            description: __('Topic emergency desc'),
            link: "/courses?category=medical"
        }
    ];

    const langKey = language as keyof typeof sectionData.title;
    const sectionTitle = (sectionData?.title && sectionData.title[langKey]) ? sectionData.title[langKey] : __('Featured title');
    const sectionDesc = (sectionData?.description && sectionData.description[langKey]) ? sectionData.description[langKey] : __('Featured desc');

    return (
        <section className="py-20 bg-white dark:bg-gray-900 transition-colors duration-200">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 transition-colors">
                    {sectionTitle}
                </h2>
                <p className="text-gray-600 dark:text-gray-300 mb-12 max-w-2xl mx-auto transition-colors">
                    {sectionDesc}
                </p>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8 rtl:text-right ltr:text-left">
                    {topics.map((topic, idx) => (
                        <div key={idx} className="group flex flex-col bg-white dark:bg-gray-800 rounded-sm overflow-hidden shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-700 transition-all duration-300 h-full">
                            <div className="h-48 overflow-hidden relative">
                                <img
                                    src={topic.image}
                                    alt={topic.title}
                                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div className="absolute top-4 rtl:right-4 ltr:left-4">
                                    <span className="bg-white/90 dark:bg-gray-900/90 backdrop-blur text-gray-800 dark:text-gray-200 text-xs font-bold px-3 py-1 rounded-sm transition-colors">
                                        {topic.tag}
                                    </span>
                                </div>
                            </div>
                            <div className="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">{topic.title}</h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6 transition-colors">{topic.description}</p>
                                </div>
                                <div>
                                    <AppLink to={topic.link} className="inline-block text-sm font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 px-4 py-2 rounded-sm hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors">
                                        {__('General read story')}
                                    </AppLink>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
};

export default FeaturedTopics;
