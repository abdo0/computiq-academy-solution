import { useAppNavigate } from '../../hooks/useAppNavigate';
import React, { useState, useEffect } from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { KeyRound, Lock, AlertCircle, ArrowLeft, ArrowRight, CheckCircle } from 'lucide-react';
import {  useSearchParams } from 'react-router-dom';
import { orgAuthService } from '../../services/dataService';
import { toast } from 'react-toastify';
import { useTranslation } from '../../contexts/TranslationProvider';

const OrgResetPasswordPage: React.FC = () => {
    const [searchParams] = useSearchParams();
    const email = searchParams.get('Email') || '';
    
    const [otpCode, setOtpCode] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const { dir } = useLanguage();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();

    useEffect(() => {
        if (!email) {
            navigate('/org/login');
        }
    }, [email, navigate]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');

        if (password !== passwordConfirmation) {
            setError(__('Passwords do not match') || 'Passwords do not match');
            return;
        }

        setIsLoading(true);

        try {
            const result = await orgAuthService.resetPassword(email, otpCode, password, passwordConfirmation);

            if (result.success) {
                setSuccess(true);
                toast.success(__('Password reset success') || 'Password has been reset successfully');
                
                // Redirect to login after 2 seconds
                setTimeout(() => {
                    navigate('/org/login');
                }, 2000);
            } else {
                setError(result.error || __('Password reset failed') || 'Failed to reset password');
                toast.error(result.error || __('Password reset failed') || 'Failed to reset password');
            }
        } catch (err: any) {
            setError(__('Password reset failed') || 'Failed to reset password');
            toast.error(__('Password reset failed') || 'Failed to reset password');
        } finally {
            setIsLoading(false);
        }
    };

    if (success) {
        return (
            <div className="min-h-[80vh] flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
                <div className="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-sm shadow-xl border border-gray-100 dark:border-gray-700">
                    <div className="text-center">
                        <div className="mx-auto h-16 w-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-4">
                            <CheckCircle className="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white">{__('Password reset success') || 'Password Reset Successful'}</h2>
                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {__('Password reset success message') || 'Your password has been reset successfully. You will be redirected to the login page.'}
                        </p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-[80vh] flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-sm shadow-xl border border-gray-100 dark:border-gray-700">
                <div className="text-center">
                    <div className="mx-auto h-12 w-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                        <KeyRound className="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white">{__('Organization')} {__('Auth reset password title') || 'Reset Password'}</h2>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {__('Auth reset password subtitle') || 'Enter the OTP code sent to your email and your new password'}
                    </p>
                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-500">{email}</p>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    {error && (
                        <div className={`bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-sm text-sm flex items-center gap-2 ${dir === 'rtl' ? 'flex-row-reverse' : ''}`}>
                            <AlertCircle size={16} />
                            {error}
                        </div>
                    )}

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {__('Otp code') || 'OTP Code'} <span className="text-red-500">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <KeyRound size={18} />
                            </div>
                            <input
                                type="text"
                                required
                                maxLength={6}
                                className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-green-500 focus:border-green-500 focus:bg-white dark:focus:bg-gray-600 outline-none transition-all text-center text-2xl tracking-widest font-mono"
                                placeholder="000000"
                                value={otpCode}
                                onChange={(e) => setOtpCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
                            />
                        </div>
                        <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {__('Otp sent to email') || 'OTP code has been sent to your email address'}
                        </p>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {__('New password') || 'New Password'} <span className="text-red-500">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Lock size={18} />
                            </div>
                            <input
                                type="password"
                                required
                                minLength={8}
                                className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-green-500 focus:border-green-500 focus:bg-white dark:focus:bg-gray-600 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                                placeholder={__('Password min length') || 'Minimum 8 characters'}
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {__('Confirm password') || 'Confirm Password'} <span className="text-red-500">*</span>
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Lock size={18} />
                            </div>
                            <input
                                type="password"
                                required
                                minLength={8}
                                className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-green-500 focus:border-green-500 focus:bg-white dark:focus:bg-gray-600 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                                placeholder={__('Confirm password') || 'Confirm Password'}
                                value={passwordConfirmation}
                                onChange={(e) => setPasswordConfirmation(e.target.value)}
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={isLoading}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-sm shadow-sm text-sm font-bold text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {isLoading ? (
                            <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        ) : (
                            __('Auth reset password btn') || 'Reset Password'
                        )}
                    </button>

                    <div className="text-center">
                        <button
                            type="button"
                            onClick={() => navigate('/org/login')}
                            className={`inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 ${dir === 'rtl' ? 'flex-row-reverse' : ''}`}
                        >
                            {dir === 'rtl' ? <ArrowRight size={16} /> : <ArrowLeft size={16} />}
                            {__('Auth login link')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default OrgResetPasswordPage;

