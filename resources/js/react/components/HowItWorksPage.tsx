import React from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Search, MousePointerClick, CreditCard, Activity, ArrowRight, ShieldCheck, Globe, Clock, Target, Heart, ArrowLeft } from 'lucide-react';
import AppLink from './common/AppLink';
import { useTranslation } from '../contexts/TranslationProvider';

const HowItWorksPage: React.FC = () => {
  const { dir } = useLanguage();
  const { __ } = useTranslation();

  const isRtl = dir === 'rtl';

  const steps = [
    {
      icon: Search,
      title: __('Hiw step1 title') || 'Browse',
      desc: __('Hiw step1 desc') || 'Explore verified courses across different categories.',
      color: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    },
    {
      icon: <Target className="w-8 h-8 md:w-10 md:h-10 text-white" />,
      title: __('Hiw step2 title') || 'Select a Course',
      desc: __('Hiw step2 desc') || 'Choose a course that resonates with you.',
    },
    {
      icon: <Heart className="w-8 h-8 md:w-10 md:h-10 text-white" />,
      title: __('Hiw step3 title') || 'Enroll & Checkout',
      desc: __('Hiw step3 desc') || 'Complete your enrollment through our secure payment gateways.',
      color: 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    },
    {
      icon: Activity,
      title: __('Hiw step4 title') || 'Track Impact',
      desc: __('Hiw step4 desc') || 'See how your contribution makes a difference.',
      color: 'bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400',
    }
  ];

  const features = [
    { icon: ShieldCheck, title: __('Feature secure') || '100% Secure' },
    { icon: Globe, title: __('Feature global payment') || 'Local & International Payment' },
    { icon: Clock, title: __('Feature support') || '24/7 Support' },
    { icon: Target, title: __('Feature impact') || 'Global Impact' },
  ];

  return (
    <div className="bg-white dark:bg-gray-900 min-h-screen">
      {/* Hero Section */}
      <div className="bg-brand-900 py-20 relative overflow-hidden">
        <div className="absolute inset-0 opacity-10">
          <div className="w-full h-full bg-gradient-to-br from-brand-500/40 via-brand-700/40 to-brand-900/60" />
          {/* Add some abstract shapes to modernise the hero */}
          <div className="absolute top-0 -left-1/4 w-1/2 h-full bg-white/5 blur-3xl transform -rotate-12 rounded-full" />
          <div className="absolute bottom-0 -right-1/4 w-1/2 h-full bg-white/5 blur-3xl transform rotate-12 rounded-full" />
        </div>

        <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
          <h1 className="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white mb-6">
            {__('Hiw hero title') || 'How Computiq Academy Works'}
          </h1>
          <p className="text-xl text-brand-100 max-w-3xl mx-auto leading-relaxed">
            {__('Hiw hero subtitle') || 'A simple, transparent, and secure 4-step process to ensure your learning experience is seamless.'}
          </p>
        </div>
      </div>

      {/* Feature Highlights Bar */}
      <div className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            {features.map((feature, idx) => (
              <div key={idx} className="flex items-center justify-center gap-2 text-gray-600 dark:text-gray-300">
                <feature.icon className="w-5 h-5 text-brand-600 dark:text-brand-400" />
                <span className="font-medium text-sm sm:text-base">{feature.title}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Main Content Area */}
      <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
          {/* Left side: Steps text */}
          <div className="space-y-12">
            {steps.map((step, index) => (
              <div key={index} className="relative">
                {/* Connector Line */}
                {index !== steps.length - 1 && (
                  <div className={`absolute top-16 bottom-[-3rem] w-0.5 bg-gray-200 dark:bg-gray-700 ${isRtl ? 'right-8' : 'left-8'}`} />
                )}

                <div className="flex items-start group">
                  {/* Number/Icon bubble */}
                  <div className={`relative z-10 flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center ${step.color} shadow-lg transition-transform duration-300 group-hover:scale-110`}>
                    <step.icon className="w-8 h-8" />
                    <div className={`absolute -top-2 ${isRtl ? '-left-2' : '-right-2'} w-6 h-6 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center shadow-md font-bold text-gray-900 border border-gray-100 dark:border-gray-700 dark:text-white text-sm`}>
                      {index + 1}
                    </div>
                  </div>

                  {/* Text Content */}
                  <div className={`pt-2 ${isRtl ? 'pr-6' : 'pl-6'}`}>
                    <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                      {step.title}
                    </h3>
                    <p className="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                      {step.desc}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Right side: App Mockups */}
          <div className="relative">
            {/* Decorative Background Blob behind mockups */}
            <div className="absolute inset-0 bg-brand-100 dark:bg-brand-900/20 rounded-[3rem] transform rotate-3" />

            <div className="relative bg-white dark:bg-gray-800 rounded-md shadow-2xl border border-gray-100 dark:border-gray-700 p-2 transform -rotate-2 hover:rotate-0 transition-transform duration-500 overflow-hidden">
              {/* Fake browser/app top bar */}
              <div className="flex items-center gap-2 px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div className="w-3 h-3 rounded-full bg-red-400" />
                <div className="w-3 h-3 rounded-full bg-amber-400" />
                <div className="w-3 h-3 rounded-full bg-green-400" />
              </div>

              {/* Stylized Campaign Card Mockup embedded to build trust */}
              <div className="p-4 sm:p-6 bg-gray-50 dark:bg-gray-900/50">
                <div className="bg-white dark:bg-gray-800 rounded-md shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                  <div className="h-48 bg-gray-200 dark:bg-gray-700 relative overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                    <div className="absolute bottom-4 left-4 right-4 text-white">
                      <div className="h-6 w-3/4 bg-white/20 rounded backdrop-blur-sm mb-2" />
                      <div className="h-4 w-1/2 bg-white/20 rounded backdrop-blur-sm" />
                    </div>
                  </div>
                  <div className="p-5">
                    <div className="flex justify-between items-end mb-4">
                      <div className="space-y-2 flex-grow">
                        <div className="h-3 w-1/4 bg-brand-100 dark:bg-brand-900/40 rounded" />
                        <div className="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                          <div className="h-full bg-brand-500 w-[65%]" />
                        </div>
                      </div>
                      <div className={`${isRtl ? 'pr-4' : 'pl-4'} text-brand-600 dark:text-brand-400 font-bold`}>
                        65%
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div className="h-10 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/20 rounded-md flex items-center justify-center text-brand-600 dark:text-brand-400 font-medium">
                        {__('Modal amount') || 'Select Amount'}
                      </div>
                      <div className="h-10 bg-brand-600 rounded-md flex items-center justify-center text-white font-medium shadow-md shadow-brand-500/20">
                        {__('Hiw cta btn') || 'Donate Now'}
                      </div>
                    </div>
                  </div>
                </div>

                {/* Floating Trust Badge */}
                <div className={`absolute bottom-8 ${isRtl ? '-left-6' : '-right-6'} bg-white dark:bg-gray-800 p-4 rounded-md shadow-xl border border-gray-100 dark:border-gray-700 flex items-center gap-3 transform rotate-6 animate-pulse`}>
                  <div className="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                    <ShieldCheck className="w-6 h-6" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-gray-900 dark:text-white">{__('Hiw verified') || 'Verified'}</div>
                    <div className="text-xs text-gray-500 dark:text-gray-400">{__('Hiw platform secure') || 'Platform Secure'}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Call to Action Section */}
        <div className="mt-24 text-center bg-gray-50 dark:bg-gray-800/50 rounded-md p-8 sm:p-12 border border-gray-100 dark:border-gray-700">
          <h2 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            {__('Hiw cta title') || 'Ready to make a difference?'}
          </h2>
          <p className="text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
            {__('Ready desc') || "Join thousands of successful students who have enhanced their skills. Start your courses today and make a difference."}
          </p>
          <AppLink 
            to="/courses"
            className="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-8 py-4 rounded-sm font-bold shadow-lg shadow-brand-500/20 transition-transform duration-300 hover:-translate-y-1"
          >
            {__('Hiw cta btn') || 'Donate Now'}
            {dir === 'rtl' ? <ArrowLeft className="w-5 h-5" /> : <ArrowRight className="w-5 h-5" />}
          </AppLink>
        </div>

      </div>
    </div>
  );
};

export default HowItWorksPage;
