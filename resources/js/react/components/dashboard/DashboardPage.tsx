import React, { useState, useEffect } from 'react';
import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import { useTheme } from '../../contexts/ThemeContext';
import { userAuthService } from '../../services/dataService';
import { useCurrentRouteBootstrap } from '../../contexts/RouteBootstrapContext';
import { toast } from 'react-toastify';
import AppLink from '../common/AppLink';
import {
    User, Settings, Shield, LayoutDashboard, BookOpen, Award, Clock,
    Camera, Save, Eye, EyeOff, LogOut, Globe, Moon, Sun, ChevronLeft, ChevronRight,
    CheckCircle2, Loader2, X
} from 'lucide-react';

type TabKey = 'overview' | 'courses' | 'profile' | 'security' | 'settings';
type BannerState = { type: 'success' | 'info' | 'error'; message: string } | null;

const resolveDashboardTab = (search: string): TabKey => {
    const rawTab = new URLSearchParams(search).get('tab');

    return rawTab === 'courses'
        || rawTab === 'profile'
        || rawTab === 'security'
        || rawTab === 'settings'
        ? rawTab
        : 'overview';
};

const DashboardPage: React.FC = () => {
    const { user, logout, refreshUser } = useAuth();
    const { __, t } = useTranslation();
    const { language, dir, setLanguage } = useLanguage();
    const { theme, toggleTheme } = useTheme();
    const location = useLocation();
    const navigate = useNavigate();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const [activeTab, setActiveTab] = useState<TabKey>(() => resolveDashboardTab(location.search));
    const [banner, setBanner] = useState<BannerState>(null);

    // Profile form
    const [profileName, setProfileName] = useState('');
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
    const [coursesLoading, setCoursesLoading] = useState(false);
    const [coursesLoaded, setCoursesLoaded] = useState<boolean>(() => Array.isArray(initialBootstrap?.dashboardCourses));

    useEffect(() => {
        if (user) {
            setProfileName(user.name || '');
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
    }, [activeTab, coursesLoaded, coursesLoading, user]);

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    const setDashboardTab = (tab: TabKey) => {
        setActiveTab(tab);

        const params = new URLSearchParams(location.search);
        params.set('tab', tab);
        params.delete('payment');
        params.delete('transactionId');

        navigate({
            pathname: location.pathname,
            search: `?${params.toString()}`,
        }, { replace: true });
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
        { key: 'profile', label: __('Profile'), icon: <User className="w-5 h-5" /> },
        { key: 'security', label: __('Security'), icon: <Shield className="w-5 h-5" /> },
        { key: 'settings', label: __('Settings'), icon: <Settings className="w-5 h-5" /> },
    ];

    const handleProfileSave = async () => {
        setProfileSaving(true);
        const formData = new FormData();
        formData.append('name', profileName);
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
        const result = await userAuthService.updateLocale(locale);
        if (result.success) {
            setLanguage(locale as 'ar' | 'en' | 'ku');
            toast.success(__('Your language preference has been updated.'));
        }
    };

    const handleLogout = async () => {
        await logout();
    };

    const userInitials = user.name
        ? user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
        : '?';

    // ── Tab Content Renderers ──

    const renderOverview = () => (
        <div className="space-y-8 animate-fade-in">
            {/* Welcome Banner */}
            <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-700 to-indigo-800 p-6 sm:p-8 text-white">
                <div className="absolute inset-0 opacity-10">
                    <div className="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-white/20 blur-3xl" />
                    <div className="absolute -bottom-20 -left-20 w-60 h-60 rounded-full bg-brand-400/30 blur-2xl" />
                </div>
                <div className="relative flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl sm:text-3xl font-bold border-2 border-white/30 shrink-0">
                        {userInitials}
                    </div>
                    <div>
                        <h2 className="text-2xl sm:text-3xl font-bold">
                            {__('Welcome back')}, {user.name}! 👋
                        </h2>
                        <p className="text-white/80 mt-1 text-sm sm:text-base">{__('Here is an overview of your learning journey.')}</p>
                    </div>
                </div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                {[
                    { label: __('Courses Enrolled'), value: stats.courses_enrolled, icon: <BookOpen className="w-6 h-6" />, color: 'from-blue-500 to-blue-600', bg: 'bg-blue-50 dark:bg-blue-900/20' },
                    { label: __('Courses Completed'), value: stats.courses_completed, icon: <Award className="w-6 h-6" />, color: 'from-emerald-500 to-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-900/20' },
                    { label: __('Certificates'), value: stats.certificates, icon: <Clock className="w-6 h-6" />, color: 'from-amber-500 to-amber-600', bg: 'bg-amber-50 dark:bg-amber-900/20' },
                ].map((stat, i) => (
                    <div key={i} className={`${stat.bg} rounded-2xl p-6 border border-gray-100 dark:border-gray-700/50 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 group`}>
                        <div className={`w-12 h-12 rounded-xl bg-gradient-to-br ${stat.color} flex items-center justify-center text-white mb-4 group-hover:scale-110 transition-transform duration-300`}>
                            {stat.icon}
                        </div>
                        <p className="text-3xl font-bold text-gray-900 dark:text-white">{stat.value}</p>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{stat.label}</p>
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
                </div>
            </div>
        </div>
    );

    const renderCourses = () => (
        <div className="space-y-6 animate-fade-in">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h3 className="text-xl font-bold text-gray-900 dark:text-white">{__('My Courses')}</h3>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{__('All courses you have successfully unlocked appear here.')}</p>
                    </div>
                    <AppLink
                        to="/courses"
                        className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:border-brand-400 transition-colors"
                    >
                        <BookOpen className="w-4 h-4" />
                        {__('Browse Courses')}
                    </AppLink>
                </div>

                {coursesLoading ? (
                    <div className="flex items-center justify-center py-20">
                        <Loader2 className="w-8 h-8 animate-spin text-brand-600" />
                    </div>
                ) : courses.length > 0 ? (
                    <div className="grid grid-cols-1 xl:grid-cols-2 gap-5">
                        {courses.map((course) => (
                            <AppLink
                                key={course.id}
                                to={`/courses/${course.slug}`}
                                className="group block overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-900/30 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-lg transition-all"
                            >
                                <div className="aspect-[16/9] overflow-hidden bg-gray-100 dark:bg-gray-700">
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
                                </div>

                                <div className="p-5">
                                    <div className="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 mb-4">
                                        <CheckCircle2 className="w-4 h-4" />
                                        {__('Enrolled')}
                                    </div>

                                    <h4 className="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                        {course.title ? t(course.title) : __('Untitled course')}
                                    </h4>

                                    <p className="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        {course.instructor?.name ? t(course.instructor.name) : __('Instructor')}
                                    </p>

                                    <div className="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span className="inline-flex items-center gap-2">
                                            <Clock className="w-4 h-4" />
                                            {course.duration_hours} {__('Hours')}
                                        </span>
                                        <span className="inline-flex items-center gap-2">
                                            <CheckCircle2 className="w-4 h-4" />
                                            {formatEnrollmentDate(course.enrolled_at)}
                                        </span>
                                    </div>
                                </div>
                            </AppLink>
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
        <div className="animate-fade-in">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6">{__('Personal Information')}</h3>

                {/* Avatar */}
                <div className="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100 dark:border-gray-700">
                    <div className="relative">
                        <div className="w-20 h-20 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold border-4 border-white dark:border-gray-700 shadow-lg">
                            {userInitials}
                        </div>
                        <button className="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center shadow-lg hover:bg-brand-700 transition-colors">
                            <Camera className="w-4 h-4" />
                        </button>
                    </div>
                    <div>
                        <p className="font-semibold text-gray-900 dark:text-white text-lg">{user.name}</p>
                        <p className="text-sm text-gray-500 dark:text-gray-400">{user.email}</p>
                    </div>
                </div>

                {/* Form */}
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
                        {profileSaving ? __('Saving...') : __('Save Changes')}
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
            {/* Language */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <Globe className="w-5 h-5 text-brand-600" />
                    {__('Language')}
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {[
                        { code: 'ar', label: 'العربية', flag: '🇮🇶' },
                        { code: 'en', label: 'English', flag: '🇬🇧' },
                        { code: 'ku', label: 'کوردی', flag: '🇮🇶' },
                    ].map(lang => (
                        <button
                            key={lang.code}
                            onClick={() => handleLocaleChange(lang.code)}
                            className={`flex items-center gap-3 p-4 rounded-xl border-2 transition-all ${
                                language === lang.code
                                    ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 shadow-md'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-brand-300 dark:hover:border-brand-700'
                            }`}
                        >
                            <span className="text-2xl">{lang.flag}</span>
                            <span className={`font-medium ${language === lang.code ? 'text-brand-700 dark:text-brand-300' : 'text-gray-700 dark:text-gray-300'}`}>
                                {lang.label}
                            </span>
                            {language === lang.code && (
                                <span className="ms-auto w-5 h-5 rounded-full bg-brand-500 flex items-center justify-center">
                                    <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" /></svg>
                                </span>
                            )}
                        </button>
                    ))}
                </div>
            </div>

            {/* Theme */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sm:p-8">
                <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-6">{__('Appearance')}</h3>
                <button
                    onClick={toggleTheme}
                    className="flex items-center justify-between w-full p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 hover:border-brand-300 dark:hover:border-brand-700 transition-all"
                >
                    <div className="flex items-center gap-3">
                        {theme === 'dark' ? <Moon className="w-5 h-5 text-indigo-400" /> : <Sun className="w-5 h-5 text-amber-500" />}
                        <span className="font-medium text-gray-700 dark:text-gray-300">
                            {theme === 'dark' ? __('Dark Mode') : __('Light Mode')}
                        </span>
                    </div>
                    <div className={`w-12 h-7 rounded-full relative transition-colors ${theme === 'dark' ? 'bg-brand-600' : 'bg-gray-300'}`}>
                        <div className={`absolute top-0.5 w-6 h-6 rounded-full bg-white shadow-md transition-all ${theme === 'dark' ? 'end-0.5' : 'start-0.5'}`} />
                    </div>
                </button>
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
        profile: renderProfile(),
        security: renderSecurity(),
        settings: renderSettings(),
    };

    return (
        <div className="min-h-screen py-8">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Page Title */}
                <div className="mb-8">
                    <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{__('My Dashboard')}</h1>
                    <p className="text-gray-500 dark:text-gray-400 mt-1">{__('Manage your account and learning progress')}</p>
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
