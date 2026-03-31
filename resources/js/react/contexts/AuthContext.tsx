import React, { createContext, useContext, useState, useEffect, ReactNode, useCallback } from 'react';
import { useNavigate as useRawNavigate } from 'react-router-dom';
import { userAuthService } from '../services/dataService';
import { toast } from 'react-toastify';
import { useLanguage } from './LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';

export interface User {
    id: string;
    name: string;
    email: string;
    phone?: string;
    locale?: string;
    isVerified: boolean;
    purchasedCourseIds?: number[];
}

interface AuthContextType {
    user: User | null;
    isLoading: boolean;
    isInitialized: boolean;
    error: string | null;
    
    show2FAModal: boolean;
    setShow2FAModal: (show: boolean) => void;
    dev2FACode?: string | null;
    verify2FA: (code: string) => Promise<boolean>;
    resend2FA: () => Promise<void>;
    
    login: (email: string, password: string, remember?: boolean, turnstileToken?: string) => Promise<boolean>;
    register: (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string }) => Promise<{ success: boolean; errors?: any }>;
    
    logout: () => Promise<void>;
    refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [user, setUser] = useState<User | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [isInitialized, setIsInitialized] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [show2FAModal, setShow2FAModal] = useState(false);
    const [dev2FACode, setDev2FACode] = useState<string | null>(null);
    const { setLanguage } = useLanguage();
    const { __ } = useTranslation();
    const navigate = useRawNavigate();

    useEffect(() => {
        const checkAuth = async () => {
            const rootElement = document.getElementById('root');
            if (rootElement) {
                const initialDataAttr = rootElement.getAttribute('data-initial');
                if (initialDataAttr && initialDataAttr.trim() !== '') {
                    try {
                        const initialData = JSON.parse(initialDataAttr);
                        if (initialData.user) {
                            setUser(initialData.user as User);
                            if (initialData.user.locale && ['ar', 'en', 'ku'].includes(initialData.user.locale)) {
                                setLanguage(initialData.user.locale as 'ar' | 'en' | 'ku');
                            }
                            setIsInitialized(true);
                            return;
                        }
                    } catch (error) {
                        console.error('Failed to parse initial user data:', error);
                    }
                }
            }

            try {
                const fetchedUser = await userAuthService.getUser();
                if (fetchedUser) {
                    setUser(fetchedUser);
                    if (fetchedUser.locale && ['ar', 'en', 'ku'].includes(fetchedUser.locale)) {
                        setLanguage(fetchedUser.locale as 'ar' | 'en' | 'ku');
                    }
                }
            } catch (e) {
                console.error('Failed to initialize auth state', e);
            } finally {
                setIsInitialized(true);
            }
        };

        checkAuth();

        const handleLogout = () => setUser(null);
        
        const handleUserUpdate = (event: CustomEvent) => {
            const userData = event.detail as User | null;
            if (userData) {
                setUser(userData);
                if (userData.locale && ['ar', 'en', 'ku'].includes(userData.locale)) {
                    setLanguage(userData.locale as 'ar' | 'en' | 'ku');
                }
            } else {
                setUser(null);
            }
        };
        
        window.addEventListener('auth:logout', handleLogout);
        window.addEventListener('auth:user-update', handleUserUpdate as EventListener);
        
        return () => {
            window.removeEventListener('auth:logout', handleLogout);
            window.removeEventListener('auth:user-update', handleUserUpdate as EventListener);
        };
    }, []);

    const login = async (email: string, password: string, remember: boolean = false, turnstileToken?: string) => {
        setIsLoading(true);
        setError(null);
        try {
            const response = await userAuthService.login(email, password, remember, turnstileToken);
            if (response.success && response.user) {
                setUser(response.user);
                toast.success(__('Login successful'));
                return true;
            }
            if (response.requires2FA) {
                setShow2FAModal(true);
                return false;
            }
            throw new Error('Login failed');
        } catch (err: any) {
            setError(err.message || 'Login failed');
            return false;
        } finally {
            setIsLoading(false);
        }
    };

    const register = async (data: any) => {
        setIsLoading(true);
        setError(null);
        try {
            const response = await userAuthService.register(data);
            if (response.success) {
                if (response.user) setUser(response.user);
                toast.success(__('Registration successful'));
            }
            return response;
        } catch (err: any) {
            setError(err.message || 'Registration failed');
            return { success: false, errors: err.errors };
        } finally {
            setIsLoading(false);
        }
    };

    const verify2FA = async (code: string) => { return false; };

    const resend2FA = async () => {};

    const logout = async () => {
        setIsLoading(true);
        try {
            await userAuthService.logout();
            setUser(null);
            navigate('/login');
            toast.info(__('Logged out successfully'));
        } catch (err: any) {
            console.error('Logout error:', err);
        } finally {
            setIsLoading(false);
        }
    };

    const refreshUser = async () => {
        try {
            const userData = await userAuthService.getUser();
            if (userData) setUser(userData);
        } catch (e) {
            console.error('Failed to refresh user', e);
        }
    };

    return (
        <AuthContext.Provider value={{
            user, isLoading, isInitialized, error,
            login, register, logout, refreshUser,
            show2FAModal, setShow2FAModal, dev2FACode, verify2FA, resend2FA
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (context === undefined) throw new Error('useAuth must be used within an AuthProvider');
    return context;
};
