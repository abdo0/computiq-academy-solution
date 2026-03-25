import React, { useState, useEffect, useRef } from 'react';
import { X, ShieldCheck, RefreshCw } from 'lucide-react';
import { useLanguage } from '../../contexts/LanguageContext';
import { useTranslation } from '../../contexts/TranslationProvider';

interface TwoFactorModalProps {
    isOpen: boolean;
    onClose: () => void;
    onVerify: (code: string) => Promise<void>;
    onResend: () => Promise<void>;
    isLoading?: boolean;
    devCode?: string | null;
}

const TwoFactorModal: React.FC<TwoFactorModalProps> = ({
    isOpen,
    onClose,
    onVerify,
    onResend,
    isLoading = false,
    devCode,
}) => {
    const { __ } = useTranslation();
    const [code, setCode] = useState(['', '', '', '', '', '']);
    const [error, setError] = useState('');
    const [isResending, setIsResending] = useState(false);
    const [cooldown, setCooldown] = useState(0);
    const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

    useEffect(() => {
        if (isOpen) {
            // Focus first input when modal opens
            inputRefs.current[0]?.focus();
            // Start cooldown timer when modal opens (60 seconds)
            setCooldown(60);
        } else {
            // Reset when modal closes
            setCode(['', '', '', '', '', '']);
            setError('');
            setCooldown(0);
        }
    }, [isOpen]);

    useEffect(() => {
        if (!isOpen || cooldown <= 0) {
            return;
        }

        const timer = setInterval(() => {
            setCooldown((prev) => (prev > 0 ? prev - 1 : 0));
        }, 1000);

        return () => clearInterval(timer);
    }, [cooldown, isOpen]);

    const handleInputChange = (index: number, value: string) => {
        // Only allow digits
        if (value && !/^\d$/.test(value)) {
            return;
        }

        const newCode = [...code];
        newCode[index] = value;
        setCode(newCode);
        setError('');

        // Auto-focus next input
        if (value && index < 5) {
            inputRefs.current[index + 1]?.focus();
        }

        // Auto-submit when all 6 digits are entered
        if (newCode.every(digit => digit !== '') && index === 5) {
            handleVerify(newCode.join(''));
        }
    };

    const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
        // Handle backspace
        if (e.key === 'Backspace' && !code[index] && index > 0) {
            inputRefs.current[index - 1]?.focus();
        }
    };

    const handlePaste = (e: React.ClipboardEvent) => {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').trim();
        
        if (/^\d{6}$/.test(pastedData)) {
            const digits = pastedData.split('');
            const newCode = [...code];
            digits.forEach((digit, index) => {
                if (index < 6) {
                    newCode[index] = digit;
                }
            });
            setCode(newCode);
            setError('');
            
            // Focus last input
            inputRefs.current[5]?.focus();
            
            // Auto-submit
            handleVerify(newCode.join(''));
        }
    };

    const handleVerify = async (verificationCode?: string) => {
        const codeToVerify = verificationCode || code.join('');
        
        if (codeToVerify.length !== 6) {
            setError(__('Auth 2fa invalid code') || 'Please enter a valid 6-digit code');
            return;
        }

        try {
            await onVerify(codeToVerify);
        } catch (err: any) {
            setError(err.message || __('Auth 2fa verify failed') || 'Verification failed');
            // Clear code on error
            setCode(['', '', '', '', '', '']);
            inputRefs.current[0]?.focus();
        }
    };

    const handleResend = async () => {
        // Prevent multiple clicks
        if (cooldown > 0 || isResending) {
            return;
        }

        setIsResending(true);
        // Start cooldown immediately (before API call)
        setCooldown(60);

        try {
            await onResend();
        } catch (err) {
            // Error handled by parent, but cooldown still applies
        } finally {
            setIsResending(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div className="bg-white dark:bg-gray-800 rounded-sm shadow-2xl w-full max-w-md mx-4 p-6 relative">
                <button
                    onClick={onClose}
                    className="absolute top-4 end-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    <X size={20} />
                </button>

                <div className="text-center mb-6">
                    <div className="mx-auto w-16 h-16 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mb-4">
                        <ShieldCheck className="w-8 h-8 text-brand-600 dark:text-brand-400" />
                    </div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {__('Auth 2fa title') || 'Two-Factor Authentication'}
                    </h2>
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        {__('Auth 2fa description') || 'Enter the 6-digit code sent to your phone'}
                    </p>

                    {devCode && (
                        <p className="mt-2 text-xs font-mono text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 inline-flex items-center px-3 py-1 rounded-sm">
                            <span className="mr-1">DEV CODE:</span>
                            <span>{devCode}</span>
                        </p>
                    )}
                </div>

                <div className="space-y-4">
                    <div className="flex justify-center gap-2">
                        {code.map((digit, index) => (
                            <input
                                key={index}
                                ref={(el) => (inputRefs.current[index] = el)}
                                type="text"
                                inputMode="numeric"
                                maxLength={1}
                                value={digit}
                                onChange={(e) => handleInputChange(index, e.target.value)}
                                onKeyDown={(e) => handleKeyDown(index, e)}
                                onPaste={index === 0 ? handlePaste : undefined}
                                className="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 dark:border-gray-600 rounded-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none transition-all"
                                disabled={isLoading}
                            />
                        ))}
                    </div>

                    {error && (
                        <p className="text-sm text-red-500 text-center">{error}</p>
                    )}

                    <button
                        onClick={() => handleVerify()}
                        disabled={isLoading || code.some(d => !d)}
                        className="w-full bg-brand-600 text-white font-bold py-3 px-4 rounded-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {isLoading ? (
                            <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mx-auto" />
                        ) : (
                            __('Auth 2fa verify') || 'Verify'
                        )}
                    </button>

                    <div className="text-center">
                        <button
                            type="button"
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                handleResend();
                            }}
                            disabled={isResending || cooldown > 0}
                            className="text-sm text-brand-600 hover:text-brand-500 dark:text-brand-400 dark:hover:text-brand-300 flex items-center justify-center gap-2 mx-auto disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <RefreshCw size={16} className={isResending ? 'animate-spin' : ''} />
                            {cooldown > 0
                                ? (__('Auth 2fa resend in seconds')
                                    ? __('Auth 2fa resend in seconds').replace('{{seconds}}', String(cooldown))
                                    : `You can request a new code in ${cooldown} seconds`)
                                : (__('Auth 2fa resend') || 'Resend Code')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default TwoFactorModal;

