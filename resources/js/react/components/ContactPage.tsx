import React, { useState, useEffect } from 'react';
import { Mail, Phone, MapPin, Send, CheckCircle } from 'lucide-react';
import { useLanguage } from '../contexts/LanguageContext';
import { useSettings } from '../contexts/SettingsContext';
import CustomSelect from './CustomSelect';
import { dataService } from '../services/dataService';
import { toast } from 'react-toastify';
import { useTranslation } from '../contexts/TranslationProvider';
import Turnstile from './common/Turnstile';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const ContactPageSkeleton: React.FC = () => (
    <div className="bg-gray-50 dark:bg-gray-900 min-h-screen pb-20 animate-pulse">
        <div className="bg-brand-900/50 py-16">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
                <div className="h-10 bg-white/20 rounded-sm w-48 mb-4"></div>
                <div className="h-4 bg-white/20 rounded-sm w-64 mt-4"></div>
            </div>
        </div>

        <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
            <div className="grid lg:grid-cols-3 gap-8">
                {/* Contact Info Skeleton */}
                <div className="lg:col-span-1 space-y-6">
                    <div className="bg-white dark:bg-gray-800 rounded-sm shadow-lg p-8 border border-gray-100 dark:border-gray-700">
                        <div className="h-6 bg-gray-200 dark:bg-gray-700 w-32 mb-6"></div>
                        <div className="space-y-6">
                            {[1, 2, 3].map((i) => (
                                <div key={i} className="flex items-start gap-4">
                                    <div className="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-sm"></div>
                                    <div className="space-y-2 flex-1">
                                        <div className="h-4 bg-gray-200 dark:bg-gray-700 w-24"></div>
                                        <div className="h-4 bg-gray-200 dark:bg-gray-700 w-32"></div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Contact Form Skeleton */}
                <div className="lg:col-span-2">
                    <div className="bg-white dark:bg-gray-800 rounded-sm shadow-lg p-8 border border-gray-100 dark:border-gray-700 h-[500px]"></div>
                </div>
            </div>
        </div>
    </div>
);

const ContactPage: React.FC = () => {
    const [formStatus, setFormStatus] = useState<'idle' | 'submitting' | 'success'>('idle');
    const [subject, setSubject] = useState('');
    const [formData, setFormData] = useState({ name: '', email: '', message: '' });
    const { language } = useLanguage();
    const { __ } = useTranslation();
    const { settings } = useSettings();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const [pageData, setPageData] = useState<any>(() => initialBootstrap?.pageInfo || null);
    const [loading, setLoading] = useState(() => !initialBootstrap?.pageInfo);
    const [turnstileToken, setTurnstileToken] = useState('');

    useEffect(() => {
        if (initialBootstrap?.pageInfo) {
            setPageData(initialBootstrap.pageInfo);
            setLoading(false);
            return;
        }

        fetchPageData();
    }, [initialBootstrap]);

    const fetchPageData = async () => {
        setLoading(true);
        try {
            const data = await dataService.getDynamicPage('contact-us');
            setPageData(data);
        } catch (error) {
            console.error('Failed to load contact page data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setFormData(prev => ({ ...prev, [e.target.id]: e.target.value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setFormStatus('submitting');

        try {
            await dataService.submitContactForm({ ...formData, subject, 'cf-turnstile-response': turnstileToken });
            setFormStatus('success');
            toast.success(__('Form success'));
            // Reset after 3 seconds
            setTimeout(() => {
                setFormStatus('idle');
                setSubject('');
                setFormData({ name: '', email: '', message: '' });
            }, 3000);
        } catch (error) {
            toast.error("Failed to send message");
            setFormStatus('idle');
        }
    };

    if (loading) {
        return <ContactPageSkeleton />;
    }

    const title = pageData?.title?.[language] || pageData?.title?.['en'] || __('Contact title');
    const content = pageData?.content?.[language] || pageData?.content?.['en'];

    const subjectOptions = [
        { value: 'general', label: language === 'ar' ? 'استفسار عام' : language === 'ku' ? 'پرسیاری گشتی' : 'General Inquiry' },
        { value: 'course_issue', label: language === 'ar' ? 'مشكلة في الدورة' : language === 'ku' ? 'کێشە لە خول' : 'Course Issue' },
        { value: 'sponsorship', label: language === 'ar' ? 'طلب كفالة' : language === 'ku' ? 'داواکاری سپۆنسەری' : 'Sponsorship Request' },
    ];

    const address = settings.address || '';

    return (
        <div className="bg-gray-50 dark:bg-gray-900 min-h-screen pb-20">
            {/* Page Header */}
            <div className="bg-brand-900 py-16">
                <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl font-extrabold text-white">{title}</h1>
                    <p className="mt-4 text-brand-100">{pageData?.meta_description?.[language] || pageData?.meta_description?.['en'] || __('Contact subtitle')}</p>
                </div>
            </div>

            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
                <div className="grid lg:grid-cols-3 gap-8">
                    {/* Contact Info Cards & dynamic content */}
                    <div className="lg:col-span-1 space-y-6">
                        <div className="bg-white dark:bg-gray-800 rounded-sm shadow-lg p-8 border border-gray-100 dark:border-gray-700">
                            {content && (
                                <div
                                    className="prose prose-sm max-w-none text-gray-800 dark:text-gray-200 mb-8 border-b border-gray-100 dark:border-gray-700 pb-6 prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-brand-600 dark:prose-a:text-brand-400"
                                    dangerouslySetInnerHTML={{ __html: content }}
                                />
                            )}

                            <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6 border-b border-transparent pb-4">{__('Contact info')}</h3>

                            <div className="space-y-6">
                                <div className="flex items-start gap-4">
                                    <div className="bg-brand-50 p-3 rounded-sm text-brand-600">
                                        <Phone size={24} />
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500 dark:text-gray-400 font-medium">{__('Nav contact')}</p>
                                        {settings.contactPhone?.map((phone: string) => (
                                            <p key={phone} className="text-gray-900 dark:text-white font-semibold dir-ltr text-end">{phone}</p>
                                        ))}
                                    </div>
                                </div>

                                <div className="flex items-start gap-4">
                                    <div className="bg-brand-50 p-3 rounded-sm text-brand-600">
                                        <Mail size={24} />
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500 dark:text-gray-400 font-medium">{__('Form email')}</p>
                                        <p className="text-gray-900 dark:text-white font-semibold">{settings.contactEmail}</p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-4">
                                    <div className="bg-brand-50 p-3 rounded-sm text-brand-600">
                                        <MapPin size={24} />
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500 dark:text-gray-400 font-medium">Address</p>
                                        <p className="text-gray-900 dark:text-white font-semibold">{address}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Contact Form */}
                    <div className="lg:col-span-2">
                        <div className="bg-white dark:bg-gray-800 rounded-sm shadow-lg p-8 border border-gray-100 dark:border-gray-700 h-full">
                            <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">{__('Contact form title')}</h3>

                            {formStatus === 'success' ? (
                                <div className="h-64 flex flex-col items-center justify-center text-center animate-fade-in-up">
                                    <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                                        <CheckCircle className="w-10 h-10 text-green-600" />
                                    </div>
                                    <h4 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">{__('Form success')}</h4>
                                </div>
                            ) : (
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Form name')}</label>
                                            <input
                                                required
                                                type="text"
                                                id="name"
                                                value={formData.name}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-3 rounded-sm border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-600 transition-all outline-none"
                                            />
                                        </div>
                                        <div>
                                            <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Form email')}</label>
                                            <input
                                                required
                                                type="email"
                                                id="email"
                                                value={formData.email}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-3 rounded-sm border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-600 transition-all outline-none"
                                                placeholder="example@email.com"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <CustomSelect
                                            label={__('Form subject')}
                                            options={subjectOptions}
                                            value={subject}
                                            onChange={setSubject}
                                            placeholder={__('Form subject')}
                                        />
                                    </div>

                                    <div>
                                        <label htmlFor="message" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Form message')}</label>
                                        <textarea
                                            required
                                            id="message"
                                            rows={5}
                                            value={formData.message}
                                            onChange={handleInputChange}
                                            className="w-full px-4 py-3 rounded-sm border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-600 transition-all outline-none"
                                        ></textarea>
                                    </div>

                                    <Turnstile onVerify={setTurnstileToken} onExpire={() => setTurnstileToken('')} />

                                    <button
                                        type="submit"
                                        disabled={formStatus === 'submitting'}
                                        className="w-full bg-brand-600 text-white font-bold py-4 rounded-sm hover:bg-brand-700 transition-colors shadow-lg dark:shadow-none flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                                    >
                                        {formStatus === 'submitting' ? (
                                            __('Form sending')
                                        ) : (
                                            <>
                                                {__('Form btn')} <Send size={18} />
                                            </>
                                        )}
                                    </button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ContactPage;
