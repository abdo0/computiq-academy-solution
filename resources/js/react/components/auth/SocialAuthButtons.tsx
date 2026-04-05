import React, { useState } from 'react';
import { Github } from 'lucide-react';
import { toast } from 'react-toastify';
import { userAuthService } from '../../services/dataService';
import { useTranslation } from '../../contexts/TranslationProvider';

const GoogleIcon: React.FC = () => (
    <svg viewBox="0 0 24 24" className="h-5 w-5" aria-hidden="true">
        <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.2-.9 2.2-1.9 2.9l3 2.3c1.8-1.6 2.8-4 2.8-6.8 0-.7-.1-1.5-.2-2.2H12Z" />
        <path fill="#34A853" d="M12 21c2.5 0 4.6-.8 6.1-2.2l-3-2.3c-.8.6-1.9 1-3.1 1-2.4 0-4.5-1.6-5.2-3.9l-3.1 2.4C5.2 19 8.3 21 12 21Z" />
        <path fill="#4A90E2" d="M6.8 13.6c-.2-.6-.3-1.1-.3-1.7s.1-1.2.3-1.7L3.7 7.8C3 9.1 2.6 10.5 2.6 12s.4 2.9 1.1 4.2l3.1-2.6Z" />
        <path fill="#FBBC05" d="M12 6.4c1.4 0 2.6.5 3.6 1.4l2.7-2.7C16.6 3.5 14.5 2.7 12 2.7c-3.7 0-6.8 2.1-8.3 5.1l3.1 2.4c.7-2.2 2.8-3.8 5.2-3.8Z" />
    </svg>
);

interface SocialAuthButtonsProps {
    redirectTo?: string;
    onSuccess?: () => void;
}

const SocialAuthButtons: React.FC<SocialAuthButtonsProps> = ({ redirectTo, onSuccess }) => {
    const { __ } = useTranslation();
    const [loadingProvider, setLoadingProvider] = useState<'google' | 'github' | null>(null);

    const handleSocialRedirect = async (provider: 'google' | 'github') => {
        setLoadingProvider(provider);

        // Open window synchronously to bypass Safari popup blocker
        const width = 500;
        const height = 600;
        const left = window.screenX + (window.outerWidth - width) / 2;
        const top = window.screenY + (window.outerHeight - height) / 2;
        
        const popup = window.open(
            '',
            'socialLoginPopup',
            `width=${width},height=${height},left=${left},top=${top},status=yes,scrollbars=yes`
        );

        try {
            const { url, error } = await userAuthService.getSocialRedirect(provider, redirectTo, true);

            if (url && popup) {
                popup.location.href = url;

                const handleMessage = (e: MessageEvent) => {
                    if (e.data === 'social_login_success') {
                        window.removeEventListener('message', handleMessage);
                        toast.success(__('Login successful'));
                        setLoadingProvider(null);
                        
                        if (onSuccess) {
                            onSuccess();
                        } else {
                            window.location.reload();
                        }
                    } else if (typeof e.data === 'string' && e.data.startsWith('social_login_error:')) {
                        window.removeEventListener('message', handleMessage);
                        toast.error(__('Social login failed.'));
                        setLoadingProvider(null);
                    }
                };

                window.addEventListener('message', handleMessage);

                const pollTimer = setInterval(() => {
                    if (popup && popup.closed) {
                        clearInterval(pollTimer);
                        window.removeEventListener('message', handleMessage);
                        setLoadingProvider(null);
                    }
                }, 500);

                return;
            }

            if (popup) popup.close();
            toast.error(error || __('Social login is currently unavailable.'));
        } catch (error) {
            if (popup) popup.close();
            toast.error(__('An error occurred'));
        } finally {
            setLoadingProvider(null);
        }
    };

    const sharedClassName = 'w-full flex items-center justify-center gap-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-100 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70';

    return (
        <div className="space-y-3">
            <button
                type="button"
                onClick={() => void handleSocialRedirect('google')}
                disabled={loadingProvider !== null}
                className={sharedClassName}
            >
                {loadingProvider === 'google' ? <div className="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-brand-600" /> : <GoogleIcon />}
                <span>{__('Continue with Google')}</span>
            </button>

            <button
                type="button"
                onClick={() => void handleSocialRedirect('github')}
                disabled={loadingProvider !== null}
                className={sharedClassName}
            >
                {loadingProvider === 'github' ? <div className="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-brand-600" /> : <Github className="h-5 w-5" />}
                <span>{__('Continue with GitHub')}</span>
            </button>

            <div className="relative py-2">
                <div className="absolute inset-0 flex items-center">
                    <div className="w-full border-t border-gray-200 dark:border-gray-700" />
                </div>
                <div className="relative flex justify-center text-xs uppercase tracking-[0.25em] text-gray-400 dark:text-gray-500">
                    <span className="bg-white px-3 dark:bg-gray-900">{__('or')}</span>
                </div>
            </div>
        </div>
    );
};

export default SocialAuthButtons;
