import { useAppNavigate } from '../../hooks/useAppNavigate';
import React, { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { Mail, Lock, LogIn, AlertCircle, Building2 } from 'lucide-react';
import {  useSearchParams } from 'react-router-dom';
import { useTranslation } from '../../contexts/TranslationProvider';

const OrgLoginPage: React.FC = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [remember, setRemember] = useState(false);
    const [error, setError] = useState('');

    const { orgLogin, isLoading, organization } = useAuth();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();
    const [searchParams] = useSearchParams();

    // Redirect if already logged in
    useEffect(() => {
        if (organization) {
            const redirect = searchParams.get('Redirect') || '/org/dashboard';
            navigate(redirect, { replace: true });
        }
    }, [organization, navigate, searchParams]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');

        const success = await orgLogin(email, password, remember);
        if (success) {
            const redirect = searchParams.get('Redirect') || '/org/dashboard';
            navigate(redirect, { replace: true });
        } else {
            setError(__('Auth error'));
        }
    };

    return (
        <div className="min-h-[80vh] flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-sm shadow-xl border border-gray-100 dark:border-gray-700">
                <div className="text-center">
                    <div className="mx-auto h-14 w-14 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mb-4 shadow-lg shadow-green-200">
                        <Building2 className="h-7 w-7 text-white" />
                    </div>
                    <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white">{__('Organization')} {__('Auth login title')}</h2>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">{__('Auth welcome back')}</p>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    {error && (
                        <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-sm text-sm flex items-center gap-2">
                            <AlertCircle size={16} />
                            {error}
                        </div>
                    )}

                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Form email')}</label>
                            <div className="relative">
                                <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                    <Mail size={18} />
                                </div>
                                <input
                                    type="email"
                                    required
                                    className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-green-500 focus:border-green-500 focus:bg-white dark:focus:bg-gray-600 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                                    placeholder="org@example.com"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Form password')}</label>
                            <div className="relative">
                                <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                    <Lock size={18} />
                                </div>
                                <input
                                    type="password"
                                    required
                                    className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-green-500 focus:border-green-500 focus:bg-white dark:focus:bg-gray-600 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
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
                                className="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded-sm"
                            />
                            <label htmlFor="remember-me" className="ms-2 block text-sm text-gray-900 dark:text-gray-300">
                                {__('Remember me')}
                            </label>
                        </div>

                        <div className="text-sm">
                            <button
                                type="button"
                                onClick={() => navigate('/org/forgot-password')}
                                className="font-medium text-green-600 hover:text-green-500 dark:text-green-400 dark:hover:text-green-300"
                            >
                                {__('Auth forgot link')}
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={isLoading}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-sm shadow-sm text-sm font-bold text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:-translate-y-0.5 active:translate-y-0"
                    >
                        {isLoading ? (
                            <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        ) : (
                            <span className="flex items-center gap-2">
                                <LogIn size={18} />
                                {__('Auth login btn')}
                            </span>
                        )}
                    </button>

                    <div className="text-center text-sm text-gray-600 dark:text-gray-400">
                        {__('Auth no account')} {' '}
                        <button
                            type="button"
                            onClick={() => navigate('/org/signup')}
                            className="font-bold text-green-600 hover:text-green-500 dark:text-green-400 dark:hover:text-green-300"
                        >
                            {__('Auth create account')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default OrgLoginPage;
