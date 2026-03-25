import React from 'react';
import { Turnstile as CloudflareTurnstile } from '@marsidev/react-turnstile';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';

interface TurnstileProps {
    onVerify: (token: string) => void;
    onExpire?: () => void;
    onError?: () => void;
    siteKey?: string;
}

const Turnstile: React.FC<TurnstileProps> = ({ onVerify, onExpire, onError, siteKey }) => {
    const { theme } = useTheme();
    const { language } = useLanguage();
    
    const resolvedSiteKey = siteKey || import.meta.env.VITE_TURNSTILE_SITE_KEY || '';

    if (!resolvedSiteKey) {
        return null; // Don't render anything if there's no site key
    }

    return (
        <div className="flex justify-center my-4">
            <CloudflareTurnstile
                siteKey={resolvedSiteKey}
                options={{
                    theme: theme === 'dark' ? 'dark' : 'light',
                    language: language === 'ku' ? 'ar' : language, // CF doesn't support ku
                    size: 'normal'
                }}
                onSuccess={onVerify}
                onError={onError}
                onExpire={onExpire}
            />
        </div>
    );
};

export default Turnstile;
