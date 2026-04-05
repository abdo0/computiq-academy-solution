import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { Mail, Lock, User, AlertCircle } from 'lucide-react';
import AppLink from '../common/AppLink';
import { useLocation } from 'react-router-dom';
import { useAppNavigate } from '../../hooks/useAppNavigate';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';
import { useTranslation } from '../../contexts/TranslationProvider';
import AuthLayout from './AuthLayout';
import SocialAuthButtons from './SocialAuthButtons';
import Turnstile from '../common/Turnstile';

const SignupPage: React.FC = () => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [phone, setPhone] = useState('');
    const [turnstileToken, setTurnstileToken] = useState('');

    const [errors, setErrors] = useState<Record<string, string[]>>({});

    const { register, isLoading, refreshUser } = useAuth();
    const { dir, language } = useLanguage();
    const { __ } = useTranslation();
    const navigate = useAppNavigate();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setErrors({});

        // Validate phone
        if (!phone) {
            setErrors({ phone: [__('Auth error')] }); // You might want a specific error for phone required
            return;
        }

        const result = await register({
            name,
            email,
            password,
            password_confirmation: passwordConfirmation,
            phone,
            locale: language,
            'cf-turnstile-response': turnstileToken,
        });

        if (result.success) {
            navigate((location as any).state?.from || '/dashboard');
        } else if (result.errors) {
            setErrors(result.errors);
        }
    };

    const getFieldError = (field: string) => {
        return errors[field]?.[0];
    };

    return (
        <AuthLayout 
            title={__('Create an account')} 
            subtitle={__('Join our learning platform today')}
        >
            <form className="space-y-6" onSubmit={handleSubmit}>
                <SocialAuthButtons 
                    redirectTo={(location as any).state?.from} 
                    onSuccess={async () => {
                        await refreshUser();
                        navigate((location as any).state?.from || '/dashboard');
                    }}
                />

                <div className="space-y-4">
                    {/* Name */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {__('Full name')}
                        </label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <User size={18} />
                            </div>
                            <input
                                type="text"
                                required
                                className={`block w-full ps-10 pe-3 py-3 border ${getFieldError('name') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600'} bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500`}
                                placeholder={__('Full name')}
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                            />
                        </div>
                        {getFieldError('name') && (
                            <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                                <AlertCircle size={14} />
                                {getFieldError('name')}
                            </p>
                        )}
                    </div>

                    {/* Email */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Email address')}</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Mail size={18} />
                            </div>
                            <input
                                type="email"
                                required
                                className={`block w-full ps-10 pe-3 py-3 border ${getFieldError('email') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600'} bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500`}
                                placeholder="user@example.com"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                            />
                        </div>
                        {getFieldError('email') && (
                            <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                                <AlertCircle size={14} />
                                {getFieldError('email')}
                            </p>
                        )}
                    </div>

                    {/* Phone */}
                    <div dir="ltr">
                        <label className={`block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 ${dir === 'rtl' ? 'text-right' : 'text-left'}`}>
                            {__('Phone number')}
                        </label>
                        <PhoneInput
                            country={'iq'}
                            value={phone}
                            onChange={phone => setPhone(phone)}
                            inputClass={`!w-full !py-3 !h-auto !border-gray-200 dark:!border-gray-600 !bg-gray-50/50 dark:!bg-gray-700/50 !text-gray-900 dark:!text-white !rounded-xl focus:!ring-brand-500 focus:!border-brand-500 focus:!bg-white dark:focus:!bg-gray-700 !outline-none !transition-all`}
                            buttonClass={`!border-gray-200 dark:!border-gray-600 !bg-gray-50/50 dark:!bg-gray-700/50 !rounded-l-xl`}
                            dropdownClass={`!bg-white dark:!bg-gray-800 !text-gray-900 dark:!text-white`}
                            containerClass={`${getFieldError('phone') ? '!border-red-500' : ''}`}
                        />
                        {getFieldError('phone') && (
                            <p className={`mt-1 text-sm text-red-500 flex items-center gap-1 ${dir === 'rtl' ? 'justify-end' : 'justify-start'}`}>
                                <AlertCircle size={14} />
                                {getFieldError('phone')}
                            </p>
                        )}
                    </div>

                    {/* Password */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Password')}</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Lock size={18} />
                            </div>
                            <input
                                type="password"
                                required
                                minLength={8}
                                className={`block w-full ps-10 pe-3 py-3 border ${getFieldError('password') ? 'border-red-500' : 'border-gray-200 dark:border-gray-600'} bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500`}
                                placeholder="••••••••"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                            />
                        </div>
                        {getFieldError('password') && (
                            <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                                <AlertCircle size={14} />
                                {getFieldError('password')}
                            </p>
                        )}
                    </div>

                    {/* Confirm Password */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{__('Confirm password')}</label>
                        <div className="relative">
                            <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                                <Lock size={18} />
                            </div>
                            <input
                                type="password"
                                required
                                className="block w-full ps-10 pe-3 py-3 border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:ring-brand-500 focus:border-brand-500 focus:bg-white dark:focus:bg-gray-700 outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500"
                                placeholder="••••••••"
                                value={passwordConfirmation}
                                onChange={(e) => setPasswordConfirmation(e.target.value)}
                            />
                        </div>
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
                        __('Create account')
                    )}
                </button>

                <div className="text-center text-sm text-gray-600 dark:text-gray-400 mt-6 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                    {__('Already have an account?')} {' '}
                    <AppLink
                        to={'/login'}
                        state={{ from: (location as any).state?.from }}
                        className="font-bold text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 transition-colors"
                    >
                        {__('Log in')}
                    </AppLink>
                </div>
            </form>
        </AuthLayout>
    );
};

export default SignupPage;
