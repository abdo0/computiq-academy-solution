import React from 'react';
import { useTranslation } from '../../contexts/TranslationProvider';
import Logo from '../Logo';

interface FullScreenLoaderProps {
    label?: string;
    progress?: number;
    steps?: Array<{
        key: string;
        label: string;
        status: 'pending' | 'active' | 'done';
    }>;
}

const FullScreenLoader: React.FC<FullScreenLoaderProps> = ({ progress = 0 }) => {
    const { __ } = useTranslation();
    const normalizedProgress = Math.min(Math.max(progress, 0), 100);
    const progressBackground = `conic-gradient(from 180deg, #61d79a 0%, #2d8cff ${Math.max(normalizedProgress * 0.55, 8)}%, #1d66db ${normalizedProgress}%, rgba(148, 163, 184, 0.18) ${normalizedProgress}%, rgba(148, 163, 184, 0.18) 100%)`;

    return (
        <div className="fixed inset-0 z-[10000] flex items-center justify-center bg-white dark:bg-gray-900">
            <div className="flex w-full max-w-sm flex-col items-center gap-6 px-6 text-center animate-fade-in">
                <div
                    className="inline-flex rounded-[1.2rem] p-[3px] shadow-[0_20px_44px_rgba(31,123,242,0.18)]"
                    style={{ background: progressBackground }}
                >
                    <div
                        className="inline-flex items-center justify-center rounded-[1.02rem] border border-white/70 bg-white px-4 py-2.5 dark:border-gray-800/90 dark:bg-gray-900"
                    >
                        <Logo
                            className="gap-1"
                            textClassName="text-[1.2rem] sm:text-[1.35rem] font-black text-gray-900 dark:text-white leading-none"
                            imageClassName="h-7 sm:h-8 w-auto"
                        />
                    </div>
                </div>

                <p className="text-base font-semibold text-gray-600 dark:text-gray-300">
                    {__('Loading')}
                </p>
            </div>
        </div>
    );
};

export default FullScreenLoader;
