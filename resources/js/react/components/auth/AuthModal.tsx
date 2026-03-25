import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import { useLanguage } from '../../contexts/LanguageContext';
import LoginForm from './LoginForm';
import RegisterForm from './RegisterForm';
import { useTranslation } from '../../contexts/TranslationProvider';

interface AuthModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
}

const AuthModal: React.FC<AuthModalProps> = ({ isOpen, onClose, onSuccess }) => {
    const [authMode, setAuthMode] = useState<'login' | 'register'>('login');
    const { __ } = useTranslation();

    useEffect(() => {
        if (isOpen) {
            setAuthMode('login'); // Reset to login mode whenever modal opens
        }
    }, [isOpen]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div className="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                {/* Backdrop overlay */}
                <div
                    className="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                    aria-hidden="true"
                    onClick={onClose}
                ></div>

                <span className="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div className="relative z-10 inline-block align-bottom bg-white dark:bg-gray-900 rounded-sm text-start overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 dark:border-gray-800 rtl:text-right ltr:text-left">

                    {/* Header */}
                    <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <div className="flex items-center gap-3">
                            <h3 className="text-lg font-black text-gray-900 dark:text-white" id="modal-title">
                                {authMode === 'login' ? __('Login title', 'Login') : __('Create account title', 'Create Account')}
                            </h3>
                        </div>
                        <button
                            type="button"
                            className="p-2 bg-gray-50 dark:bg-gray-800 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none"
                            onClick={onClose}
                        >
                            <span className="sr-only">Close</span>
                            <X className="h-5 w-5" />
                        </button>
                    </div>

                    <div className="bg-gray-50 dark:bg-gray-800 rounded-sm overflow-hidden m-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div className="flex bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                            <button
                                className={`flex-1 py-3.5 text-sm font-bold transition-colors ${authMode === 'login' ? 'text-emerald-600 border-b-2 border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/10' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-300'}`}
                                onClick={() => setAuthMode('login')}
                            >
                                {__('Login')}
                            </button>
                            <button
                                className={`flex-1 py-3.5 text-sm font-bold transition-colors ${authMode === 'register' ? 'text-emerald-600 border-b-2 border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/10' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-300'}`}
                                onClick={() => setAuthMode('register')}
                            >
                                {__('Register')}
                            </button>
                        </div>

                        <div className="p-6">
                            {authMode === 'login' ? (
                                <LoginForm onSuccess={onSuccess} />
                            ) : (
                                <RegisterForm onSuccess={onSuccess} />
                            )}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    );
};

export default AuthModal;
