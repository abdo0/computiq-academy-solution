import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { Mail, Lock, User, AlertCircle } from 'lucide-react';
import { useTranslation } from '../../contexts/TranslationProvider';
import PhoneNumberInput, { createPhoneFieldValue } from '../common/PhoneNumberInput';

interface RegisterFormProps {
    onSuccess: () => void;
}

const RegisterForm: React.FC<RegisterFormProps> = ({ onSuccess }) => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [phoneField, setPhoneField] = useState(() => createPhoneFieldValue());
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [isLoading, setIsLoading] = useState(false);

    const { register } = useAuth();
    const { dir, language } = useLanguage();
    const { __ } = useTranslation();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setErrors({});

        if (password !== passwordConfirmation) {
            setErrors({ password: [__('Passwords do not match') || 'Passwords do not match'] });
            return;
        }

        setIsLoading(true);
        const result = await register({
            name,
            email,
            password,
            password_confirmation: passwordConfirmation,
            phone: phoneField.phone || undefined,
            country_code: phoneField.phone ? phoneField.countryCode : undefined,
            locale: language,
        });
        setIsLoading(false);

        if (result.success) {
            onSuccess();
        } else if (result.errors) {
            setErrors(result.errors);
        }
    };

    const getFieldError = (field: string) => {
        return errors[field]?.[0];
    };

    return (
        <form className="space-y-4" onSubmit={handleSubmit}>
            {/* Name */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {__('Form name')}
                </label>
                <div className="relative">
                    <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                        <User size={18} />
                    </div>
                    <input
                        type="text"
                        required
                        className={`block w-full ps-10 pe-3 py-2.5 border ${getFieldError('name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'} bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-brand-500 focus:border-brand-500 outline-none transition-all sm:text-sm`}
                        placeholder={__('Form name')}
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                    />
                </div>
                {getFieldError('name') && (
                    <p className="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <AlertCircle size={12} />
                        {getFieldError('name')}
                    </p>
                )}
            </div>

            {/* Email */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {__('Form email')}
                </label>
                <div className="relative">
                    <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                        <Mail size={18} />
                    </div>
                    <input
                        type="email"
                        required
                        className={`block w-full ps-10 pe-3 py-2.5 border ${getFieldError('email') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'} bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-brand-500 focus:border-brand-500 outline-none transition-all sm:text-sm`}
                        placeholder="user@example.com"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                    />
                </div>
                {getFieldError('email') && (
                    <p className="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <AlertCircle size={12} />
                        {getFieldError('email')}
                    </p>
                )}
            </div>

            {/* Phone */}
            <div>
                <label className={`block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 ${dir === 'rtl' ? 'text-right' : 'text-left'}`}>
                    {__('Form phone')}
                </label>
                <PhoneNumberInput
                    value={phoneField.phone}
                    countryCode={phoneField.countryCode}
                    onChange={setPhoneField}
                    variant="compact"
                    invalid={Boolean(getFieldError('phone'))}
                    placeholder={__('Enter phone number with country code')}
                />
                {getFieldError('phone') && (
                    <p className={`mt-1 text-xs text-red-500 flex items-center gap-1 ${dir === 'rtl' ? 'justify-end' : 'justify-start'}`}>
                        <AlertCircle size={12} />
                        {getFieldError('phone')}
                    </p>
                )}
            </div>

            {/* Password */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {__('Form password')}
                </label>
                <div className="relative">
                    <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                        <Lock size={18} />
                    </div>
                    <input
                        type="password"
                        required
                        minLength={8}
                        className={`block w-full ps-10 pe-3 py-2.5 border ${getFieldError('password') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'} bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-brand-500 focus:border-brand-500 outline-none transition-all sm:text-sm`}
                        placeholder="••••••••"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                    />
                </div>
                {getFieldError('password') && (
                    <p className="mt-1 text-xs text-red-500 flex items-center gap-1">
                        <AlertCircle size={12} />
                        {getFieldError('password')}
                    </p>
                )}
            </div>

            {/* Confirm Password */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {__('Form confirm password')}
                </label>
                <div className="relative">
                    <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-gray-400">
                        <Lock size={18} />
                    </div>
                    <input
                        type="password"
                        required
                        className="block w-full ps-10 pe-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-sm focus:ring-brand-500 focus:border-brand-500 outline-none transition-all sm:text-sm"
                        placeholder="••••••••"
                        value={passwordConfirmation}
                        onChange={(e) => setPasswordConfirmation(e.target.value)}
                    />
                </div>
            </div>

            <button
                type="submit"
                disabled={isLoading}
                className="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-sm shadow-sm text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                {isLoading ? (
                    <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                    __('Register')
                )}
            </button>
        </form>
    );
};

export default RegisterForm;
