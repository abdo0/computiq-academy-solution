import { useAppNavigate } from '../../hooks/useAppNavigate';
import React, { useState } from 'react';
import { useLanguage } from '../../contexts/LanguageContext';
import { Mail, ArrowLeft, ArrowRight, AlertCircle, Heart } from 'lucide-react';
import { userAuthService } from '../../services/dataService';
import { toast } from 'react-toastify';
import { useTranslation } from '../../contexts/TranslationProvider';
import AuthLayout from './AuthLayout';

const ForgotPasswordPage: React.FC = () => {
    const [email, setEmail] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const { dir } = useLanguage();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setIsLoading(true);

        try {
            const result = await userAuthService.forgotPassword(email);

            if (result.success) {
                toast.success(__('Password reset otp sent') || 'Password reset code has been sent to your email');
                
                // Show OTP in development
                if (result.otpCode) {
                    toast.info(`OTP Code (Development): ${result.otpCode}`, { autoClose: 10000 });
                }

                // Navigate to reset password page with email
                navigate(`/reset-password?email=${encodeURIComponent(email)}`);
            } else {
                setError(result.error || __('Password reset failed') || 'Failed to send password reset code');
                toast.error(result.error || __('Password reset failed') || 'Failed to send password reset code');
            }
        } catch (err: any) {
            setError(__('Password reset failed') || 'Failed to send password reset code');
            toast.error(__('Password reset failed') || 'Failed to send password reset code');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <AuthLayout 
            title={__('Auth forgot password')} 
            subtitle={__('Auth forgot subtitle')}
        >
            <form className="space-y-6" onSubmit={handleSubmit}>
                {error && (
                    <div className={`bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2 ${dir === 'rtl' ? 'flex-row-reverse' : ''}`}>
                        <AlertCircle size={16} />
                        {error}
                    </div>
                )}

                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Form email')}</label>
                    <div className="relative">
                        <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                            <Mail size={18} />
                        </div>
                        <input
                            type="email"
                            required
                            className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                            placeholder="student@example.com"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    disabled={isLoading}
                    className="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:-translate-y-0.5"
                >
                    {isLoading ? (
                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                    ) : (
                        __('Auth send otp') || __('Send otp') || 'Send OTP'
                    )}
                </button>

                <div className="text-center mt-6 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                    <button
                        type="button"
                        onClick={() => navigate('/login')}
                        className={`inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400 transition-colors ${dir === 'rtl' ? 'flex-row-reverse' : ''}`}
                    >
                        {dir === 'rtl' ? <ArrowRight size={16} /> : <ArrowLeft size={16} />}
                        {__('Auth login link')}
                    </button>
                </div>
            </form>
        </AuthLayout>
    );
};

export default ForgotPasswordPage;
