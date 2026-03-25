import { useAppNavigate } from '../../hooks/useAppNavigate';
import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { ShieldCheck, AlertCircle } from 'lucide-react';
import { useTranslation } from '../../contexts/TranslationProvider';

const VerifyEmailPage: React.FC = () => {
    const [code, setCode] = useState('');
    const { verifyEmail, isLoading, error } = useAuth();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const success = await verifyEmail(code);
        if (success) {
            navigate('/dashboard');
        }
    };

    return (
        <div className="min-h-[80vh] flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-sm shadow-xl border border-gray-100 dark:border-gray-700">
                <div className="text-center">
                    <div className="mx-auto h-12 w-12 bg-brand-100 rounded-full flex items-center justify-center mb-4">
                        <ShieldCheck className="h-6 w-6 text-brand-600" />
                    </div>
                    <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white">{__('Auth verify title')}</h2>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">{__('Auth verify subtitle')}</p>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    {error && (
                        <div className="bg-red-50 dark:bg-red-900/20 p-4 rounded-sm flex items-center gap-2 text-red-600 dark:text-red-400 text-sm font-medium">
                            <AlertCircle size={18} />
                            {error}
                        </div>
                    )}

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Auth verify code')}</label>
                        <input
                            type="text"
                            required
                            maxLength={4}
                            className="block w-full px-3 py-4 text-center text-2xl tracking-widest border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-600 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="1234"
                            value={code}
                            onChange={(e) => setCode(e.target.value)}
                        />
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">Enter code: 1234</p>
                    </div>

                    <button
                        type="submit"
                        disabled={isLoading}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-sm shadow-sm text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {isLoading ? (
                            <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        ) : (
                            __('Auth verify btn')
                        )}
                    </button>
                </form>
            </div>
        </div>
    );
};

export default VerifyEmailPage;