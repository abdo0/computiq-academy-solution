import React, { useMemo, useState } from 'react';
import { AlertCircle, CheckCircle2, Lock, Mail, User as UserIcon, LockKeyhole } from 'lucide-react';
import { toast } from 'react-toastify';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';
import { useAuth } from '../../contexts/AuthContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import SocialAuthButtons from '../auth/SocialAuthButtons';
import Turnstile from '../common/Turnstile';

interface CheckoutAuthStepProps {
    onAuthenticated?: () => Promise<void> | void;
}

const CheckoutAuthStep: React.FC<CheckoutAuthStepProps> = ({ onAuthenticated }) => {
    const { user, login, register, isLoading, error } = useAuth();
    const { __ } = useTranslation();
    const { dir, language } = useLanguage();
    const [activeTab, setActiveTab] = useState<'login' | 'register'>('login');
    const [submitError, setSubmitError] = useState('');

    const [loginEmail, setLoginEmail] = useState('');
    const [loginPassword, setLoginPassword] = useState('');
    const [loginRemember, setLoginRemember] = useState(false);
    const [loginTurnstileToken, setLoginTurnstileToken] = useState('');

    const [registerName, setRegisterName] = useState('');
    const [registerEmail, setRegisterEmail] = useState('');
    const [registerPassword, setRegisterPassword] = useState('');
    const [registerPasswordConfirmation, setRegisterPasswordConfirmation] = useState('');
    const [registerPhone, setRegisterPhone] = useState('');
    const [registerTurnstileToken, setRegisterTurnstileToken] = useState('');
    const [registerErrors, setRegisterErrors] = useState<Record<string, string[]>>({});

    const authError = useMemo(() => {
        const params = new URLSearchParams(window.location.search);
        const errorCode = params.get('auth_error');

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
    }, [__]);

    React.useEffect(() => {
        if (error) {
            setSubmitError(error);
        }
    }, [error]);

    const handleAuthenticated = async () => {
        if (typeof onAuthenticated === 'function') {
            await onAuthenticated();
        }

        toast.success(__('Your cart is now connected to your account.'));
    };

    const handleLoginSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        setSubmitError('');

        const success = await login(loginEmail, loginPassword, loginRemember, loginTurnstileToken);

        if (success) {
            await handleAuthenticated();
            return;
        }

        setSubmitError(error || __('Auth error'));
    };

    const handleRegisterSubmit = async (event: React.FormEvent) => {
        event.preventDefault();
        setSubmitError('');
        setRegisterErrors({});

        if (!registerPhone) {
            setRegisterErrors({ phone: [__('Auth error')] });
            return;
        }

        const result = await register({
            name: registerName,
            email: registerEmail,
            password: registerPassword,
            password_confirmation: registerPasswordConfirmation,
            phone: registerPhone,
            locale: language,
            'cf-turnstile-response': registerTurnstileToken,
        });

        if (result.success) {
            await handleAuthenticated();
            return;
        }

        if (result.errors) {
            setRegisterErrors(result.errors);
        }

        setSubmitError(error || __('Registration failed'));
    };

    const getFieldError = (field: string) => registerErrors[field]?.[0];

    if (user) {
        return (
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                <div className="flex items-start gap-3">
                    <div className="mt-0.5 rounded-full bg-emerald-100 p-2 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <CheckCircle2 className="h-5 w-5" />
                    </div>
                    <div className="flex-1">
                        <h3 className="text-base font-black text-emerald-900 dark:text-emerald-100">
                            {__('Your account is ready')}
                        </h3>
                        <p className="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                            {__('Signed in as :email', { email: user.email })}
                        </p>
                        <p className="mt-2 text-sm text-emerald-700/90 dark:text-emerald-300/90">
                            {__('Your cart is linked to this account. You can continue to payment now.')}
                        </p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="rounded-2xl border border-gray-100 bg-white p-6 dark:border-gray-700/50 dark:bg-gray-800">
            <div className="flex items-start gap-3">
                <div className="rounded-2xl bg-brand-50 p-3 text-brand-600 dark:bg-brand-900/30 dark:text-brand-300">
                    <Lock className="h-5 w-5" />
                </div>
                <div className="flex-1">
                    <h3 className="text-lg font-black text-gray-900 dark:text-white">{__('Account')}</h3>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {__('Please log in or create an account before completing your payment.')}
                    </p>
                </div>
            </div>

            {(authError || submitError) && (
                <div className="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-300">
                    <div className="flex items-center gap-2">
                        <AlertCircle className="h-4 w-4 shrink-0" />
                        <span>{authError || submitError}</span>
                    </div>
                </div>
            )}

            <div className="mt-5 inline-flex rounded-2xl border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-900">
                <button
                    type="button"
                    onClick={() => setActiveTab('login')}
                    className={`rounded-[14px] px-4 py-2 text-sm font-bold transition-colors ${
                        activeTab === 'login'
                            ? 'bg-white text-brand-600 shadow-sm dark:bg-gray-800 dark:text-brand-300'
                            : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                    }`}
                >
                    {__('Log in')}
                </button>
                <button
                    type="button"
                    onClick={() => setActiveTab('register')}
                    className={`rounded-[14px] px-4 py-2 text-sm font-bold transition-colors ${
                        activeTab === 'register'
                            ? 'bg-white text-brand-600 shadow-sm dark:bg-gray-800 dark:text-brand-300'
                            : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'
                    }`}
                >
                    {__('Create an account')}
                </button>
            </div>

            <div className="mt-6">
                <SocialAuthButtons redirectTo="/checkout" onSuccess={onAuthenticated} />

                {activeTab === 'login' ? (
                    <form className="space-y-4" onSubmit={handleLoginSubmit}>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{__('Email address')}</label>
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                    <Mail size={18} />
                                </div>
                                <input
                                    type="email"
                                    required
                                    className="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 ps-10 pe-3 text-gray-900 outline-none transition-all placeholder-gray-400 focus:border-brand-500 focus:bg-white dark:border-gray-600 dark:bg-gray-700/50 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-700"
                                    placeholder="user@example.com"
                                    value={loginEmail}
                                    onChange={(event) => setLoginEmail(event.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{__('Password')}</label>
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                    <LockKeyhole size={18} />
                                </div>
                                <input
                                    type="password"
                                    required
                                    className="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 ps-10 pe-3 text-gray-900 outline-none transition-all placeholder-gray-400 focus:border-brand-500 focus:bg-white dark:border-gray-600 dark:bg-gray-700/50 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-700"
                                    placeholder="••••••••"
                                    value={loginPassword}
                                    onChange={(event) => setLoginPassword(event.target.value)}
                                />
                            </div>
                        </div>

                        <label className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input
                                type="checkbox"
                                checked={loginRemember}
                                onChange={(event) => setLoginRemember(event.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                            />
                            <span>{__('Remember me')}</span>
                        </label>

                        <Turnstile onVerify={setLoginTurnstileToken} onExpire={() => setLoginTurnstileToken('')} />

                        <button
                            type="submit"
                            disabled={isLoading}
                            className="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {isLoading ? __('Signing in...') : __('Log in and continue')}
                        </button>
                    </form>
                ) : (
                    <form className="space-y-4" onSubmit={handleRegisterSubmit}>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{__('Full name')}</label>
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                    <UserIcon size={18} />
                                </div>
                                <input
                                    type="text"
                                    required
                                    className={`block w-full rounded-xl border py-3 ps-10 pe-3 text-gray-900 outline-none transition-all placeholder-gray-400 focus:border-brand-500 focus:bg-white dark:bg-gray-700/50 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-700 ${
                                        getFieldError('name') ? 'border-red-500 bg-red-50/40 dark:border-red-500' : 'border-gray-200 bg-gray-50/50 dark:border-gray-600'
                                    }`}
                                    placeholder={__('Full name')}
                                    value={registerName}
                                    onChange={(event) => setRegisterName(event.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{__('Email address')}</label>
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                    <Mail size={18} />
                                </div>
                                <input
                                    type="email"
                                    required
                                    className={`block w-full rounded-xl border py-3 ps-10 pe-3 text-gray-900 outline-none transition-all placeholder-gray-400 focus:border-brand-500 focus:bg-white dark:bg-gray-700/50 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-700 ${
                                        getFieldError('email') ? 'border-red-500 bg-red-50/40 dark:border-red-500' : 'border-gray-200 bg-gray-50/50 dark:border-gray-600'
                                    }`}
                                    placeholder="user@example.com"
                                    value={registerEmail}
                                    onChange={(event) => setRegisterEmail(event.target.value)}
                                />
                            </div>
                        </div>

                        <div dir="ltr">
                            <label className={`mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300 ${dir === 'rtl' ? 'text-right' : 'text-left'}`}>
                                {__('Phone number')}
                            </label>
                            <PhoneInput
                                country={'iq'}
                                value={registerPhone}
                                onChange={(phone) => setRegisterPhone(phone)}
                                inputClass="!w-full !rounded-xl !border-gray-200 dark:!border-gray-600 !bg-gray-50/50 dark:!bg-gray-700/50 !py-3 !h-auto !text-gray-900 dark:!text-white focus:!border-brand-500 focus:!bg-white dark:focus:!bg-gray-700"
                                buttonClass="!rounded-s-xl !border-gray-200 dark:!border-gray-600 !bg-gray-50/50 dark:!bg-gray-700/50"
                                dropdownClass="!bg-white dark:!bg-gray-800 !text-gray-900 dark:!text-white"
                            />
                            {getFieldError('phone') && (
                                <p className={`mt-1 text-sm text-red-500 ${dir === 'rtl' ? 'text-right' : 'text-left'}`}>
                                    {getFieldError('phone')}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{__('Password')}</label>
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                    <LockKeyhole size={18} />
                                </div>
                                <input
                                    type="password"
                                    required
                                    className={`block w-full rounded-xl border py-3 ps-10 pe-3 text-gray-900 outline-none transition-all placeholder-gray-400 focus:border-brand-500 focus:bg-white dark:bg-gray-700/50 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-700 ${
                                        getFieldError('password') ? 'border-red-500 bg-red-50/40 dark:border-red-500' : 'border-gray-200 bg-gray-50/50 dark:border-gray-600'
                                    }`}
                                    placeholder="••••••••"
                                    value={registerPassword}
                                    onChange={(event) => setRegisterPassword(event.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">{__('Confirm password')}</label>
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                    <LockKeyhole size={18} />
                                </div>
                                <input
                                    type="password"
                                    required
                                    className="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 ps-10 pe-3 text-gray-900 outline-none transition-all placeholder-gray-400 focus:border-brand-500 focus:bg-white dark:border-gray-600 dark:bg-gray-700/50 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-700"
                                    placeholder="••••••••"
                                    value={registerPasswordConfirmation}
                                    onChange={(event) => setRegisterPasswordConfirmation(event.target.value)}
                                />
                            </div>
                        </div>

                        <Turnstile onVerify={setRegisterTurnstileToken} onExpire={() => setRegisterTurnstileToken('')} />

                        <button
                            type="submit"
                            disabled={isLoading}
                            className="w-full rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {isLoading ? __('Creating account...') : __('Create account and continue')}
                        </button>
                    </form>
                )}
            </div>
        </div>
    );
};

export default CheckoutAuthStep;
