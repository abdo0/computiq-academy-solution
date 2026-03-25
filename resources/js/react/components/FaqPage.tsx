import React, { useState, useEffect } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { dataService } from '../services/dataService';
import { FaqItem } from '../types';
import { useTranslation } from '../contexts/TranslationProvider';

const FaqPage: React.FC = () => {
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const [openId, setOpenId] = useState<string | null>(null);
    const [faqs, setFaqs] = useState<FaqItem[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const loadFaqs = async () => {
            try {
                // Load SEO first (faqs endpoint already includes SEO)
                await dataService.loadPageSeo('faq');
                const data = await dataService.getFaqs();
                setFaqs(data);
            } catch (error) {
                console.error("Failed to load FAQs", error);
            } finally {
                setIsLoading(false);
            }
        };
        loadFaqs();
    }, []);

    const toggle = (id: string) => setOpenId(openId === id ? null : id);

    const getContent = (item: any) => {
        if (language === 'en') return { q: item.questionEn, a: item.answerEn };
        if (language === 'ku') return { q: item.questionKu, a: item.answerKu };
        return { q: item.question, a: item.answer };
    };

    const FaqSkeleton = () => (
        <div className="bg-white rounded-sm shadow-sm border border-gray-200 overflow-hidden p-5 animate-pulse">
            <div className="h-6 bg-gray-200 rounded-sm w-3/4"></div>
        </div>
    );

    return (
        <div className="bg-gray-50 min-h-screen py-12">
            <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-bold text-gray-900 mb-4">{__('Faq title')}</h1>
                </div>
                
                <div className="space-y-4">
                    {isLoading ? (
                        <>
                            <FaqSkeleton />
                            <FaqSkeleton />
                            <FaqSkeleton />
                            <FaqSkeleton />
                        </>
                    ) : (
                        faqs.map(item => {
                            const { q, a } = getContent(item);
                            const isOpen = openId === item.id;
                            return (
                                <div key={item.id} className="bg-white rounded-sm shadow-sm border border-gray-200 overflow-hidden">
                                    <button 
                                        onClick={() => toggle(item.id)}
                                        className="w-full flex justify-between items-center p-5 text-start focus:outline-none"
                                    >
                                        <span className="font-bold text-gray-900 text-lg">{q}</span>
                                        {isOpen ? <ChevronUp className="text-brand-500" /> : <ChevronDown className="text-gray-400" />}
                                    </button>
                                    {isOpen && (
                                        <div className="px-5 pb-5 text-gray-600 leading-relaxed border-t border-gray-100 pt-3 bg-gray-50/50">
                                            {a}
                                        </div>
                                    )}
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </div>
    );
};

export default FaqPage;