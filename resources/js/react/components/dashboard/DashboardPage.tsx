import React, { useState, useEffect, useRef } from 'react';
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
    CheckCircle2, Loader2, X, ShoppingCart, Building2, Briefcase, CalendarDays,
    Folder, Github, GraduationCap, IdCard, Link as LinkIcon, Linkedin, Mail, MapPin, FileText, Printer
} from 'lucide-react';
import PhoneNumberInput, { createPhoneFieldValue } from '../common/PhoneNumberInput';
import OrderInvoiceModal from './OrderInvoiceModal';

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

type AccountProfileSnapshot = {
    name: string;
    real_name: string;
    phone: string;
    country_code: string;
};

const AVATAR_MAX_SIZE_BYTES = 5 * 1024 * 1024;
const AVATAR_ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

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
const createAccountProfileSnapshot = (user: any): AccountProfileSnapshot => {
    const phoneField = createPhoneFieldValue(user?.phone, user?.country_code);

    return {
        name: user?.name || '',
        real_name: user?.real_name || '',
        phone: phoneField.phone,
        country_code: phoneField.countryCode,
    };
};
const classNames = (...parts: Array<string | false | null | undefined>) => parts.filter(Boolean).join(' ');

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
    const [profileName, setProfileName] = useState(() => createAccountProfileSnapshot(user).name);
    const [profileRealName, setProfileRealName] = useState(() => createAccountProfileSnapshot(user).real_name);
    const [profilePhone, setProfilePhone] = useState(() => createAccountProfileSnapshot(user).phone);
    const [profilePhoneCountryCode, setProfilePhoneCountryCode] = useState(() => createAccountProfileSnapshot(user).country_code);
    const [profileAvatarFile, setProfileAvatarFile] = useState<File | null>(null);
    const [profileAvatarPreview, setProfileAvatarPreview] = useState<string | null>(() => (user as any)?.avatar || null);
    const [profileAvatarDragActive, setProfileAvatarDragActive] = useState(false);
    const [savedProfileSnapshot, setSavedProfileSnapshot] = useState<AccountProfileSnapshot>(() => createAccountProfileSnapshot(user));
    const [profileSavedOnce, setProfileSavedOnce] = useState(false);
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
    const [selectedOrderForInvoice, setSelectedOrderForInvoice] = useState<any>(null);
    const [invoiceShouldAutoPrint, setInvoiceShouldAutoPrint] = useState(false);
    const [verifyCooldowns, setVerifyCooldowns] = useState<Record<number, number>>({});
    
    const [localeSaving, setLocaleSaving] = useState(false);
    const [roleSwitching, setRoleSwitching] = useState(false);
    const [studentProfile, setStudentProfile] = useState<StudentProfileForm>(() => mapStudentProfile(initialBootstrap?.studentProfile));
    const [skillsText, setSkillsText] = useState<string>(() => stringifyList(mapStudentProfile(initialBootstrap?.studentProfile).skills));
    const [projectsText, setProjectsText] = useState<string>(() => stringifyList(mapStudentProfile(initialBootstrap?.studentProfile).projects));
    const [savedStudentProfile, setSavedStudentProfile] = useState<StudentProfileForm>(() => mapStudentProfile(initialBootstrap?.studentProfile));
    const [savedSkillsText, setSavedSkillsText] = useState<string>(() => stringifyList(mapStudentProfile(initialBootstrap?.studentProfile).skills));
    const [savedProjectsText, setSavedProjectsText] = useState<string>(() => stringifyList(mapStudentProfile(initialBootstrap?.studentProfile).projects));
    const [studentProfileSavedOnce, setStudentProfileSavedOnce] = useState(false);
    const [studentProfileLoading, setStudentProfileLoading] = useState(false);
    const [studentProfileLoaded, setStudentProfileLoaded] = useState<boolean>(() => Boolean(initialBootstrap?.studentProfile));
    const [studentProfileSaving, setStudentProfileSaving] = useState(false);
    const profileAvatarInputRef = useRef<HTMLInputElement | null>(null);
    const profileAvatarObjectUrlRef = useRef<string | null>(null);

    useEffect(() => {
        const interval = setInterval(() => {
            setVerifyCooldowns(prev => {
                let changed = false;
                const next = { ...prev };
                const now = Date.now();
                for (const key in next) {
                    if (now >= next[key]) {
                        delete next[key];
                        changed = true;
                    }
                }
                return changed ? next : prev;
            });
        }, 1000);
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        if (user) {
            const snapshot = createAccountProfileSnapshot(user);
            setProfileName(snapshot.name);
            setProfileRealName(snapshot.real_name);
            setProfilePhone(snapshot.phone);
            setProfilePhoneCountryCode(snapshot.country_code);
            setSavedProfileSnapshot(snapshot);
            setProfileSavedOnce(false);
            if (profileAvatarObjectUrlRef.current) {
                URL.revokeObjectURL(profileAvatarObjectUrlRef.current);
                profileAvatarObjectUrlRef.current = null;
            }
            setProfileAvatarFile(null);
            setProfileAvatarPreview((user as any).avatar || null);
            if (initialBootstrap?.dashboardStats) {
                setStats(initialBootstrap.dashboardStats);
                return;
            }

            userAuthService.getDashboardStats()
                .then(data => setStats(data || { courses_enrolled: 0, courses_completed: 0, certificates: 0 }))
                .catch(() => {});
        }
    }, [initialBootstrap, user]);

    useEffect(() => () => {
        if (profileAvatarObjectUrlRef.current) {
            URL.revokeObjectURL(profileAvatarObjectUrlRef.current);
        }
    }, []);

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
        setSavedStudentProfile(mappedProfile);
        setSavedSkillsText(stringifyList(mappedProfile.skills));
        setSavedProjectsText(stringifyList(mappedProfile.projects));
        setStudentProfileSavedOnce(false);
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
                setSavedStudentProfile(mappedProfile);
                setSavedSkillsText(stringifyList(mappedProfile.skills));
                setSavedProjectsText(stringifyList(mappedProfile.projects));
                setStudentProfileSavedOnce(false);
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
                setSavedStudentProfile(mappedProfile);
                setSavedSkillsText('');
                setSavedProjectsText('');
                setStudentProfileSavedOnce(false);
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

    const openInvoicePreview = (order: any) => {
        setInvoiceShouldAutoPrint(false);
        setSelectedOrderForInvoice(order);
    };

    const openInvoicePrint = (order: any) => {
        setInvoiceShouldAutoPrint(true);
        setSelectedOrderForInvoice(order);
    };

    const closeInvoiceModal = () => {
        setInvoiceShouldAutoPrint(false);
        setSelectedOrderForInvoice(null);
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
        formData.append('phone', profilePhone);
        formData.append('country_code', profilePhoneCountryCode);
        if (profileAvatarFile) formData.append('avatar', profileAvatarFile);
        const result = await userAuthService.updateProfile(formData);
        if (result.success) {
            const nextSnapshot = createAccountProfileSnapshot(result.data);
            setProfileName(nextSnapshot.name);
            setProfileRealName(nextSnapshot.real_name);
            setProfilePhone(nextSnapshot.phone);
            setProfilePhoneCountryCode(nextSnapshot.country_code);
            setSavedProfileSnapshot(nextSnapshot);
            if (profileAvatarObjectUrlRef.current) {
                URL.revokeObjectURL(profileAvatarObjectUrlRef.current);
                profileAvatarObjectUrlRef.current = null;
            }
            setProfileAvatarFile(null);
            setProfileAvatarPreview(result.data?.avatar || (user as any)?.avatar || null);
            setProfileSavedOnce(true);
            toast.success(result.message || __('Your profile has been updated successfully.'));
            await refreshUser();
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }
        setProfileSaving(false);
    };

    const handleProfileAvatarSelection = (file: File | null | undefined) => {
        if (!file) {
            return;
        }

        if (!AVATAR_ACCEPTED_TYPES.includes(file.type)) {
            toast.error(__('Please upload a JPG, PNG, GIF, or WebP image.'));
            return;
        }

        if (file.size > AVATAR_MAX_SIZE_BYTES) {
            toast.error(__('Profile image must be 5 MB or smaller.'));
            return;
        }

        if (profileAvatarObjectUrlRef.current) {
            URL.revokeObjectURL(profileAvatarObjectUrlRef.current);
        }

        const objectUrl = URL.createObjectURL(file);
        profileAvatarObjectUrlRef.current = objectUrl;
        setProfileAvatarFile(file);
        setProfileAvatarPreview(objectUrl);
    };

    const handleProfileAvatarInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        handleProfileAvatarSelection(e.target.files?.[0]);
        e.target.value = '';
    };

    const handleProfileAvatarDrop = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        setProfileAvatarDragActive(false);
        handleProfileAvatarSelection(e.dataTransfer.files?.[0]);
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
            const nextSkillsText = stringifyList(mappedProfile.skills);
            const nextProjectsText = stringifyList(mappedProfile.projects);
            setStudentProfile(mappedProfile);
            setSkillsText(nextSkillsText);
            setProjectsText(nextProjectsText);
            setSavedStudentProfile(mappedProfile);
            setSavedSkillsText(nextSkillsText);
            setSavedProjectsText(nextProjectsText);
            setStudentProfileSavedOnce(true);
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
    const profileHasChanges = profileName !== savedProfileSnapshot.name
        || profileRealName !== savedProfileSnapshot.real_name
        || profilePhone !== savedProfileSnapshot.phone
        || profilePhoneCountryCode !== savedProfileSnapshot.country_code
        || Boolean(profileAvatarFile);
    const studentProfileHasChanges = JSON.stringify(studentProfile) !== JSON.stringify(savedStudentProfile)
        || skillsText !== savedSkillsText
        || projectsText !== savedProjectsText;

    type ProfileSectionStatusProps = {
        isSaving: boolean;
        isDirty: boolean;
        hasSavedOnce?: boolean;
    };

    const ProfileSectionStatus = ({ isSaving, isDirty, hasSavedOnce = false }: ProfileSectionStatusProps) => {
        if (!isSaving && !isDirty && !hasSavedOnce) {
            return null;
        }

        const tone = isSaving
            ? 'bg-brand-50 text-brand-700 border-brand-100 dark:bg-brand-500/10 dark:text-brand-200 dark:border-brand-500/20'
            : isDirty
                ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20'
                : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20';
        const label = isSaving
            ? __('Saving...')
            : isDirty
                ? __('You have unsaved changes')
                : __('All changes saved');

        return (
            <div className={classNames('inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold', tone)}>
                {isSaving ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : isDirty ? <Clock className="h-3.5 w-3.5" /> : <CheckCircle2 className="h-3.5 w-3.5" />}
                <span>{label}</span>
            </div>
        );
    };

    type ProfileSectionHeaderProps = {
        icon: React.ReactNode;
        title: string;
        description: string;
        status: React.ReactNode;
    };

    const ProfileSectionHeader = ({ icon, title, description, status }: ProfileSectionHeaderProps) => (
        <div className="mb-7 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div className="flex items-start gap-4">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-200">
                    {icon}
                </div>
                <div className="space-y-1">
                    <h3 className="text-xl font-bold text-gray-900 dark:text-white">{title}</h3>
                    <p className="max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">{description}</p>
                </div>
            </div>
            {status}
        </div>
    );

    type ProfileFieldShellProps = {
        label: string;
        icon: React.ReactNode;
        helper?: string;
        children: React.ReactNode;
        iconAlign?: 'center' | 'top';
    };

    const ProfileFieldShell = ({ label, icon, helper, children, iconAlign = 'center' }: ProfileFieldShellProps) => (
        <div className="space-y-2.5">
            <label className="block text-sm font-semibold text-gray-700 dark:text-gray-200">{label}</label>
            <div className="relative">
                <span className={classNames(
                    'pointer-events-none absolute z-10 text-brand-500/85 dark:text-brand-300',
                    iconAlign === 'top' ? 'start-4 top-4' : 'start-4 top-1/2 -translate-y-1/2'
                )}>
                    {icon}
                </span>
                {children}
            </div>
            {helper ? <p className="text-xs leading-5 text-gray-500 dark:text-gray-400">{helper}</p> : null}
        </div>
    );

    type ProfileInputFieldProps = {
        label: string;
        icon: React.ReactNode;
        value: string;
        onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
        helper?: string;
        placeholder?: string;
        type?: string;
        disabled?: boolean;
        inputMode?: React.HTMLAttributes<HTMLInputElement>['inputMode'];
        min?: string;
        max?: string;
    };

    const ProfileInputField = ({
        label,
        icon,
        value,
        onChange,
        helper,
        placeholder,
        type = 'text',
        disabled = false,
        inputMode,
        min,
        max,
    }: ProfileInputFieldProps) => (
        <ProfileFieldShell label={label} icon={icon} helper={helper}>
            <input
                type={type}
                value={value}
                onChange={onChange}
                placeholder={placeholder}
                disabled={disabled}
                inputMode={inputMode}
                min={min}
                max={max}
                className={classNames(
                    'block w-full rounded-2xl border px-4 py-3.5 text-sm text-gray-900 transition-all outline-none',
                    'ltr:pl-12 ltr:pr-4 rtl:pr-12 rtl:pl-4',
                    disabled
                        ? 'border-gray-200 bg-gray-100/90 text-gray-500 shadow-inner dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-400'
                        : 'border-gray-200 bg-gray-50/80 hover:border-brand-200 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/15 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:border-brand-400/40 dark:focus:border-brand-400 dark:focus:bg-gray-700',
                    'placeholder:text-gray-400 dark:placeholder:text-gray-500'
                )}
            />
        </ProfileFieldShell>
    );

    type ProfileTextareaFieldProps = {
        label: string;
        icon: React.ReactNode;
        value: string;
        onChange: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
        helper?: string;
        placeholder?: string;
        rows?: number;
    };

    const ProfileTextareaField = ({
        label,
        icon,
        value,
        onChange,
        helper,
        placeholder,
        rows = 4,
    }: ProfileTextareaFieldProps) => (
        <ProfileFieldShell label={label} icon={icon} helper={helper} iconAlign="top">
            <textarea
                value={value}
                onChange={onChange}
                rows={rows}
                placeholder={placeholder}
                className="block w-full rounded-2xl border border-gray-200 bg-gray-50/80 px-4 py-3.5 text-sm text-gray-900 transition-all outline-none ltr:pl-12 ltr:pr-4 rtl:pr-12 rtl:pl-4 placeholder:text-gray-400 hover:border-brand-200 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/15 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500 dark:hover:border-brand-400/40 dark:focus:border-brand-400 dark:focus:bg-gray-700"
            />
        </ProfileFieldShell>
    );

    type ProfileToggleCardProps = {
        label: string;
        description: string;
        icon: React.ReactNode;
        checked: boolean;
        onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
    };

    const ProfileToggleCard = ({ label, description, icon, checked, onChange }: ProfileToggleCardProps) => (
        <label className={classNames(
            'flex items-center gap-4 rounded-2xl border px-4 py-4 transition-all',
            checked
                ? 'border-brand-200 bg-brand-50/70 dark:border-brand-500/25 dark:bg-brand-500/10'
                : 'border-gray-200 bg-gray-50/70 hover:border-brand-200 dark:border-gray-700 dark:bg-gray-900/30 dark:hover:border-brand-500/25'
        )}>
            <div className={classNames(
                'flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border',
                checked
                    ? 'border-brand-200 bg-white text-brand-600 dark:border-brand-500/20 dark:bg-gray-900/50 dark:text-brand-200'
                    : 'border-gray-200 bg-white text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
            )}>
                {icon}
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-sm font-semibold text-gray-800 dark:text-gray-100">{label}</p>
                <p className="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{description}</p>
            </div>
            <input
                type="checkbox"
                checked={checked}
                onChange={onChange}
                className="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
            />
        </label>
    );

    // ── Tab Content Renderers ──

    const handleVerifyOrder = async (transactionId: number, orderId: number) => {
        setVerifyingOrderId(transactionId);
        setVerifyCooldowns(p => ({...p, [orderId]: Date.now() + 60000}));
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
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 shadow-[0_20px_70px_-48px_rgba(15,23,42,0.42)]">
                <div className="flex items-center gap-3 mb-6">
                    <div className="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300">
                        <ShoppingCart className="w-5 h-5" />
                    </div>
                    <div>
                        <h2 className="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{__('Order History')}</h2>
                        <p className="text-sm text-gray-500 dark:text-gray-400">{__('Review your previous orders and transactions')}</p>
                    </div>
                </div>
                
                {!ordersLoaded || ordersLoading ? (
                    <div className="grid gap-4">
                        {Array.from({ length: 3 }).map((_, i) => (
                            <div key={i} className="bg-gray-50 dark:bg-gray-900/30 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center animate-pulse">
                                <div className="space-y-4 flex-grow w-full">
                                    <div className="flex items-center gap-3">
                                        <div className="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                        <div className="h-5 w-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                    </div>
                                    <div className="h-5 w-3/4 max-w-[300px] bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    <div className="flex items-center gap-4">
                                        <div className="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                        <div className="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                                <div className="flex flex-col sm:items-end gap-3 w-full sm:w-auto mt-4 sm:mt-0">
                                    <div className="h-7 w-28 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : orders.length === 0 ? (
                    <div className="bg-gray-50 dark:bg-gray-900/30 rounded-2xl p-8 border border-dashed border-gray-200 dark:border-gray-700/50 text-center text-gray-500 dark:text-gray-400">
                        <ShoppingCart className="w-12 h-12 mx-auto mb-4 opacity-30" />
                        <p className="font-medium text-gray-700 dark:text-gray-300">{__('You have no orders yet.')}</p>
                        <p className="text-sm mt-1">{__('Orders you place will appear here.')}</p>
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
                                <div key={order.id} className="bg-gray-50 dark:bg-gray-900/30 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-5 sm:p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center hover:bg-white dark:hover:bg-gray-800 transition-colors">
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
                                        <div className="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 mt-2 sm:mt-0 w-full sm:w-auto">
                                            <button
                                                onClick={() => openInvoicePreview(order)}
                                                className="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-700 flex items-center justify-center gap-2"
                                                title={__('View Invoice')}
                                            >
                                                <FileText className="w-4 h-4" />
                                                <span className="hidden xl:inline">{__('View')}</span>
                                            </button>
                                            <button
                                                onClick={() => openInvoicePrint(order)}
                                                className="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors border border-gray-200 dark:border-gray-700 flex items-center justify-center gap-2"
                                                title={__('Print Invoice')}
                                            >
                                                <Printer className="w-4 h-4" />
                                                <span className="hidden xl:inline">{__('Print')}</span>
                                            </button>
                                            {showReverify && (
                                                <button 
                                                    disabled={verifyingOrderId === order.transaction_id || verifyCooldowns[order.id] > Date.now()}
                                                    onClick={() => handleVerifyOrder(order.transaction_id, order.id)}
                                                    className="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:hover:bg-brand-900/50 dark:text-brand-400 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2 disabled:opacity-50 border border-brand-100 dark:border-brand-800"
                                                >
                                                    {verifyingOrderId === order.transaction_id && <Loader2 className="w-4 h-4 animate-spin" />}
                                                    {verifyCooldowns[order.id] > Date.now() 
                                                        ? `${__('Wait')} ${Math.ceil((verifyCooldowns[order.id] - Date.now()) / 1000)}s` 
                                                        : __('Re-verify')}
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
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
                    <div className="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-5">
                        {Array.from({ length: 3 }).map((_, i) => (
                            <div key={i} className="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-900/30 animate-pulse">
                                <div className="p-4">
                                    <div className="relative aspect-[16/10] overflow-hidden rounded-xl bg-gray-200 dark:bg-gray-800"></div>
                                </div>
                                <div className="px-5 pb-5 space-y-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="h-6 w-20 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
                                        <div className="h-4 w-8 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                    </div>
                                    <div className="space-y-2">
                                        <div className="h-6 w-3/4 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                        <div className="h-4 w-1/2 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                    </div>
                                    <div className="flex flex-wrap gap-3 pt-2">
                                        <div className="h-11 flex-1 min-w-[180px] bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                        <div className="h-11 w-36 bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                    </div>
                                </div>
                            </div>
                        ))}
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
                    <div className="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-5">
                        {Array.from({ length: 3 }).map((_, i) => (
                            <div key={i} className="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700/50 bg-gray-50 dark:bg-gray-900/30 animate-pulse">
                                <div className="relative aspect-[16/9] bg-gray-200 dark:bg-gray-800"></div>
                                <div className="p-5 space-y-4">
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between gap-3">
                                            <div className="h-4 w-32 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                            <div className="h-4 w-24 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                        </div>
                                        <div className="h-6 w-4/5 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                        <div className="h-4 w-1/3 bg-gray-200 dark:bg-gray-800 rounded"></div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-3 pb-2 pt-1">
                                        <div className="h-[76px] bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                        <div className="h-[76px] bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                    </div>
                                    <div className="h-[96px] bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                    <div className="flex flex-wrap gap-3 pt-1">
                                        <div className="h-11 flex-1 min-w-[180px] bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                        <div className="h-11 w-36 bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
                                    </div>
                                </div>
                            </div>
                        ))}
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
            <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700/50 dark:bg-gray-800 sm:p-8">
                <div className="mb-8 flex flex-col gap-5 border-b border-gray-100 pb-6 sm:flex-row sm:items-center dark:border-gray-700">
                    <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 to-indigo-600 text-2xl font-bold text-white shadow-lg shadow-brand-500/20">
                        {profileAvatarPreview ? (
                            <img src={profileAvatarPreview} alt={user.name} className="h-full w-full object-cover" />
                        ) : (
                            userInitials
                        )}
                    </div>
                    <div className="space-y-1">
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">{user.name}</p>
                        <p className="text-sm text-gray-500 dark:text-gray-400">{user.email}</p>
                    </div>
                </div>

                <ProfileSectionHeader
                    icon={<User className="h-5 w-5" />}
                    title={__('Account Information')}
                    description={__('Keep your name and contact details up to date.')}
                    status={<ProfileSectionStatus isSaving={profileSaving} isDirty={profileHasChanges} hasSavedOnce={profileSavedOnce} />}
                />

                <div className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                        <div className="space-y-4">
                            <div className="flex flex-col items-center gap-4 lg:items-start">
                                <div
                                    role="button"
                                    tabIndex={0}
                                    onClick={() => profileAvatarInputRef.current?.click()}
                                    onKeyDown={e => {
                                        if (e.key === 'Enter' || e.key === ' ') {
                                            e.preventDefault();
                                            profileAvatarInputRef.current?.click();
                                        }
                                    }}
                                    onDragEnter={e => { e.preventDefault(); setProfileAvatarDragActive(true); }}
                                    onDragOver={e => { e.preventDefault(); setProfileAvatarDragActive(true); }}
                                    onDragLeave={e => { e.preventDefault(); setProfileAvatarDragActive(false); }}
                                    onDrop={handleProfileAvatarDrop}
                                    className={classNames(
                                        'group relative flex h-40 w-40 cursor-pointer items-center justify-center overflow-hidden rounded-full border-4 text-4xl font-bold text-white shadow-xl outline-none transition-all',
                                        profileAvatarDragActive
                                            ? 'scale-[1.02] border-brand-300 ring-4 ring-brand-500/20 dark:border-brand-300'
                                            : 'border-white dark:border-gray-700',
                                        'bg-gradient-to-br from-brand-500 to-indigo-600 shadow-brand-500/20'
                                    )}
                                >
                                    <input
                                        ref={profileAvatarInputRef}
                                        type="file"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        onChange={handleProfileAvatarInputChange}
                                        className="hidden"
                                    />
                                    {profileAvatarPreview ? (
                                        <img src={profileAvatarPreview} alt={user.name} className="h-full w-full object-cover" />
                                    ) : (
                                        userInitials
                                    )}
                                    <div className={classNames(
                                        'pointer-events-none absolute inset-[7px] rounded-full border-2 border-dashed transition-colors',
                                        profileAvatarDragActive
                                            ? 'border-white/95'
                                            : 'border-white/70 group-hover:border-white/95 dark:border-white/55 dark:group-hover:border-white/85'
                                    )} />
                                    <div className="pointer-events-none absolute bottom-3 end-3 flex h-10 w-10 items-center justify-center rounded-full border border-white/70 bg-slate-950/60 text-white shadow-lg backdrop-blur-sm">
                                        <Camera className="h-4.5 w-4.5" />
                                    </div>
                                    <div className={classNames(
                                        'absolute inset-0 flex flex-col items-center justify-center gap-2 bg-slate-950/45 text-center text-white transition-opacity',
                                        profileAvatarDragActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100'
                                    )}>
                                        <span className="flex h-11 w-11 items-center justify-center rounded-full bg-white/18 backdrop-blur-sm">
                                            <Camera className="h-5 w-5" />
                                        </span>
                                        <span className="px-4 text-xs font-semibold leading-5">{__('Drag and drop your profile photo here')}</span>
                                    </div>
                                </div>

                                <div className="text-center lg:text-start">
                                    <p className="text-sm font-semibold text-gray-800 dark:text-gray-100">{__('Choose profile photo')}</p>
                                    <p className="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{__('You can also browse from your device. JPG, PNG, GIF, or WebP up to 5 MB.')}</p>
                                    {profileAvatarFile ? <p className="mt-2 text-xs font-medium text-brand-700 dark:text-brand-300">{profileAvatarFile.name}</p> : null}
                                </div>
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <ProfileInputField label={__('Full Name')} icon={<User className="h-4 w-4" />} value={profileName} onChange={e => setProfileName(e.target.value)} />
                                <ProfileInputField
                                    label={__('Real Name')}
                                    icon={<IdCard className="h-4 w-4" />}
                                    value={profileRealName}
                                    onChange={e => setProfileRealName(e.target.value)}
                                    placeholder={__('Enter the exact name that should appear on your certificates')}
                                    helper={__('This formal name will be rendered on your course certificates when you download them.')}
                                />
                                <ProfileInputField label={__('Email')} icon={<Mail className="h-4 w-4" />} value={user.email} type="email" disabled helper={__('To change your email, go to the Security tab.')} />
                                <div className="space-y-2.5">
                                    <label className="block text-sm font-semibold text-gray-700 dark:text-gray-200">{__('Phone Number')}</label>
                                    <PhoneNumberInput
                                        value={profilePhone}
                                        countryCode={profilePhoneCountryCode}
                                        onChange={({ phone, countryCode }) => {
                                            setProfilePhone(phone);
                                            setProfilePhoneCountryCode(countryCode);
                                        }}
                                        variant="dashboard"
                                        placeholder={__('Enter phone number with country code')}
                                    />
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <button
                                    onClick={handleProfileSave}
                                    disabled={profileSaving || !profileHasChanges}
                                    className="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition-all hover:from-brand-700 hover:to-brand-800 hover:shadow-brand-500/40 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                                >
                                    <Save className="h-4 w-4" />
                                    {profileSaving ? __('Saving...') : __('Save Account')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700/50 dark:bg-gray-800 sm:p-8">
                <ProfileSectionHeader
                    icon={<Briefcase className="h-5 w-5" />}
                    title={__('Student Profile')}
                    description={__('Add the academic, career, and portfolio details that should travel with your student account.')}
                    status={(
                        <div className="flex items-center gap-2 self-start md:self-auto">
                            {studentProfileLoading ? <Loader2 className="h-4 w-4 animate-spin text-brand-600" /> : null}
                            <ProfileSectionStatus isSaving={studentProfileSaving} isDirty={studentProfileHasChanges} hasSavedOnce={studentProfileSavedOnce} />
                        </div>
                    )}
                />

                <div className="space-y-7">
                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <ProfileInputField label={__('University')} icon={<GraduationCap className="h-4 w-4" />} value={studentProfile.university} onChange={e => handleStudentProfileChange('university', e.target.value)} placeholder={__('Your university or institute')} />
                        <ProfileInputField label={__('Department')} icon={<Building2 className="h-4 w-4" />} value={studentProfile.department} onChange={e => handleStudentProfileChange('department', e.target.value)} placeholder={__('Your major or department')} />
                        <ProfileInputField label={__('Degree')} icon={<Award className="h-4 w-4" />} value={studentProfile.degree} onChange={e => handleStudentProfileChange('degree', e.target.value)} placeholder={__('Bachelor, Diploma, or Master')} />
                        <ProfileInputField label={__('Academic Status')} icon={<BookOpen className="h-4 w-4" />} value={studentProfile.academic_status} onChange={e => handleStudentProfileChange('academic_status', e.target.value)} placeholder={__('Example: Final-year student')} />
                        <ProfileInputField label={__('Start Year')} icon={<CalendarDays className="h-4 w-4" />} value={studentProfile.start_year} onChange={e => handleStudentProfileChange('start_year', e.target.value)} type="number" inputMode="numeric" min="1950" max="2100" placeholder="2024" helper={__('Use 4 digits, for example 2026')} />
                        <ProfileInputField label={__('Graduation Year')} icon={<CalendarDays className="h-4 w-4" />} value={studentProfile.graduation_year} onChange={e => handleStudentProfileChange('graduation_year', e.target.value)} type="number" inputMode="numeric" min="1950" max="2100" placeholder="2028" helper={__('Use 4 digits, for example 2026')} />
                    </div>

                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <ProfileInputField label={__('Headline')} icon={<FileText className="h-4 w-4" />} value={studentProfile.headline} onChange={e => handleStudentProfileChange('headline', e.target.value)} placeholder={__('Example: Frontend Developer in Training')} helper={__('Show the role or focus you are preparing for.')} />
                        <ProfileInputField label={__('Preferred Role')} icon={<Briefcase className="h-4 w-4" />} value={studentProfile.preferred_role} onChange={e => handleStudentProfileChange('preferred_role', e.target.value)} placeholder={__('Example: Junior Frontend Developer')} />
                        <ProfileInputField label={__('City')} icon={<MapPin className="h-4 w-4" />} value={studentProfile.city} onChange={e => handleStudentProfileChange('city', e.target.value)} />
                        <ProfileInputField label={__('Country')} icon={<Globe className="h-4 w-4" />} value={studentProfile.country} onChange={e => handleStudentProfileChange('country', e.target.value)} />
                        <ProfileInputField label={__('Preferred City')} icon={<MapPin className="h-4 w-4" />} value={studentProfile.preferred_city} onChange={e => handleStudentProfileChange('preferred_city', e.target.value)} placeholder={__('Where you would like to work')} />
                        <ProfileInputField label={__('Portfolio URL')} icon={<LinkIcon className="h-4 w-4" />} value={studentProfile.portfolio_url} onChange={e => handleStudentProfileChange('portfolio_url', e.target.value)} type="url" placeholder="https://yourportfolio.com" helper={__('Paste the full public profile link')} />
                    </div>

                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <ProfileInputField label={__('LinkedIn URL')} icon={<Linkedin className="h-4 w-4" />} value={studentProfile.linkedin_url} onChange={e => handleStudentProfileChange('linkedin_url', e.target.value)} type="url" placeholder="https://linkedin.com/in/username" helper={__('Paste the full public profile link')} />
                        <ProfileInputField label={__('GitHub URL')} icon={<Github className="h-4 w-4" />} value={studentProfile.github_url} onChange={e => handleStudentProfileChange('github_url', e.target.value)} type="url" placeholder="https://github.com/username" helper={__('Paste the full public profile link')} />
                    </div>

                    <ProfileTextareaField label={__('Short Bio')} icon={<FileText className="h-4 w-4" />} value={studentProfile.short_bio} onChange={e => handleStudentProfileChange('short_bio', e.target.value)} rows={4} placeholder={__('Write 2 to 4 lines about your goals and strengths.')} />

                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <ProfileTextareaField label={__('Skills')} icon={<Award className="h-4 w-4" />} value={skillsText} onChange={e => setSkillsText(e.target.value)} rows={5} placeholder={__('One skill per line')} helper={__('Add one skill per line to keep your profile easy to scan.')} />
                        <ProfileTextareaField label={__('Projects')} icon={<Folder className="h-4 w-4" />} value={projectsText} onChange={e => setProjectsText(e.target.value)} rows={5} placeholder={__('One project per line')} helper={__('Add one project per line with concise names.')} />
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <ProfileToggleCard label={__('Open to full-time jobs')} description={__('Helpful for employers browsing student profiles.')} icon={<Briefcase className="h-5 w-5" />} checked={studentProfile.job_available} onChange={e => handleStudentProfileChange('job_available', e.target.checked)} />
                        <ProfileToggleCard label={__('Open to internships')} description={__('Useful for internship and academy partner opportunities.')} icon={<BookOpen className="h-5 w-5" />} checked={studentProfile.internship_available} onChange={e => handleStudentProfileChange('internship_available', e.target.checked)} />
                    </div>

                    <div className="flex justify-end">
                        <button
                            onClick={handleStudentProfileSave}
                            disabled={studentProfileSaving || !studentProfileHasChanges}
                            className="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition-all hover:from-brand-700 hover:to-brand-800 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            <Save className="h-4 w-4" />
                            {studentProfileSaving ? __('Saving...') : __('Save Student Profile')}
                        </button>
                    </div>
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
            <OrderInvoiceModal
                order={selectedOrderForInvoice}
                isOpen={!!selectedOrderForInvoice}
                autoPrint={invoiceShouldAutoPrint}
                onAutoPrintHandled={() => setInvoiceShouldAutoPrint(false)}
                onClose={closeInvoiceModal}
            />
        </div>
    );
};

export default DashboardPage;
