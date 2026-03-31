import React from 'react';
import { useSettings } from '../../contexts/SettingsContext';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';

interface FullScreenLoaderProps {
    label?: string;
    progress?: number;
    steps?: Array<{
        key: string;
        label: string;
        status: 'pending' | 'active' | 'done';
    }>;
}

const FullScreenLoader: React.FC<FullScreenLoaderProps> = ({ label, progress = 0, steps = [] }) => {
    const { settings } = useSettings();
    const { theme } = useTheme();
    const { language } = useLanguage();

    const fallbackLogo =
        language === 'ar'
            ? (theme === 'dark' ? '/images/PNG/image_1124.png' : '/images/PNG/2T.png')
            : (theme === 'dark' ? '/images/SVG/MainLogo_01_PNG.svg' : '/images/SVG/MainLogo_03_PNG.svg');

    const logoSrc = settings.logoUrl || fallbackLogo;
    const logoAlt = settings.siteName || 'Computiq Academy';

    return (
        <div className="fixed inset-0 z-[10000] flex items-center justify-center bg-white dark:bg-gray-900">
            <div className="flex w-full max-w-sm flex-col items-center gap-8 px-6 text-center animate-fade-in">
                <img
                    src={logoSrc}
                    alt={logoAlt}
                    className="h-14 sm:h-16 w-auto max-w-[240px] object-contain"
                />

                <div className="w-full">
                    <div className="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            className="h-full rounded-full bg-gradient-to-r from-brand-500 via-brand-600 to-brand-700 transition-[width] duration-300 ease-out opacity-95"
                            style={{ width: `${Math.min(Math.max(progress, 0), 100)}%` }}
                        />
                    </div>
                    <div className="mt-4 flex items-center justify-between gap-4 text-sm">
                        <p className="font-medium text-gray-500 dark:text-gray-400">{label || 'Loading...'}</p>
                        <span className="font-semibold text-brand-600 dark:text-brand-400">{Math.min(Math.max(progress, 0), 100)}%</span>
                    </div>
                    {steps.length > 0 ? (
                        <div className="mt-5 space-y-2 text-start">
                            {steps.map((step) => (
                                <div key={step.key} className="flex items-center gap-3 text-xs">
                                    <span
                                        className={`h-2 w-2 rounded-full ${
                                            step.status === 'done'
                                                ? 'bg-green-500'
                                                : step.status === 'active'
                                                    ? 'bg-brand-600 dark:bg-brand-400'
                                                    : 'bg-gray-300 dark:bg-gray-700'
                                        }`}
                                    />
                                    <span
                                        className={
                                            step.status === 'active'
                                                ? 'font-semibold text-gray-700 dark:text-gray-200'
                                                : step.status === 'done'
                                                    ? 'text-gray-500 dark:text-gray-400'
                                                    : 'text-gray-400 dark:text-gray-500'
                                        }
                                    >
                                        {step.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    ) : null}
                </div>
            </div>
        </div>
    );
};

export default FullScreenLoader;
