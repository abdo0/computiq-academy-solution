import React, { useState } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { Search, Compass, MapPin } from 'lucide-react';
import Seo from './Seo';

const PathsPage: React.FC = () => {
    const { dir } = useLanguage();
    const { __ } = useTranslation();
    
    const [searchQuery, setSearchQuery] = useState('');

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // Future logic
    };

    return (
        <div className="bg-[#fcfdfd] dark:bg-slate-950 min-h-screen py-10 pb-24">
            <Seo 
                title={__('Learning paths')} 
                description={__('Paths description')}
            />

            <div className="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
                
                {/* Header Section */}
                <div className="mb-12 text-center flex flex-col items-center justify-center gap-6">
                    <div>
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 text-sm font-bold mb-4">
                            <Compass className="w-4 h-4" />
                            <span>{__('Our paths')}</span>
                        </div>
                        <h1 className="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                            {__('Explore paths')}
                        </h1>
                        <p className="mt-4 text-gray-600 dark:text-gray-400 text-lg max-w-2xl mx-auto">
                            {__('Explore paths description')}
                        </p>
                    </div>

                    {/* Search Bar */}
                    <form onSubmit={handleSearchSubmit} className="relative w-full max-w-lg group">
                        <div className="absolute inset-y-0 ltr:left-0 rtl:right-0 flex items-center ltr:pl-4 rtl:pr-4 pointer-events-none">
                            <Search className="w-5 h-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" />
                        </div>
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="block w-full ltr:pl-11 ltr:pr-4 rtl:pr-11 rtl:pl-4 py-3.5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl leading-5 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-shadow shadow-sm"
                            placeholder={__('Search paths')}
                        />
                        <button type="submit" className="hidden"></button>
                    </form>
                </div>

                {/* Empty State / Coming Soon */}
                <div className="bg-white dark:bg-slate-900 rounded-2xl p-12 text-center border border-gray-100 dark:border-slate-800 shadow-sm flex flex-col items-center justify-center min-h-[400px]">
                    <div className="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                        <MapPin className="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {__('Paths coming soon')}
                    </h3>
                    <p className="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        {__('Paths coming soon desc')}
                    </p>
                </div>

            </div>
        </div>
    );
};

export default PathsPage;
