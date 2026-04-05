import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { Mail, Lock, AlertCircle } from 'lucide-react';
import { useSearchParams, useLocation } from 'react-router-dom';
import AppLink from '../common/AppLink';
import { useAppNavigate } from '../../hooks/useAppNavigate';
import { useTranslation } from '../../contexts/TranslationProvider';
import AuthLayout from './AuthLayout';
import SocialAuthButtons from './SocialAuthButtons';
import Turnstile from '../common/Turnstile';

const LoginPage: React.FC = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [remember, setRemember] = useState(false);
    const [error, setError] = useState('');
    const [turnstileToken, setTurnstileToken] = useState('');

    const { login, isLoading, refreshUser } = useAuth();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();
    const location = useLocation();
    const [searchParams] = useSearchParams();

    const socialError = (() => {
        const errorCode = searchParams.get('error');

        if (errorCode === 'social_auth_failed') {
            return __('We could not complete the social login. Please try again.');
        }

        if (errorCode === 'social_email_required') {
            return __('Your social account must expose an email address before you can sign in.');
        }

        if (errorCode === 'unsupported_provider') {
            return __('That social login provider is not supported.');
        }

        if (errorCode === 'social_provider_not_configured') {
            return __('This social login provider is not configured yet.');
        }

        return '';
    })();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');

        const success = await login(email, password, remember, turnstileToken);
        if (success) {
            navigate((location as any).state?.from || '/dashboard');
        }

        if (!success) {
            setError(__('Auth error'));
        }
    };

    return (
        <AuthLayout 
            title={__('Log in')} 
            subtitle={__('Welcome back!')}
        >
            <form className="space-y-6" onSubmit={handleSubmit}>
                {socialError && (
                    <div className="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                        <AlertCircle size={16} />
                        {socialError}
                    </div>
                )}

                {error && (
                    <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                        <AlertCircle size={16} />
                        {error}
                    </div>
                )}

                <SocialAuthButtons 
                    redirectTo={(location as any).state?.from} 
                    onSuccess={async () => {
                        await refreshUser();
                        navigate((location as any).state?.from || '/dashboard');
                    }}
                />

                <div className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Email address')}</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Mail size={18} />
                            </div>
                            <input
                                type="email"
                                required
                                className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                                placeholder="user@example.com"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Password')}</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Lock size={18} />
                            </div>
                            <input
                                type="password"
                                required
                                className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                                placeholder="••••••••"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                            />
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <div className="flex items-center">
                        <input
                            id="remember-me"
                            name="remember-me"
                            type="checkbox"
                            checked={remember}
                            onChange={(e) => setRemember(e.target.checked)}
                            className="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded"
                        />
                        <label htmlFor="remember-me" className="ms-2 block text-sm text-gray-900 dark:text-gray-300">
                            {__('Remember me')}
                        </label>
                    </div>

                    <div className="text-sm">
                        <AppLink
                            to={'/forgot-password'}
                            className="font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition-colors"
                        >
                            {__('Forgot password?')}
                        </AppLink>
                    </div>
                </div>

                <Turnstile onVerify={setTurnstileToken} onExpire={() => setTurnstileToken('')} />

                <button
                    type="submit"
                    disabled={isLoading}
                    className="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:-translate-y-0.5"
                >
                    {isLoading ? (
                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                    ) : (
                        __('Log in')
                    )}
                </button>

                <div className="text-center text-sm text-gray-600 dark:text-gray-400 mt-6 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                    {__('Don\'t have an account?')} {' '}
                    <AppLink
                        to={'/signup'}
                        state={{ from: (location as any).state?.from }}
                        className="font-bold text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition-colors"
                    >
                        {__('Create an account')}
                    </AppLink>
                </div>
            </form>
        </AuthLayout>
    );
};

export default LoginPage;
