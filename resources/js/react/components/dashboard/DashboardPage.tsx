import React, { useState, useEffect } from 'react';
import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import { useTheme } from '../../contexts/ThemeContext';
import { dataService, userAuthService } from '../../services/dataService';
import { useCurrentRouteBootstrap } from '../../contexts/RouteBootstrapContext';
import { toast } from 'react-toastify';
import AppLink from '../common/AppLink';
import { loadRouteModule } from '../../routing/routeRegistry';
import NProgress from 'nprogress';
import {
    User, Settings, Shield, LayoutDashboard, BookOpen, Award, Clock,
    Camera, Save, Eye, EyeOff, LogOut, Globe, Moon, Sun, ChevronLeft, ChevronRight, ChevronDown, PlayCircle,
    CheckCircle2, Loader2, X, ShoppingCart
} from 'lucide-react';

type TabKey = 'overview' | 'courses' | 'certificates' | 'orders' | 'profile' | 'security' | 'settings';
type BannerState = { type: 'success' | 'info' | 'error'; message: string } | null;
type StudentProfileForm = {
    university: string;
    department: string;
    degree: string;
    start_year: string;
    graduation_year: string;
    academic_status: string;
    headline: string;
    short_bio: string;
    city: string;
    country: string;
    preferred_role: string;
    preferred_city: string;
    job_available: boolean;
    internship_available: boolean;
    linkedin_url: string;
    github_url: string;
    portfolio_url: string;
    skills: string[];
    projects: string[];
};

const createEmptyStudentProfile = (): StudentProfileForm => ({
    university: '',
    department: '',
    degree: '',
    start_year: '',
    graduation_year: '',
    academic_status: '',
    headline: '',
    short_bio: '',
    city: '',
    country: '',
    preferred_role: '',
    preferred_city: '',
    job_available: false,
    internship_available: false,
    linkedin_url: '',
    github_url: '',
    portfolio_url: '',
    skills: [],
    projects: [],
});

const mapStudentProfile = (profile: any): StudentProfileForm => ({
    ...createEmptyStudentProfile(),
    university: profile?.university || '',
    department: profile?.department || '',
    degree: profile?.degree || '',
    start_year: profile?.start_year ? String(profile.start_year) : '',
    graduation_year: profile?.graduation_year ? String(profile.graduation_year) : '',
    academic_status: profile?.academic_status || '',
    headline: profile?.headline || '',
    short_bio: profile?.short_bio || '',
    city: profile?.city || '',
    country: profile?.country || '',
    preferred_role: profile?.preferred_role || '',
    preferred_city: profile?.preferred_city || '',
    job_available: Boolean(profile?.job_available),
    internship_available: Boolean(profile?.internship_available),
    linkedin_url: profile?.linkedin_url || '',
    github_url: profile?.github_url || '',
    portfolio_url: profile?.portfolio_url || '',
    skills: Array.isArray(profile?.skills) ? profile.skills : [],
    projects: Array.isArray(profile?.projects) ? profile.projects : [],
});

const stringifyList = (items: string[]): string => items.join('\n');
const parseList = (value: string): string[] => value.split(/\r?\n/).map(item => item.trim()).filter(Boolean);

const resolveDashboardTab = (search: string): TabKey => {
    const rawTab = new URLSearchParams(search).get('tab');

    return rawTab === 'courses'
        || rawTab === 'certificates'
        || rawTab === 'orders'
        || rawTab === 'profile'
        || rawTab === 'security'
        || rawTab === 'settings'
        ? rawTab
        : 'overview';
};

const DashboardPage: React.FC = () => {
    const { user, logout, refreshUser } = useAuth();
    const { __, t, prefetchLocale, applyFetchedLocale } = useTranslation();
    const { language, dir, setLanguage } = useLanguage();
    const { theme, toggleTheme } = useTheme();
    const location = useLocation();
    const navigate = useNavigate();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const [activeTab, setActiveTab] = useState<TabKey>(() => resolveDashboardTab(location.search));
    const [banner, setBanner] = useState<BannerState>(null);

    // Profile form
    const [profileName, setProfileName] = useState('');
    const [profileRealName, setProfileRealName] = useState('');
    const [profilePhone, setProfilePhone] = useState('');
    const [profileSaving, setProfileSaving] = useState(false);

    // Password form
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [showCurrentPw, setShowCurrentPw] = useState(false);
    const [showNewPw, setShowNewPw] = useState(false);
    const [passwordSaving, setPasswordSaving] = useState(false);

    // Email form
    const [newEmail, setNewEmail] = useState('');
    const [emailPassword, setEmailPassword] = useState('');
    const [emailOtp, setEmailOtp] = useState('');
    const [emailStep, setEmailStep] = useState<'form' | 'otp'>('form');
    const [emailSaving, setEmailSaving] = useState(false);

    // Stats
    const [stats, setStats] = useState(() => initialBootstrap?.dashboardStats || { courses_enrolled: 0, courses_completed: 0, certificates: 0 });
    const [courses, setCourses] = useState<any[]>(() => initialBootstrap?.dashboardCourses || []);
    const [certificateCards, setCertificateCards] = useState<any[]>(() => initialBootstrap?.dashboardCertificates || []);
    const [coursesLoading, setCoursesLoading] = useState(false);
    const [coursesLoaded, setCoursesLoaded] = useState<boolean>(() => Array.isArray(initialBootstrap?.dashboardCourses));
    const [certificateCardsLoading, setCertificateCardsLoading] = useState(false);
    const [certificateCardsLoaded, setCertificateCardsLoaded] = useState<boolean>(() => Array.isArray(initialBootstrap?.dashboardCertificates));
    
    // Orders
    const [orders, setOrders] = useState<any[]>([]);
    const [ordersLoading, setOrdersLoading] = useState(false);
    const [ordersLoaded, setOrdersLoaded] = useState(false);
    const [verifyingOrderId, setVerifyingOrderId] = useState<number | null>(null);
    
    const [localeSaving, setLocaleSaving] = useState(false);
    const [roleSwitching, setRoleSwitching] = useState(false);
    const [studentProfile, setStudentProfile] = useState<StudentProfileForm>(() => mapStudentProfile(initialBootstrap?.studentProfile));
    const [skillsText, setSkillsText] = useState<string>(() => stringifyList(mapStudentProfile(initialBootstrap?.studentProfile).skills));
    const [projectsText, setProjectsText] = useState<string>(() => stringifyList(mapStudentProfile(initialBootstrap?.studentProfile).projects));
    const [studentProfileLoading, setStudentProfileLoading] = useState(false);
    const [studentProfileLoaded, setStudentProfileLoaded] = useState<boolean>(() => Boolean(initialBootstrap?.studentProfile));
    const [studentProfileSaving, setStudentProfileSaving] = useState(false);

    useEffect(() => {
        if (user) {
            setProfileName(user.name || '');
            setProfileRealName((user as any).real_name || '');
            setProfilePhone((user as any).phone || '');
            if (initialBootstrap?.dashboardStats) {
                setStats(initialBootstrap.dashboardStats);
                return;
            }

            userAuthService.getDashboardStats()
                .then(data => setStats(data || { courses_enrolled: 0, courses_completed: 0, certificates: 0 }))
                .catch(() => {});
        }
    }, [initialBootstrap, user]);

    useEffect(() => {
        setActiveTab(resolveDashboardTab(location.search));
    }, [location.search]);

    useEffect(() => {
        if (Array.isArray(initialBootstrap?.dashboardCourses)) {
            setCourses(initialBootstrap.dashboardCourses);
            setCoursesLoaded(true);
        }
    }, [initialBootstrap]);

    useEffect(() => {
        if (Array.isArray(initialBootstrap?.dashboardCertificates)) {
            setCertificateCards(initialBootstrap.dashboardCertificates);
            setCertificateCardsLoaded(true);
        }
    }, [initialBootstrap]);

    useEffect(() => {
        if (!initialBootstrap?.studentProfile) {
            return;
        }

        const mappedProfile = mapStudentProfile(initialBootstrap.studentProfile);
        setStudentProfile(mappedProfile);
        setSkillsText(stringifyList(mappedProfile.skills));
        setProjectsText(stringifyList(mappedProfile.projects));
        setStudentProfileLoaded(true);
    }, [initialBootstrap]);

    useEffect(() => {
        const params = new URLSearchParams(location.search);

        if (params.get('payment') !== 'success') {
            return;
        }

        setBanner({
            type: 'success',
            message: __('Payment completed successfully. Your courses are now unlocked.'),
        });
        setActiveTab('courses');

        const nextParams = new URLSearchParams(location.search);
        nextParams.set('tab', 'courses');
        nextParams.delete('payment');
        nextParams.delete('transactionId');

        navigate({
            pathname: location.pathname,
            search: nextParams.toString() ? `?${nextParams.toString()}` : '',
        }, { replace: true });
    }, [__, location.pathname, location.search, navigate]);

    useEffect(() => {
        if (!user || activeTab !== 'courses' || coursesLoaded || coursesLoading) {
            return;
        }

        let isMounted = true;
        setCoursesLoading(true);

        userAuthService.getMyCourses()
            .then((data) => {
                if (!isMounted) {
                    return;
                }

                setCourses(data || []);
                setCoursesLoaded(true);
            })
            .catch(() => {
                if (!isMounted) {
                    return;
                }

                setCourses([]);
                setCoursesLoaded(true);
            })
            .finally(() => {
                if (isMounted) {
                    setCoursesLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeTab, user]);

    useEffect(() => {
        if (!user || activeTab !== 'certificates' || certificateCardsLoaded || certificateCardsLoading) {
            return;
        }

        let isMounted = true;
        setCertificateCardsLoading(true);

        userAuthService.getMyCertificates()
            .then((data) => {
                if (!isMounted) {
                    return;
                }

                setCertificateCards(data || []);
                setCertificateCardsLoaded(true);
            })
            .catch(() => {
                if (!isMounted) {
                    return;
                }

                setCertificateCards([]);
                setCertificateCardsLoaded(true);
            })
            .finally(() => {
                if (isMounted) {
                    setCertificateCardsLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeTab, user]);

    useEffect(() => {
        if (!user || activeTab !== 'orders' || ordersLoaded || ordersLoading) {
            return;
        }

        let isMounted = true;
        setOrdersLoading(true);

        userAuthService.getOrders()
            .then((data) => {
                if (!isMounted) return;
                setOrders(data || []);
                setOrdersLoaded(true);
            })
            .catch(() => {
                if (!isMounted) return;
                setOrders([]);
                setOrdersLoaded(true);
            })
            .finally(() => {
                if (isMounted) setOrdersLoading(false);
            });

        return () => {
            isMounted = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeTab, user]);

    useEffect(() => {
        if (!user || activeTab !== 'profile' || studentProfileLoaded || studentProfileLoading) {
            return;
        }

        let isMounted = true;
        setStudentProfileLoading(true);

        userAuthService.getStudentProfile()
            .then((data) => {
                if (!isMounted) {
                    return;
                }

                const mappedProfile = mapStudentProfile(data);
                setStudentProfile(mappedProfile);
                setSkillsText(stringifyList(mappedProfile.skills));
                setProjectsText(stringifyList(mappedProfile.projects));
                setStudentProfileLoaded(true);
            })
            .catch(() => {
                if (!isMounted) {
                    return;
                }

                const mappedProfile = createEmptyStudentProfile();
                setStudentProfile(mappedProfile);
                setSkillsText('');
                setProjectsText('');
                setStudentProfileLoaded(true);
            })
            .finally(() => {
                if (isMounted) {
                    setStudentProfileLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeTab, user]);

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    const setDashboardTab = (tab: TabKey) => {
        setActiveTab(tab);

        const params = new URLSearchParams(location.search);
        params.set('tab', tab);
        params.delete('payment');
        params.delete('transactionId');

        const newUrl = `${location.pathname}?${params.toString()}`;
        window.history.replaceState(null, '', newUrl);
    };

    const formatEnrollmentDate = (value?: string | null) => {
        if (!value) {
            return __('Recently enrolled');
        }

        try {
            return new Intl.DateTimeFormat(
                language === 'ar' ? 'ar-EG' : language === 'ku' ? 'ku' : 'en-US',
                { year: 'numeric', month: 'short', day: 'numeric' }
            ).format(new Date(value));
        } catch {
            return value;
        }
    };

    const tabs: { key: TabKey; label: string; icon: React.ReactNode }[] = [
        { key: 'overview', label: __('Overview'), icon: <LayoutDashboard className="w-5 h-5" /> },
        { key: 'courses', label: __('My Courses'), icon: <BookOpen className="w-5 h-5" /> },
        { key: 'certificates', label: __('My Certificates'), icon: <Award className="w-5 h-5" /> },
        { key: 'orders', label: __('Order History'), icon: <ShoppingCart className="w-5 h-5" /> },
        { key: 'profile', label: __('Profile'), icon: <User className="w-5 h-5" /> },
        { key: 'security', label: __('Security'), icon: <Shield className="w-5 h-5" /> },
        { key: 'settings', label: __('Settings'), icon: <Settings className="w-5 h-5" /> },
    ];

    const handleProfileSave = async () => {
        setProfileSaving(true);
        const formData = new FormData();
        formData.append('name', profileName);
        formData.append('real_name', profileRealName);
        if (profilePhone) formData.append('phone', profilePhone);
        const result = await userAuthService.updateProfile(formData);
        if (result.success) {
            toast.success(result.message || __('Your profile has been updated successfully.'));
            await refreshUser();
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }
        setProfileSaving(false);
    };

    const handleRoleSwitch = async (role: string) => {
        if (!user || roleSwitching || role === user.active_role) {
            return;
        }

        setRoleSwitching(true);
        const result = await userAuthService.switchRole(role);

        if (result.success) {
            await refreshUser();
            toast.success(__('Role switched successfully.'));
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }

        setRoleSwitching(false);
    };

    const handleStudentProfileChange = <K extends keyof StudentProfileForm>(field: K, value: StudentProfileForm[K]) => {
        setStudentProfile((current) => ({
            ...current,
            [field]: value,
        }));
    };

    const handleStudentProfileSave = async () => {
        setStudentProfileSaving(true);

        const payload = {
            ...studentProfile,
            start_year: studentProfile.start_year ? Number(studentProfile.start_year) : null,
            graduation_year: studentProfile.graduation_year ? Number(studentProfile.graduation_year) : null,
            skills: parseList(skillsText),
            projects: parseList(projectsText),
        };

        const result = await userAuthService.updateStudentProfile(payload);

        if (result.success) {
            const mappedProfile = mapStudentProfile(result.data);
            setStudentProfile(mappedProfile);
            setSkillsText(stringifyList(mappedProfile.skills));
            setProjectsText(stringifyList(mappedProfile.projects));
            setStudentProfileLoaded(true);
            toast.success(result.message || __('Profile updated successfully.'));
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }

        setStudentProfileSaving(false);
    };

    const handlePasswordSave = async () => {
        if (newPassword !== confirmPassword) {
            toast.error(__('Passwords do not match.'));
            return;
        }
        setPasswordSaving(true);
        const result = await userAuthService.updatePassword(currentPassword, newPassword);
        if (result.success) {
            toast.success(result.message || __('Your password has been updated successfully.'));
            setCurrentPassword('');
            setNewPassword('');
            setConfirmPassword('');
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }
        setPasswordSaving(false);
    };

    const handleEmailChange = async () => {
        setEmailSaving(true);
        const result = await userAuthService.updateEmail(newEmail, emailPassword);
        if (result.success) {
            toast.success(result.message || __('A verification code has been sent to your new email.'));
            setEmailStep('otp');
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }
        setEmailSaving(false);
    };

    const handleEmailOtpVerify = async () => {
        setEmailSaving(true);
        const result = await userAuthService.verifyEmailOTP(emailOtp);
        if (result.success) {
            toast.success(result.message || __('Your email has been updated successfully.'));
            setNewEmail('');
            setEmailPassword('');
            setEmailOtp('');
            setEmailStep('form');
            await refreshUser();
        } else {
            toast.error(result.error || __('The verification code is invalid or has expired. Please request a new one.'));
        }
        setEmailSaving(false);
    };

    const handleLocaleChange = async (locale: string) => {
        if (locale === language || localeSaving) {
            return;
        }

        setLocaleSaving(true);
        NProgress.start();
        sessionStorage.setItem('language_switch_in_progress', '1');

        try {
            let currentPath = location.pathname;
            const pathSegments = currentPath.split('/').filter(Boolean);

            if (pathSegments.length > 0 && ['ar', 'en', 'ku'].includes(pathSegments[0])) {
                currentPath = '/' + pathSegments.slice(1).join('/');
            }

            if (currentPath === '') {
                currentPath = '/';
            }

            let targetPath = currentPath;
            if (locale !== 'ar') {
                targetPath = `/${locale}${currentPath === '/' ? '' : currentPath}`;
            }

            const targetUrl = `${targetPath}${location.search || ''}${location.hash || ''}`;

            const [prefetched] = await Promise.all([
                prefetchLocale(locale as 'ar' | 'en' | 'ku'),
                loadRouteModule(currentPath).catch(() => { }),
            ]);

            fetch(`/lang/${locale}`, { redirect: 'manual' }).catch(() => { });

            const result = await userAuthService.updateLocale(locale);
            if (!result.success) {
                throw new Error(result.error || 'Locale update failed.');
            }

            applyFetchedLocale(prefetched);
            setLanguage(locale as 'ar' | 'en' | 'ku');
            await refreshUser();

            navigate(targetUrl, { replace: true });
            toast.success(__('Your language preference has been updated.'));
            NProgress.done();
            setTimeout(() => sessionStorage.removeItem('language_switch_in_progress'), 500);
        } catch (error) {
            console.error('Dashboard language switch failed:', error);
            NProgress.done();
            sessionStorage.removeItem('language_switch_in_progress');
            toast.error(__('Something went wrong. Please try again.'));
        } finally {
            setLocaleSaving(false);
        }
    };

    const handleLogout = async () => {
        await logout();
    };

    const userInitials = user.name
        ? user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
        : '?';
    const completedCoursesCount = courses.filter((course) => (course.progress_percent ?? 0) === 100).length;
    const readyCertificatesCount = certificateCardsLoaded
        ? certificateCards.filter((card) => !!card?.certificate?.available).length
        : courses.filter((course) => !!course.certificate_available).length;

    // ── Tab Content Renderers ──

    const handleVerifyOrder = async (transactionId: number) => {
        setVerifyingOrderId(transactionId);
        try {
            const res = await userAuthService.verifyPayment(transactionId);
            if (res.success) {
                setTimeout(() => {
                    toast.success(__('Payment verified successfully.'));
                }, 0);
                setOrdersLoading(true);
                const updatedOrders = await userAuthService.getOrders();
                setOrders(updatedOrders);
                setOrdersLoaded(true);
                setOrdersLoading(false);
            } else {
                setTimeout(() => {
                    toast.error(res.error || __('Verification failed.'));
                }, 0);
            }
        } catch (e) {
            setTimeout(() => {
                toast.error(__('Something went wrong.'));
            }, 0);
        } finally {
            setVerifyingOrderId(null);
        }
    };

    const renderOrders = () => (
        <div className="space-y-6 animate-fade-in">
            <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">{__('Order History')}</h2>
            
            {(ordersLoading && !ordersLoaded) ? (
                <div className="flex justify-center items-center py-12">
                    <Loader2 className="w-8 h-8 animate-spin text-brand-500" />
                </div>
            ) : (!ordersLoaded && !ordersLoading) ? (
                <div className="flex justify-center items-center py-12">
                    <Loader2 className="w-8 h-8 animate-spin text-brand-500" />
                </div>
            ) : orders.length === 0 ? (
                <div className="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-100 dark:border-gray-700/50 text-center text-gray-500 dark:text-gray-400">
                    <ShoppingCart className="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>{__('You have no orders yet.')}</p>
                </div>
            ) : (
                <div className="grid gap-4">
                    {orders.map((order) => {
                        const isPending = order.status === 'pending';
                        const orderDate = new Date(order.created_at);
                        const isLessThenAnHour = (Date.now() - orderDate.getTime()) < 3600000;
                        // Need transaction_id to reverify
                        const showReverify = isPending && isLessThenAnHour && order.transaction_id;

                        // simple formatting for numbers
                        const amt = new Intl.NumberFormat().format(order.total_amount);

                        return (
                            <div key={order.id} className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center hover:shadow-sm transition-shadow">
                                <div className="space-y-2 flex-grow">
                                    <div className="flex items-center gap-3">
                                        <span className="font-mono text-sm text-gray-500 dark:text-gray-400">#{order.order_ref}</span>
                                        <span className={`px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            order.status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' :
                                            order.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' :
                                            'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
                                        }`}>
                                            {__(order.status.charAt(0).toUpperCase() + order.status.slice(1))}
                                        </span>
                                    </div>
                                    <div className="text-gray-900 dark:text-white font-medium">
                                        {Array.isArray(order.items) ? order.items.map((i: any) => {
                                            const rawTitle = i.course?.title || i.title;
                                            if (!rawTitle) return '';
                                            if (typeof rawTitle === 'string') return rawTitle;
                                            return rawTitle[language] || rawTitle['en'] || rawTitle['ar'] || Object.values(rawTitle)[0] || '';
                                        }).filter(Boolean).join(', ') : ''}
                                    </div>
                                    <div className="text-sm text-gray-500 dark:text-gray-400 flex flex-wrap gap-4">
                                        <span>{new Intl.DateTimeFormat(language === 'ar' ? 'ar-EG' : language === 'ku' ? 'ku' : 'en-US', { dateStyle: 'medium', timeStyle: 'short' }).format(orderDate)}</span>
                                        {order.payment_method && <span className="capitalize">{order.payment_method}</span>}
                                    </div>
                                </div>
                                <div className="flex flex-col sm:items-end gap-3 w-full sm:w-auto">
                                    <div className="text-xl font-bold text-gray-900 dark:text-white shrink-0">
                                        {amt} {__('IQD')}
                                    </div>
                                    {showReverify && (
                                        <button 
                                            disabled={verifyingOrderId === order.transaction_id}
                                            onClick={() => handleVerifyOrder(order.transaction_id)}
                                            className="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:hover:bg-brand-900/50 dark:text-brand-400 rounded-lg text-sm font-medium transition-colors m-auto sm:m-0 w-full sm:w-auto flex items-center justify-center gap-2 disabled:opacity-50"
                                        >
                                            {verifyingOrderId === order.transaction_id && <Loader2 className="w-4 h-4 animate-spin" />}
                                            {__('Re-verify')}
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );

    const renderOverview = () => (
        <div className="space-y-8 animate-fade-in">
            {/* Welcome Banner */}
            <div className="rounded-2xl bg-white dark:bg-gray-800 border-l-4 border-brand-500 shadow-sm border-t border-r border-b border-gray-100 dark:border-gray-700/50 p-6 sm:p-8 flex items-center">
                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center text-2xl sm:text-3xl font-bold border border-brand-100 dark:border-brand-800 shrink-0">
                        {userInitials}
                    </div>
                    <div>
                        <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                            {__('Welcome back')}, {user.name}! 👋
                        </h2>
                        <p className="text-gray-500 dark:text-gray-400 mt-1 text-sm sm:text-base">{__('Here is an overview of your learning journey.')}</p>
                    </div>
                </div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                {[
                    { label: __('Courses Enrolled'), value: stats.courses_enrolled, icon: <BookOpen className="w-8 h-8" />, color: 'text-blue-500 dark:text-blue-400' },
                    { label: __('Courses Completed'), value: stats.courses_completed, icon: <Award className="w-8 h-8" />, color: 'text-emerald-500 dark:text-emerald-400' },
                    { label: __('Certificates'), value: stats.certificates, icon: <Award className="w-8 h-8" />, color: 'text-amber-500 dark:text-amber-400' },
                ].map((stat, i) => (
                    <div key={i} className="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/50 shadow-sm flex items-center gap-5 hover:-translate-y-0.5 transition-transform duration-300">
                        <div className={`flex items-center justify-center ${stat.color}`}>
                            {stat.icon}
                        </div>
                        <div>
                            <p className="text-3xl font-bold text-gray-900 dark:text-white">{stat.value}</p>
                            <p className="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{stat.label}</p>
                        </div>
                    </div>
                ))}
            </div>

            {/* Quick Actions */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">{__('Quick Actions')}</h3>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button onClick={() => setDashboardTab('courses')} className="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors group text-start">
                        <div className="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <BookOpen className="w-5 h-5" />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900 dark:text-white">{__('My Courses')}</p>
                            <p className="text-xs text-gray-500 dark:text-gray-400">{__('View your enrolled courses')}</p>
                        </div>
                    </button>
                    <button onClick={() => setDashboardTab('profile')} className="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors group text-start">
                        <div className="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 group-hover:scale-110 transition-transform">
                            <User className="w-5 h-5" />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900 dark:text-white">{__('Edit Profile')}</p>
                            <p className="text-xs text-gray-500 dark:text-gray-400">{__('Update your personal information')}</p>
                        </div>
                    </button>
                    <button onClick={() => setDashboardTab('security')} className="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors group text-start">
                        <div className="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                            <Shield className="w-5 h-5" />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900 dark:text-white">{__('Security Settings')}</p>
                            <p className="text-xs text-gray-500 dark:text-gray-400">{__('Manage your password and security')}</p>
                        </div>
                    </button>
                    <button onClick={() => setDashboardTab('certificates')} className="flex items-center gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors group text-start">
                        <div className="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                            <Award className="w-5 h-5" />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900 dark:text-white">{__('My Certificates')}</p>
                            <p className="text-xs text-gray-500 dark:text-gray-400">{__('See locked and ready certificates for your enrolled courses')}</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    );

    const renderCertificatePreview = (certificate: any, title: any) => {
        const template = certificate?.template;
        const area = template?.name_area;
        const style = template?.style;
        const previewName = certificate?.student_name || profileRealName || (user as any)?.real_name || __('Your Real Name');

        return (
            <div className="relative aspect-[16/10] overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900">
                {template?.preview_url ? (
                    <img
                        src={template.preview_url}
                        alt={typeof title === 'string' ? title : __('Certificate Template')}
                        className="w-full h-full object-cover"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-gray-400">
                        <Award className="w-10 h-10" />
                    </div>
                )}
                {template?.preview_url && area && (
                    <div
                        className="absolute flex items-center justify-center px-2 text-center font-semibold pointer-events-none"
                        style={{
                            left: `${Math.min(area.x1, area.x2) * 100}%`,
                            top: `${Math.min(area.y1, area.y2) * 100}%`,
                            width: `${Math.max(Math.abs(area.x2 - area.x1), 0.01) * 100}%`,
                            height: `${Math.max(Math.abs(area.y2 - area.y1), 0.01) * 100}%`,
                            color: style?.text_color || '#111827',
                            fontSize: `${Math.max(Math.min((style?.font_size || 42) / 2.5, 26), 14)}px`,
                            fontFamily: style?.font_family || 'inherit',
                            textAlign: style?.text_align || 'center',
                            opacity: certificate?.requires_real_name ? 0.55 : 0.85,
                        }}
                    >
                        {previewName}
                    </div>
                )}
            </div>
        );
    };

    const renderCertificates = () => (
        <div className="space-y-6 animate-fade-in">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 shadow-[0_20px_70px_-48px_rgba(15,23,42,0.42)]">
                <div className="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-6">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-amber-200/60 dark:border-amber-800/70 bg-amber-50/80 dark:bg-amber-900/20 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 mb-3">
                            <Award className="w-4 h-4" />
                            {__('Certificate Center')}
                        </div>
                        <h3 className="text-2xl font-bold text-gray-900 dark:text-white">{__('My Certificates')}</h3>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">{__('Certificates appear here as soon as you enroll. Finish the course and set your real name to unlock the final download.')}</p>
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="min-w-[120px] rounded-xl border border-gray-100 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-900/40 px-4 py-3">
                            <p className="text-xs text-gray-500 dark:text-gray-400">{__('Visible')}</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">{certificateCards.length}</p>
                        </div>
                        <div className="min-w-[120px] rounded-xl border border-amber-100 dark:border-amber-800/60 bg-amber-50/70 dark:bg-amber-900/20 px-4 py-3">
                            <p className="text-xs text-amber-700 dark:text-amber-300">{__('Ready')}</p>
                            <p className="text-2xl font-bold text-amber-700 dark:text-amber-200 mt-1">{readyCertificatesCount}</p>
                        </div>
                    </div>
                </div>

                {certificateCardsLoading ? (
                    <div className="flex items-center justify-center py-20">
                        <Loader2 className="w-8 h-8 animate-spin text-brand-600" />
                    </div>
                ) : certificateCards.length > 0 ? (
                    <div className="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-5">
                        {certificateCards.map((card) => {
                            const certificate = card.certificate || {};
                            const isReady = !!certificate.available;
                            const isLockedByName = certificate.status === 'locked_real_name';

                            return (
                                <div
                                    key={card.course_id}
                                    className="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-900/30"
                                >
                                    <div className="p-4">
                                        {renderCertificatePreview(certificate, t(card.course_title))}
                                    </div>
                                    <div className="px-5 pb-5 space-y-4">
                                        <div className="flex items-center justify-between gap-3 text-xs">
                                            <span className={`inline-flex items-center gap-2 rounded-full px-3 py-1 font-semibold ${isReady ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'}`}>
                                                {isReady ? <CheckCircle2 className="w-4 h-4" /> : <Clock className="w-4 h-4" />}
                                                {isReady ? __('Ready') : __('Locked')}
                                            </span>
                                            <span className="text-gray-500 dark:text-gray-400">{card.progress_percent ?? 0}%</span>
                                        </div>

                                        <div>
                                            <h4 className="text-lg font-bold text-gray-900 dark:text-white line-clamp-2">
                                                {t(card.course_title)}
                                            </h4>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                {certificate.locked_reason || (isReady ? __('Your certificate is ready to download.') : __('This certificate is currently locked.'))}
                                            </p>
                                        </div>

                                        <div className="flex flex-wrap gap-3">
                                            {isReady ? (
                                                <a
                                                    href={certificate.download_url}
                                                    className="inline-flex flex-1 min-w-[180px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-medium text-white"
                                                >
                                                    <Award className="w-4 h-4" />
                                                    {__('Download Certificate')}
                                                </a>
                                            ) : isLockedByName ? (
                                                <button
                                                    type="button"
                                                    onClick={() => setDashboardTab('profile')}
                                                    className="inline-flex flex-1 min-w-[180px] items-center justify-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-2.5 text-sm font-medium text-brand-700 dark:border-brand-800 dark:bg-brand-900/20 dark:text-brand-300"
                                                >
                                                    <User className="w-4 h-4" />
                                                    {__('Set Real Name')}
                                                </button>
                                            ) : (
                                                <AppLink
                                                    to={card.continue_url || `/learn/${card.course_slug}`}
                                                    className="inline-flex flex-1 min-w-[180px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 px-4 py-2.5 text-sm font-medium text-white"
                                                >
                                                    <PlayCircle className="w-4 h-4" />
                                                    {__('Continue Learning')}
                                                </AppLink>
                                            )}
                                            <AppLink
                                                to={`/courses/${card.course_slug}`}
                                                className="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-200"
                                            >
                                                <BookOpen className="w-4 h-4" />
                                                {__('Course Details')}
                                            </AppLink>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-10 text-center">
                        <div className="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-4">
                            <Award className="w-8 h-8 text-gray-400" />
                        </div>
                        <h4 className="text-lg font-bold text-gray-900 dark:text-white mb-2">{__('No certificates yet')}</h4>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mb-6">{__('Once you enroll in a course, its certificate will appear here immediately.')}</p>
                        <AppLink
                            to="/courses"
                            className="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 text-white font-medium"
                        >
                            <BookOpen className="w-4 h-4" />
                            {__('Explore Courses')}
                        </AppLink>
                    </div>
                )}
            </div>
        </div>
    );

    const renderCourses = () => (
        <div className="space-y-6 animate-fade-in">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 shadow-[0_20px_70px_-48px_rgba(15,23,42,0.42)]">
                <div className="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-6">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-brand-200/60 dark:border-brand-800/70 bg-brand-50/70 dark:bg-brand-900/20 px-3 py-1 text-xs font-semibold text-brand-700 dark:text-brand-300 mb-3">
                            <BookOpen className="w-4 h-4" />
                            {__('Learning Hub')}
                        </div>
                        <h3 className="text-2xl font-bold text-gray-900 dark:text-white">{__('My Courses')}</h3>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">{__('Track your progress, continue where you left off, and download your certificate once you finish a course successfully.')}</p>
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="min-w-[120px] rounded-xl border border-gray-100 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-900/40 px-4 py-3">
                            <p className="text-xs text-gray-500 dark:text-gray-400">{__('Enrolled')}</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">{courses.length}</p>
                        </div>
                        <div className="min-w-[120px] rounded-xl border border-emerald-100 dark:border-emerald-800/60 bg-emerald-50/70 dark:bg-emerald-900/20 px-4 py-3">
                            <p className="text-xs text-emerald-700 dark:text-emerald-300">{__('Completed')}</p>
                            <p className="text-2xl font-bold text-emerald-700 dark:text-emerald-200 mt-1">{completedCoursesCount}</p>
                        </div>
                        <div className="min-w-[120px] rounded-xl border border-amber-100 dark:border-amber-800/60 bg-amber-50/70 dark:bg-amber-900/20 px-4 py-3">
                            <p className="text-xs text-amber-700 dark:text-amber-300">{__('Certificates')}</p>
                            <p className="text-2xl font-bold text-amber-700 dark:text-amber-200 mt-1">{readyCertificatesCount}</p>
                        </div>
                        <AppLink
                            to="/courses"
                            className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:border-brand-400 transition-colors"
                        >
                            <BookOpen className="w-4 h-4" />
                            {__('Browse Courses')}
                        </AppLink>
                    </div>
                </div>

                {coursesLoading ? (
                    <div className="flex items-center justify-center py-20">
                        <Loader2 className="w-8 h-8 animate-spin text-brand-600" />
                    </div>
                ) : courses.length > 0 ? (
                    <div className="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-5">
                        {courses.map((course) => (
                            <div
                                key={course.id}
                                className="group overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-900/30 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-xl hover:-translate-y-0.5 transition-all"
                            >
                                <div className="relative aspect-[16/9] overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    {course.image ? (
                                        <img
                                            src={course.image}
                                            alt={course.title?.en || course.title?.ar || __('Course image')}
                                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center text-gray-400">
                                            <BookOpen className="w-10 h-10" />
                                        </div>
                                    )}
                                    <div className="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-gray-950/75 to-transparent" />
                                    <div className="absolute top-4 inset-x-4 flex items-center justify-between gap-3">
                                        <div className="inline-flex items-center gap-2 rounded-full bg-emerald-50/95 dark:bg-emerald-900/85 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-200 shadow-sm">
                                            <CheckCircle2 className="w-4 h-4" />
                                            {__('Enrolled')}
                                        </div>
                                        <div className="rounded-xl bg-white/90 dark:bg-gray-950/80 px-3 py-1 text-xs font-bold text-brand-600 dark:text-brand-300">
                                            {course.progress_percent ?? 0}% {__('Complete')}
                                        </div>
                                    </div>
                                    {course.certificate_available && (
                                        <div className="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-full bg-amber-50/95 dark:bg-amber-900/85 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-200 shadow-sm">
                                            <Award className="w-4 h-4" />
                                            {__('Certificate Ready')}
                                        </div>
                                    )}
                                </div>

                                <div className="p-5 space-y-4">
                                    <div>
                                        <div className="flex items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400 mb-3">
                                            <span>
                                                {course.certificate_status === 'locked_real_name'
                                                    ? __('Set your real name to unlock your certificate')
                                                    : course.certificate_available
                                                        ? __('Course completed successfully')
                                                        : __('Continue where you left off')}
                                            </span>
                                            <span>{course.completed_at ? formatEnrollmentDate(course.completed_at) : formatEnrollmentDate(course.enrolled_at)}</span>
                                        </div>
                                        <h4 className="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                            {course.title ? t(course.title) : __('Untitled course')}
                                        </h4>
                                        <p className="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {course.instructor?.name ? t(course.instructor.name) : __('Instructor')}
                                        </p>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 px-4 py-3">
                                            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-500">{__('Duration')}</p>
                                            <p className="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                <Clock className="w-4 h-4 text-brand-500" />
                                                {course.duration_hours} {__('Hours')}
                                            </p>
                                        </div>
                                        <div className="rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 px-4 py-3">
                                            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-500">{__('Progress')}</p>
                                            <p className="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                                {course.progress_percent ?? 0}% {__('Complete')}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="rounded-xl bg-white/80 dark:bg-gray-900/70 border border-gray-100 dark:border-gray-800 px-4 py-4">
                                        <div className="flex items-center justify-between gap-3 text-xs font-semibold mb-2">
                                            <span className="text-gray-500 dark:text-gray-400">{__('Learning Progress')}</span>
                                            <span className="text-brand-600 dark:text-brand-400">{course.progress_percent ?? 0}%</span>
                                        </div>
                                        <div className="h-2.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div
                                                className="h-full rounded-full bg-gradient-to-r from-brand-500 via-brand-600 to-emerald-500"
                                                style={{ width: `${course.progress_percent ?? 0}%` }}
                                            />
                                        </div>
                                        {course.certificate_status === 'locked_real_name' ? (
                                            <p className="mt-3 text-xs text-brand-700 dark:text-brand-300">{__('Add your real name in the profile tab to enable certificate download.')}</p>
                                        ) : course.certificate_available ? (
                                            <p className="mt-3 text-xs text-amber-700 dark:text-amber-300">{__('Your certificate is now ready to download.')}</p>
                                        ) : (
                                            <p className="mt-3 text-xs text-gray-500 dark:text-gray-400">{__('Complete the lessons and pass the assessments to unlock your certificate.')}</p>
                                        )}
                                    </div>

                                    <div className="flex flex-wrap gap-3 pt-1">
                                        <AppLink
                                            to={course.continue_url || `/learn/${course.slug}`}
                                            className="inline-flex flex-1 min-w-[180px] items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-medium"
                                        >
                                            <PlayCircle className="w-4 h-4" />
                                            {__('Continue Learning')}
                                        </AppLink>
                                        <AppLink
                                            to={`/courses/${course.slug}`}
                                            className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200"
                                        >
                                            <BookOpen className="w-4 h-4" />
                                            {__('Course Details')}
                                        </AppLink>
                                        {course.certificate_available ? (
                                            <a
                                                href={course.certificate_url || dataService.getCourseCertificateUrl(course.id)}
                                                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-sm font-medium text-amber-700 dark:text-amber-200"
                                            >
                                                <Award className="w-4 h-4" />
                                                {__('Download Certificate')}
                                            </a>
                                        ) : course.certificate_status === 'locked_real_name' ? (
                                            <button
                                                type="button"
                                                onClick={() => setDashboardTab('profile')}
                                                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-brand-200 dark:border-brand-800 bg-brand-50 dark:bg-brand-900/20 text-sm font-medium text-brand-700 dark:text-brand-300"
                                            >
                                                <User className="w-4 h-4" />
                                                {__('Set Real Name')}
                                            </button>
                                        ) : null}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-10 text-center">
                        <div className="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-4">
                            <BookOpen className="w-8 h-8 text-gray-400" />
                        </div>
                        <h4 className="text-lg font-bold text-gray-900 dark:text-white mb-2">{__('No enrolled courses yet')}</h4>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mb-6">{__('Once you complete checkout successfully, your courses will appear here.')}</p>
                        <AppLink
                            to="/courses"
                            className="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 text-white font-medium hover:from-brand-700 hover:to-brand-800 transition-all"
                        >
                            <BookOpen className="w-4 h-4" />
                            {__('Explore Courses')}
                        </AppLink>
                    </div>
                )}
            </div>
        </div>
    );

    const renderProfile = () => (
        <div className="space-y-6 animate-fade-in">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <div className="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100 dark:border-gray-700">
                    <div className="w-20 h-20 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold border-4 border-white dark:border-gray-700 shadow-lg">
                        {userInitials}
                    </div>
                    <div>
                        <p className="font-semibold text-gray-900 dark:text-white text-lg">{user.name}</p>
                        <p className="text-sm text-gray-500 dark:text-gray-400">{user.email}</p>
                    </div>
                </div>

                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6">{__('Account Information')}</h3>

                <div className="space-y-5">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Full Name')}</label>
                        <input
                            type="text"
                            value={profileName}
                            onChange={e => setProfileName(e.target.value)}
                            className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Real Name')}</label>
                        <input
                            type="text"
                            value={profileRealName}
                            onChange={e => setProfileRealName(e.target.value)}
                            placeholder={__('Enter the exact name that should appear on your certificates')}
                            className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                        />
                        <p className="text-xs text-gray-400 dark:text-gray-500 mt-1">{__('This formal name will be rendered on your course certificates when you download them.')}</p>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Email')}</label>
                        <input
                            type="email"
                            value={user.email}
                            disabled
                            className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                        />
                        <p className="text-xs text-gray-400 dark:text-gray-500 mt-1">{__('To change your email, go to the Security tab.')}</p>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Phone Number')}</label>
                        <input
                            type="tel"
                            value={profilePhone}
                            onChange={e => setProfilePhone(e.target.value)}
                            placeholder={__('Enter your phone number')}
                            className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                        />
                    </div>
                    <button
                        onClick={handleProfileSave}
                        disabled={profileSaving}
                        className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-medium hover:from-brand-700 hover:to-brand-800 transition-all disabled:opacity-50 shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40"
                    >
                        <Save className="w-4 h-4" />
                        {profileSaving ? __('Saving...') : __('Save Account')}
                    </button>
                </div>
            </div>

            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <div className="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h3 className="text-xl font-bold text-gray-900 dark:text-white">{__('Student Profile')}</h3>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{__('Add the academic, career, and portfolio details that should travel with your student account.')}</p>
                    </div>
                    {studentProfileLoading ? <Loader2 className="w-5 h-5 animate-spin text-brand-600" /> : null}
                </div>

                <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('University')}</label>
                            <input
                                type="text"
                                value={studentProfile.university}
                                onChange={e => handleStudentProfileChange('university', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Department')}</label>
                            <input
                                type="text"
                                value={studentProfile.department}
                                onChange={e => handleStudentProfileChange('department', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Degree')}</label>
                            <input
                                type="text"
                                value={studentProfile.degree}
                                onChange={e => handleStudentProfileChange('degree', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Academic Status')}</label>
                            <input
                                type="text"
                                value={studentProfile.academic_status}
                                onChange={e => handleStudentProfileChange('academic_status', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Start Year')}</label>
                            <input
                                type="number"
                                min="1950"
                                max="2100"
                                value={studentProfile.start_year}
                                onChange={e => handleStudentProfileChange('start_year', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Graduation Year')}</label>
                            <input
                                type="number"
                                min="1950"
                                max="2100"
                                value={studentProfile.graduation_year}
                                onChange={e => handleStudentProfileChange('graduation_year', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Headline')}</label>
                            <input
                                type="text"
                                value={studentProfile.headline}
                                onChange={e => handleStudentProfileChange('headline', e.target.value)}
                                placeholder={__('Example: Frontend Developer in Training')}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Preferred Role')}</label>
                            <input
                                type="text"
                                value={studentProfile.preferred_role}
                                onChange={e => handleStudentProfileChange('preferred_role', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('City')}</label>
                            <input
                                type="text"
                                value={studentProfile.city}
                                onChange={e => handleStudentProfileChange('city', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Country')}</label>
                            <input
                                type="text"
                                value={studentProfile.country}
                                onChange={e => handleStudentProfileChange('country', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Preferred City')}</label>
                            <input
                                type="text"
                                value={studentProfile.preferred_city}
                                onChange={e => handleStudentProfileChange('preferred_city', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Short Bio')}</label>
                        <textarea
                            value={studentProfile.short_bio}
                            onChange={e => handleStudentProfileChange('short_bio', e.target.value)}
                            rows={4}
                            className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                        />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('LinkedIn URL')}</label>
                            <input
                                type="url"
                                value={studentProfile.linkedin_url}
                                onChange={e => handleStudentProfileChange('linkedin_url', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('GitHub URL')}</label>
                            <input
                                type="url"
                                value={studentProfile.github_url}
                                onChange={e => handleStudentProfileChange('github_url', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Portfolio URL')}</label>
                            <input
                                type="url"
                                value={studentProfile.portfolio_url}
                                onChange={e => handleStudentProfileChange('portfolio_url', e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Skills')}</label>
                            <textarea
                                value={skillsText}
                                onChange={e => setSkillsText(e.target.value)}
                                rows={5}
                                placeholder={__('One skill per line')}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Projects')}</label>
                            <textarea
                                value={projectsText}
                                onChange={e => setProjectsText(e.target.value)}
                                rows={5}
                                placeholder={__('One project per line')}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label className="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/30 px-4 py-3">
                            <input
                                type="checkbox"
                                checked={studentProfile.job_available}
                                onChange={e => handleStudentProfileChange('job_available', e.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                            />
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">{__('Open to full-time jobs')}</span>
                        </label>
                        <label className="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/30 px-4 py-3">
                            <input
                                type="checkbox"
                                checked={studentProfile.internship_available}
                                onChange={e => handleStudentProfileChange('internship_available', e.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                            />
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">{__('Open to internships')}</span>
                        </label>
                    </div>

                    <button
                        onClick={handleStudentProfileSave}
                        disabled={studentProfileSaving}
                        className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-medium hover:from-brand-700 hover:to-brand-800 transition-all disabled:opacity-50 shadow-lg shadow-brand-500/25"
                    >
                        <Save className="w-4 h-4" />
                        {studentProfileSaving ? __('Saving...') : __('Save Student Profile')}
                    </button>
                </div>
            </div>
        </div>
    );

    const renderSecurity = () => (
        <div className="space-y-6 animate-fade-in">
            {/* Change Password */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6">{__('Change Password')}</h3>
                <div className="space-y-5">
                    <div className="relative">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Current Password')}</label>
                        <div className="relative">
                            <input
                                type={showCurrentPw ? 'text' : 'password'}
                                value={currentPassword}
                                onChange={e => setCurrentPassword(e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                            <button type="button" onClick={() => setShowCurrentPw(!showCurrentPw)} className="absolute top-1/2 -translate-y-1/2 end-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                {showCurrentPw ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                            </button>
                        </div>
                    </div>
                    <div className="relative">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('New Password')}</label>
                        <div className="relative">
                            <input
                                type={showNewPw ? 'text' : 'password'}
                                value={newPassword}
                                onChange={e => setNewPassword(e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                            <button type="button" onClick={() => setShowNewPw(!showNewPw)} className="absolute top-1/2 -translate-y-1/2 end-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                {showNewPw ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Confirm New Password')}</label>
                        <input
                            type="password"
                            value={confirmPassword}
                            onChange={e => setConfirmPassword(e.target.value)}
                            className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                        />
                    </div>
                    <button
                        onClick={handlePasswordSave}
                        disabled={passwordSaving || !currentPassword || !newPassword || !confirmPassword}
                        className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-medium hover:from-brand-700 hover:to-brand-800 transition-all disabled:opacity-50 shadow-lg shadow-brand-500/25"
                    >
                        <Shield className="w-4 h-4" />
                        {passwordSaving ? __('Saving...') : __('Update Password')}
                    </button>
                </div>
            </div>

            {/* Change Email */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2">{__('Change Email')}</h3>
                <p className="text-sm text-gray-500 dark:text-gray-400 mb-6">{__('Current email')}: <span className="font-medium text-gray-700 dark:text-gray-300">{user.email}</span></p>

                {emailStep === 'form' ? (
                    <div className="space-y-5">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('New Email')}</label>
                            <input
                                type="email"
                                value={newEmail}
                                onChange={e => setNewEmail(e.target.value)}
                                placeholder={__('Enter your new email')}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Current Password')}</label>
                            <input
                                type="password"
                                value={emailPassword}
                                onChange={e => setEmailPassword(e.target.value)}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all"
                            />
                        </div>
                        <button
                            onClick={handleEmailChange}
                            disabled={emailSaving || !newEmail || !emailPassword}
                            className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-medium hover:from-brand-700 hover:to-brand-800 transition-all disabled:opacity-50 shadow-lg shadow-brand-500/25"
                        >
                            {emailSaving ? __('Sending...') : __('Send Verification Code')}
                        </button>
                    </div>
                ) : (
                    <div className="space-y-5">
                        <div className="p-4 bg-brand-50 dark:bg-brand-900/20 rounded-xl border border-brand-200 dark:border-brand-800">
                            <p className="text-sm text-brand-700 dark:text-brand-300">{__('A verification code has been sent to your new email.')}</p>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Verification Code')}</label>
                            <input
                                type="text"
                                value={emailOtp}
                                onChange={e => setEmailOtp(e.target.value)}
                                placeholder="000000"
                                maxLength={6}
                                className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition-all text-center text-2xl tracking-[0.3em] font-mono"
                            />
                        </div>
                        <div className="flex gap-3">
                            <button
                                onClick={handleEmailOtpVerify}
                                disabled={emailSaving || emailOtp.length !== 6}
                                className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-medium hover:from-brand-700 hover:to-brand-800 transition-all disabled:opacity-50 shadow-lg shadow-brand-500/25"
                            >
                                {emailSaving ? __('Verifying...') : __('Verify & Update')}
                            </button>
                            <button
                                onClick={() => { setEmailStep('form'); setEmailOtp(''); }}
                                className="px-6 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-xl font-medium transition-colors"
                            >
                                {__('Cancel')}
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );

    const renderSettings = () => (
        <div className="space-y-6 animate-fade-in">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <Settings className="w-5 h-5 text-brand-600" />
                    {__('Preferences')}
                </h3>

                <div className="space-y-5">
                    <div className="flex flex-col gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="flex items-center gap-2 text-gray-900 dark:text-white font-semibold">
                                <Globe className="w-4 h-4 text-brand-600" />
                                {__('Language')}
                            </div>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{__('Choose the language used across your dashboard and learning pages.')}</p>
                        </div>

                        <div className="relative w-full sm:w-[240px]">
                            <select
                                value={language}
                                onChange={(e) => handleLocaleChange(e.target.value)}
                                disabled={localeSaving}
                                className="w-full appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 pe-11 text-sm font-medium text-gray-900 dark:text-white outline-none transition-all focus:border-brand-400 focus:ring-2 focus:ring-brand-500/20 disabled:opacity-70"
                            >
                                <option value="ar">العربية</option>
                                <option value="en">English</option>
                                <option value="ku">کوردی</option>
                            </select>
                            <div className="pointer-events-none absolute inset-y-0 end-3 flex items-center gap-2 text-gray-400 dark:text-gray-500">
                                {localeSaving ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
                                <ChevronDown className="w-4 h-4" />
                            </div>
                        </div>
                    </div>

                    <button
                        onClick={toggleTheme}
                        className="flex items-center justify-between w-full p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 hover:border-brand-300 dark:hover:border-brand-700 transition-all"
                    >
                        <div className="flex items-center gap-3">
                            {theme === 'dark' ? <Moon className="w-5 h-5 text-indigo-400" /> : <Sun className="w-5 h-5 text-amber-500" />}
                            <div className="text-start">
                                <p className="font-medium text-gray-700 dark:text-gray-300">
                                    {__('Appearance')}
                                </p>
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {theme === 'dark' ? __('Dark Mode') : __('Light Mode')}
                                </p>
                            </div>
                        </div>
                        <div className={`w-12 h-7 rounded-full relative transition-colors ${theme === 'dark' ? 'bg-brand-600' : 'bg-gray-300'}`}>
                            <div className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-all ${theme === 'dark' ? 'end-0.5' : 'start-0.5'}`} />
                        </div>
                    </button>
                </div>
            </div>

            {/* Logout */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-red-100 dark:border-red-900/30 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2">{__('Danger Zone')}</h3>
                <p className="text-sm text-gray-500 dark:text-gray-400 mb-4">{__('Logging out will end your current session.')}</p>
                <button
                    onClick={handleLogout}
                    className="flex items-center gap-2 px-6 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors border border-red-200 dark:border-red-800"
                >
                    <LogOut className="w-4 h-4" />
                    {__('Log Out')}
                </button>
            </div>
        </div>
    );

    const tabContent: Record<TabKey, React.ReactNode> = {
        overview: renderOverview(),
        courses: renderCourses(),
        certificates: renderCertificates(),
        orders: renderOrders(),
        profile: renderProfile(),
        security: renderSecurity(),
        settings: renderSettings(),
    };

    return (
        <div className="min-h-screen py-8">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Page Title */}
                <div className="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{__('My Dashboard')}</h1>
                        <p className="text-gray-500 dark:text-gray-400 mt-1">{__('Manage your account and learning progress')}</p>
                    </div>

                    {Array.isArray(user.available_roles) && user.available_roles.length > 1 ? (
                        <div className="w-full lg:w-[280px]">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{__('Active Role')}</label>
                            <div className="relative">
                                <select
                                    value={user.active_role || 'student'}
                                    onChange={(e) => void handleRoleSwitch(e.target.value)}
                                    disabled={roleSwitching}
                                    className="w-full appearance-none rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 pe-11 text-sm font-medium text-gray-900 dark:text-white outline-none transition-all focus:border-brand-400 focus:ring-2 focus:ring-brand-500/20 disabled:opacity-70"
                                >
                                    {user.available_roles.map((role) => (
                                        <option key={role} value={role}>
                                            {__(role === 'hr' ? 'HR' : role === 'organization' ? 'Organization' : 'Student')}
                                        </option>
                                    ))}
                                </select>
                                <div className="pointer-events-none absolute inset-y-0 end-3 flex items-center gap-2 text-gray-400 dark:text-gray-500">
                                    {roleSwitching ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
                                    <ChevronDown className="w-4 h-4" />
                                </div>
                            </div>
                        </div>
                    ) : null}
                </div>

                <div className="flex flex-col lg:flex-row gap-6">
                    {/* Sidebar Tabs */}
                    <div className="lg:w-64 shrink-0">
                        <nav className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-2 flex lg:flex-col gap-1 overflow-x-auto lg:overflow-x-visible sticky top-24">
                            {tabs.map(tab => (
                                <button
                                    key={tab.key}
                                    onClick={() => setDashboardTab(tab.key)}
                                    className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all whitespace-nowrap w-full ${
                                        activeTab === tab.key
                                            ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300 shadow-sm'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                                    }`}
                                >
                                    {tab.icon}
                                    <span>{tab.label}</span>
                                    {activeTab === tab.key && (
                                        <span className="ms-auto">
                                            {dir === 'rtl' ? <ChevronLeft className="w-4 h-4" /> : <ChevronRight className="w-4 h-4" />}
                                        </span>
                                    )}
                                </button>
                            ))}
                        </nav>
                    </div>

                    {/* Content Area */}
                    <div className="flex-1 min-w-0">
                        {banner && (
                            <div className={`mb-6 rounded-2xl border p-4 sm:p-5 ${
                                banner.type === 'success'
                                    ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20'
                                    : banner.type === 'error'
                                        ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20'
                                        : 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20'
                            }`}>
                                <div className="flex items-start gap-3">
                                    <div className={`mt-0.5 ${
                                        banner.type === 'success'
                                            ? 'text-emerald-600 dark:text-emerald-300'
                                            : banner.type === 'error'
                                                ? 'text-red-600 dark:text-red-300'
                                                : 'text-blue-600 dark:text-blue-300'
                                    }`}>
                                        <CheckCircle2 className="w-5 h-5" />
                                    </div>
                                    <div className="flex-1">
                                        <p className={`font-semibold ${
                                            banner.type === 'success'
                                                ? 'text-emerald-800 dark:text-emerald-100'
                                                : banner.type === 'error'
                                                    ? 'text-red-800 dark:text-red-100'
                                                    : 'text-blue-800 dark:text-blue-100'
                                        }`}>
                                            {banner.message}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setBanner(null)}
                                        className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                        aria-label={__('Close')}
                                    >
                                        <X className="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        )}
                        {tabContent[activeTab]}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default DashboardPage;
