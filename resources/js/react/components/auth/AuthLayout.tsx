import React from 'react';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Autoplay, Pagination, EffectCards } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/effect-cards';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import { ShieldCheck, BookOpen, Users, Trophy } from 'lucide-react';
import AppLink from '../common/AppLink';
import Logo from '../Logo';

interface AuthLayoutProps {
    children: React.ReactNode;
    title: string;
    subtitle: string;
}

const AuthLayout: React.FC<AuthLayoutProps> = ({ children, title, subtitle }) => {
    const { __ } = useTranslation();
    const { language, dir } = useLanguage();

    const slides = [
        {
            id: 1,
            title: __('Learn from the best experts'),
            description: __('Join our platform and explore hundreds of verified courses delivered by top professionals in the tech industry.'),
            icon: <BookOpen className="w-12 h-12 text-white/90" />,
            color: 'from-blue-600 to-indigo-700'
        },
        {
            id: 2,
            title: __('Trusted & Certified'),
            description: __('Obtain recognizable certifications that boost your career and prove your skills in the competitive market.'),
            icon: <ShieldCheck className="w-12 h-12 text-white/90" />,
            color: 'from-brand-600 to-brand-800'
        },
        {
            id: 3,
            title: __('Thriving Community'),
            description: __('Be part of a growing community of thousands of ambitious learners sharing knowledge and building the future.'),
            icon: <Users className="w-12 h-12 text-white/90" />,
            color: 'from-purple-600 to-fuchsia-700'
        },
        {
            id: 4,
            title: __('Achieve Your Goals'),
            description: __('Track your progress, unlock achievements, and reach new milestones every single day.'),
            icon: <Trophy className="w-12 h-12 text-white/90" />,
            color: 'from-emerald-500 to-teal-700'
        }
    ];

    return (
        <div className={`min-h-screen flex ${dir === 'rtl' ? 'flex-row-reverse' : 'flex-row'} bg-gray-50 dark:bg-gray-900`}>

            {/* Left Side: Creative Swiper Panel (Hidden on Mobile) */}
            <div className="hidden lg:flex w-1/2 relative overflow-hidden bg-white dark:bg-gray-900 flex-col border-e border-gray-100 dark:border-gray-800">

                {/* Swiper Content */}
                <div className="flex-1 w-full flex items-center justify-center relative z-10 px-10 py-16 bg-gray-50/50 dark:bg-gray-900/50">
                    <div className="w-full max-w-lg">
                        <Swiper
                            modules={[Autoplay, Pagination, EffectCards]}
                            effect="cards"
                            cardsEffect={{
                                slideShadows: false,
                                perSlideOffset: 12,
                                perSlideRotate: 3,
                            }}
                            grabCursor={true}
                            autoplay={{ delay: 5000, disableOnInteraction: false }}
                            pagination={{
                                clickable: true,
                                bulletElement: 'span',
                                bulletClass: 'swiper-pagination-bullet !bg-gray-300 dark:!bg-gray-700 !w-3 !h-3 !transition-all duration-300',
                                bulletActiveClass: 'swiper-pagination-bullet-active !bg-brand-600 dark:!bg-brand-500 !w-8 !rounded-full'
                            }}
                            className="w-full !pb-20"
                            dir={dir}
                        >
                            {slides.map((slide) => (
                                <SwiperSlide key={slide.id}>
                                    <div className="p-8 sm:p-12 rounded-3xl bg-white dark:bg-gray-800 shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-gray-100 dark:border-gray-700 text-center flex flex-col items-center justify-center h-[420px]">
                                        <div className={`mb-8 w-24 h-24 rounded-full flex items-center justify-center bg-gradient-to-br ${slide.color} shadow-lg text-white`}>
                                            {slide.icon}
                                        </div>
                                        <h3 className="text-2xl font-bold mb-4 text-gray-900 dark:text-white leading-tight">
                                            {slide.title}
                                        </h3>
                                        <p className="text-gray-500 dark:text-gray-400 text-base leading-relaxed max-w-sm">
                                            {slide.description}
                                        </p>
                                    </div>
                                </SwiperSlide>
                            ))}
                        </Swiper>
                    </div>
                </div>

                {/* Footer Copy */}
                <div className="relative z-10 p-10 text-gray-400 dark:text-gray-500 text-sm font-medium">
                    &copy; {new Date().getFullYear()} Computiq Academy. {__('All rights reserved.')}
                </div>
            </div>

            {/* Right Side: Form Content Panel */}
            <div className="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 xl:p-24 relative">
                {/* Mobile Logo Setup */}
                <div className="lg:hidden absolute top-8 left-8 right-8 flex justify-between items-center">
                    <AppLink to="/">
                        <Logo />
                    </AppLink>
                </div>

                <div className="w-full max-w-md mx-auto relative z-10 animate-fade-in-up mt-12 lg:mt-0">
                    {/* Header */}
                    <div className="mb-10 text-center lg:text-start">
                        <h2 className="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight mb-2">
                            {title}
                        </h2>
                        <p className="text-gray-500 dark:text-gray-400 text-lg">
                            {subtitle}
                        </p>
                    </div>

                    {/* Dynamic Form Slot */}
                    <div className="bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        {children}
                    </div>
                </div>
            </div>

        </div>
    );
};

export default AuthLayout;
