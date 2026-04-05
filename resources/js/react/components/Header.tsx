import React, { useState, useRef, useEffect } from 'react';
import { useLanguage, Language } from '../contexts/LanguageContext';
import { useAuth } from '../contexts/AuthContext';
import { useSettings } from '../contexts/SettingsContext';
import { useLocation, useNavigate as useRawNavigate } from 'react-router-dom';
import { useTheme } from '../contexts/ThemeContext';
import { userAuthService, dataService } from '../services/dataService';
import { Menu, X, ChevronDown, ChevronLeft, Search, ShoppingCart, Sun, Moon, Loader2, LayoutDashboard, BookOpen, User, Shield, Settings, LogOut, Award } from 'lucide-react';
import { useTranslation } from '../contexts/TranslationProvider';
import { useAppNavigate } from '../hooks/useAppNavigate';
import { loadRouteModule } from '../routing/routeRegistry';
import Logo from './Logo';
import AppLink from './common/AppLink';
import NProgress from 'nprogress';
import { useCart } from '../contexts/CartContext';
import CartSlideOver from './cart/CartSlideOver';
import { AnimatePresence, motion } from 'framer-motion';

interface HeaderProps {
  onLoginClick: () => void;
}

const Header: React.FC<HeaderProps> = ({ onLoginClick }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isCoursesDropdownOpen, setIsCoursesDropdownOpen] = useState(false);
  const [isLangDropdownOpen, setIsLangDropdownOpen] = useState(false);
  const [isUserDropdownOpen, setIsUserDropdownOpen] = useState(false);
  const [isLangLoading, setIsLangLoading] = useState(false);
  const [activeCourseType, setActiveCourseType] = useState<string | null>(null);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  const [dbCategories, setDbCategories] = useState<any[]>([]);
  const [dbCourses, setDbCourses] = useState<any[]>([]);

  const [isScrolled, setIsScrolled] = useState(false);
  const { language, setLanguage } = useLanguage();
  const { __, t, prefetchLocale, applyFetchedLocale } = useTranslation();
  const { user, logout } = useAuth();
  const { cartCount } = useCart();
  const { settings } = useSettings();
  const { theme, toggleTheme } = useTheme();
  const dropdownRef = useRef<HTMLDivElement>(null);
  const langDropdownRef = useRef<HTMLDivElement>(null);
  const userDropdownRef = useRef<HTMLDivElement>(null);

  const navigate = useAppNavigate();
  const rawNavigate = useRawNavigate();
  const location = useLocation();

  // Handle Scroll Effect
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 10);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Fetch true DB Categories & Courses for Header Nav
  useEffect(() => {
    Promise.all([
      dataService.getCategories().catch(() => []),
      dataService.getHomeData().catch(() => ({ courses: [] })),
    ]).then(([cats, data]) => {
      setDbCategories(cats || []);
      setDbCourses(data.courses || []);
      if ((cats || []).length > 0) {
        setActiveCourseType(cats[0].slug);
      }
    }).catch(console.error);
  }, []);

  // Handle Click Outside Dropdowns
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsCoursesDropdownOpen(false);
      }
      if (langDropdownRef.current && !langDropdownRef.current.contains(event.target as Node)) {
        setIsLangDropdownOpen(false);
      }
      if (userDropdownRef.current && !userDropdownRef.current.contains(event.target as Node)) {
        setIsUserDropdownOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleNavClick = async (path: string) => {
    setIsOpen(false);
    setIsCoursesDropdownOpen(false);
    setIsLangDropdownOpen(false);
    setIsUserDropdownOpen(false);
    navigate(path);
  };

  const handleSearch = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      const q = searchQuery.trim();
      if (q.length >= 2) {
        setIsOpen(false);
        navigate(`/search?q=${encodeURIComponent(q)}`);
      }
    }
  };

  const handleLanguageSelect = async (lang: Language) => {
    if (lang === language) return; // No-op if same language
    setIsLangDropdownOpen(false);
    setIsLangLoading(true);
    NProgress.start();

    // Prevent LocaleSync from interfering during the switch
    sessionStorage.setItem('language_switch_in_progress', '1');

    try {
      // === PHASE 1: Silent loading (NO UI changes) ===
      // Build target path
      let currentPath = location.pathname;
      const pathSegments = currentPath.split('/').filter(Boolean);
      if (pathSegments.length > 0 && ['ar', 'en', 'ku'].includes(pathSegments[0])) {
        currentPath = '/' + pathSegments.slice(1).join('/');
      }
      if (currentPath === '') currentPath = '/';

      let targetPath = currentPath;
      if (lang !== 'ar') {
        targetPath = `/${lang}${currentPath === '/' ? '' : currentPath}`;
      }

      const targetUrl = `${targetPath}${location.search || ''}${location.hash || ''}`;

      // Fetch translations + preload page chunk/data in PARALLEL — no state changes yet
      const [prefetched] = await Promise.all([
        prefetchLocale(lang),
        loadRouteModule(currentPath).catch(() => { }),
      ]);

      // Fire-and-forget: update backend session + persist for logged-in users
      fetch(`/lang/${lang}`, { redirect: 'manual' }).catch(() => { });
      if (user) {
        userAuthService.updateLocale(lang).catch(console.error);
      }

      // === PHASE 2: Apply everything atomically ===
      // Apply translations to React state + window globals
      applyFetchedLocale(prefetched);
      // Update language context (also flips DOM dir)
      setLanguage(lang);
      // Navigate (SPA push — no reload)
      rawNavigate(targetUrl);
      // Done!
      NProgress.done();

      // Clear the guard after LocaleSync has settled
      setTimeout(() => sessionStorage.removeItem('language_switch_in_progress'), 500);
    } catch (e) {
      console.error('Language switch failed:', e);
      NProgress.done();
      sessionStorage.removeItem('language_switch_in_progress');
      // Ultimate fallback: full backend redirect
      window.location.href = `/lang/${lang}`;
    } finally {
      setIsLangLoading(false);
    }
  };

  const languages = [
    { code: 'ar', label: 'العربية', short: 'ع', flag: '🇮🇶', native: 'عربي' },
    { code: 'en', label: 'English', short: 'EN', flag: '🇬🇧', native: 'Eng' },
    { code: 'ku', label: 'کوردی', short: 'کو', flag: '🇮🇶', native: 'کوردی' }
  ];

  const isActive = (path: string) => {
    const pathSegments = location.pathname.split('/').filter(Boolean);
    let currentPath = location.pathname;

    if (pathSegments.length > 0 && ['ar', 'en', 'ku'].includes(pathSegments[0])) {
      currentPath = '/' + pathSegments.slice(1).join('/');
    }
    if (currentPath === '') currentPath = '/';

    return currentPath === path;
  };

  const siteName = settings.siteName || 'Computiq Academy';
  const isDashboardPage = (() => {
    let currentPath = location.pathname;
    const pathSegments = currentPath.split('/').filter(Boolean);

    if (pathSegments.length > 0 && ['ar', 'en', 'ku'].includes(pathSegments[0])) {
      currentPath = '/' + pathSegments.slice(1).join('/');
    }

    return currentPath === '/dashboard';
  })();
  const currentDashboardTab = new URLSearchParams(location.search).get('tab') || 'overview';
  const dashboardMenuItems = [
    { key: 'overview', label: __('Overview'), icon: <LayoutDashboard size={16} /> },
    { key: 'courses', label: __('My Courses'), icon: <BookOpen size={16} /> },
    { key: 'certificates', label: __('My Certificates'), icon: <Award size={16} />, href: '/dashboard?tab=certificates' },
    { key: 'profile', label: __('Profile'), icon: <User size={16} /> },
    { key: 'security', label: __('Security'), icon: <Shield size={16} /> },
    { key: 'settings', label: __('Settings'), icon: <Settings size={16} /> },
  ];
  const resolveImage = (image?: string | null) => {
    if (!image) return null;
    if (image.startsWith('http') || image.startsWith('/')) return image;
    return `/storage/${image}`;
  };
  const selectedCategory = dbCategories.find((cat) => cat.slug === activeCourseType) || null;
  const selectedCategoryCourses = dbCourses.filter((course) => course.category_slug === activeCourseType).slice(0, 5);

  return (
    <nav
      className={`fixed top-0 w-full z-50 transition-all duration-300 ${isScrolled
        ? 'bg-white/92 dark:bg-slate-950/94 backdrop-blur-xl shadow-sm py-2 border-b border-[#dbeaff]/80 dark:border-slate-800'
        : 'bg-[#edf6ff] dark:bg-slate-950 py-3 border-b border-[#d9e9ff] dark:border-slate-800'
        }`}
    >
      <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-14 lg:h-16 gap-6">

          {/* Right Side: Logo & Primary Nav (RTL Start) */}
          <div className="flex items-center lg:gap-8 h-full shrink-0">
            {/* Logo */}
            <div className="flex-shrink-0 flex items-center rtl:ml-4 ltr:mr-4">
              <button onClick={() => handleNavClick('/')} className="flex items-center outline-none group">
                <Logo
                  imageClassName="h-10 w-auto object-contain transition-transform group-hover:scale-105 mx-0.5"
                  textClassName={`text-[22px] sm:text-[26px] font-black tracking-tight transition-colors duration-200 ${isScrolled ? 'text-gray-900 dark:text-white' : 'text-gray-900 dark:text-white'}`}
                />
              </button>
            </div>

            {/* Desktop Navigation */}
            <div className="hidden xl:flex items-center gap-8 h-full rtl:pr-4 ltr:pl-4 overflow-visible relative" ref={dropdownRef}>

              {/* Courses with Mega Menu */}
              <div
                className="h-full flex items-center"
                onMouseEnter={() => setIsCoursesDropdownOpen(true)}
                onMouseLeave={() => setIsCoursesDropdownOpen(false)}
              >
                <button
                  onClick={() => setIsCoursesDropdownOpen(!isCoursesDropdownOpen)}
                  className="text-[15px] font-bold transition-colors duration-200 flex items-center gap-1.5 text-gray-700 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400 group h-full"
                >
                  {__('Nav courses')}
                  <ChevronDown size={14} className={`mt-0.5 transition-transform duration-200 opacity-80 ${isCoursesDropdownOpen ? 'rotate-180 text-brand-600' : ''}`} />
                </button>

                {/* Mega Menu Dropdown */}
                {isCoursesDropdownOpen && (
                  <div className="absolute top-full rtl:right-2 ltr:left-2 w-[800px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-brand-100/70 dark:border-gray-700 flex z-50 animate-fade-in-down overflow-hidden pointer-events-auto">

                    {/* Right Column: Categories */}
                    <div className="w-[44%] bg-white dark:bg-gray-800 p-4 border-l rtl:border-l rtl:border-r-0 ltr:border-r border-gray-100 dark:border-gray-700">
                      <h3 className="font-bold text-base text-gray-900 dark:text-white mb-3 text-center">{__('Nav categories')}</h3>
                      <div className="app-scrollbar-soft flex flex-col gap-1.5 max-h-[420px] overflow-y-auto pe-1">
                        {dbCategories.map(cat => (
                          <AppLink
                            key={cat.id}
                            to={`/courses?category=${cat.slug}`}
                            onMouseEnter={() => setActiveCourseType(cat.slug)}
                            onFocus={() => setActiveCourseType(cat.slug)}
                            onClick={() => {
                              setIsCoursesDropdownOpen(false);
                              setIsOpen(false);
                            }}
                            className={`w-full text-start px-3 py-2.5 rounded-xl text-sm font-bold flex items-center gap-3 justify-between transition-all ${activeCourseType === cat.slug ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'}`}
                          >
                            <div className="flex items-center gap-3 min-w-0">
                              {resolveImage(cat.image) ? (
                                <div className="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                  <img src={resolveImage(cat.image) || ''} alt={t(cat.name)} className="w-full h-full object-cover" />
                                </div>
                              ) : (
                                <div className={`w-12 h-12 rounded-lg shrink-0 flex items-center justify-center ${activeCourseType === cat.slug ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500'}`}>
                                  <BookOpen size={16} />
                                </div>
                              )}
                              <div className="min-w-0">
                                <span className="block truncate">{t(cat.name)}</span>
                                <span className={`mt-0.5 block text-xs font-medium ${activeCourseType === cat.slug ? 'text-brand-500 dark:text-brand-300' : 'text-gray-400 dark:text-gray-500'}`}>
                                  {__('View all courses in this category')}
                                </span>
                              </div>
                            </div>
                            <ChevronLeft size={16} className={`shrink-0 transition-transform ${activeCourseType === cat.slug ? 'text-brand-600' : 'text-gray-300 dark:text-gray-600'}`} />
                          </AppLink>
                        ))}
                        {dbCategories.length === 0 && (
                          <div className="text-center py-4 text-xs text-gray-400">Loading...</div>
                        )}
                      </div>
                    </div>

                    {/* Left Column: Courses in Category */}
                    <div className="w-[56%] bg-white dark:bg-gray-800 p-4">
                      {selectedCategory && (
                        <button
                          type="button"
                          onClick={() => handleNavClick(`/courses?category=${selectedCategory.slug}`)}
                          className="group relative mb-4 block w-full overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700 bg-gradient-to-br from-slate-50 to-white dark:from-gray-900 dark:to-gray-800"
                        >
                          <div className="flex items-center gap-3 p-3">
                            <div className="w-20 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                              {resolveImage(selectedCategory.image) ? (
                                <img src={resolveImage(selectedCategory.image) || ''} alt={t(selectedCategory.name)} className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                              ) : (
                                <div className="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                  <BookOpen size={22} />
                                </div>
                              )}
                            </div>
                            <div className="min-w-0 flex-1 text-start">
                              <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-brand-500">{__('Featured Category')}</p>
                              <h4 className="mt-1 truncate text-base font-bold text-gray-900 dark:text-white">{t(selectedCategory.name)}</h4>
                              <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">{__('Browse the highlighted courses in this category')}</p>
                            </div>
                          </div>
                        </button>
                      )}

                      <div className="app-scrollbar flex flex-col gap-2.5 max-h-[330px] overflow-y-auto pe-1">
                        {selectedCategoryCourses.map(course => {
                          const img = resolveImage(course.image);
                          return (
                            <button
                              key={course.id}
                              onClick={() => handleNavClick(`/courses/${course.slug}`)}
                              className="w-full text-start p-2.5 rounded-xl flex items-center gap-3 border border-transparent hover:border-gray-100 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-all group/item"
                            >
                              <div className="w-14 h-14 rounded-lg bg-gray-100 dark:bg-gray-800 overflow-hidden shrink-0">
                                {img && <img src={img} className="w-full h-full object-cover" alt="" />}
                              </div>
                              <div className="flex flex-col flex-1">
                                <span className="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">{t(course.title)}</span>
                                <span className="text-xs text-brand-600 dark:text-brand-400 mt-0.5">{t(course.instructor_name)}</span>
                              </div>
                            </button>
                          );
                        })}

                        {activeCourseType && selectedCategoryCourses.length === 0 && (
                          <div className="flex min-h-[180px] items-center justify-center rounded-xl border border-dashed border-gray-200 dark:border-gray-700 text-center text-sm text-gray-400 dark:text-gray-500 px-6">
                            {__('There are no courses in this category yet.')}
                          </div>
                        )}

                        {activeCourseType && selectedCategoryCourses.length > 0 && (
                          <button
                            onClick={() => handleNavClick(`/courses?category=${activeCourseType}`)}
                            className="mt-1 text-center text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors"
                          >
                            {__('View all courses in this category')}
                          </button>
                        )}
                      </div>
                    </div>

                  </div>
                )}
              </div>

              {/* Paths */}
              <button
                onClick={() => handleNavClick('/paths')}
                className="text-[15px] font-bold transition-colors duration-200 flex items-center gap-1 text-gray-700 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400"
              >
                {__('Nav paths')}
              </button>
            </div>
          </div>

          {/* Middle: Custom Blue-Outlined Search Bar (Desktop) */}
          <div className="hidden lg:flex flex-1 max-w-[450px] items-center justify-center">
            <div className="relative w-full group">
              <input
                type="text"
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                onKeyDown={handleSearch}
                className="w-full bg-white dark:bg-gray-900 border border-[#c6dcff] dark:border-gray-700 focus:border-brand-500 dark:focus:border-brand-700 rounded-xl px-5 py-2.5 text-sm font-medium text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition-all shadow-sm placeholder-[#8fa9cf] dark:placeholder-gray-500 rtl:pl-10 ltr:pr-10"
                placeholder={__('Search courses')}
              />
              <div className="absolute inset-y-0 rtl:left-0 ltr:right-0 flex items-center rtl:pl-4 ltr:pr-4 pointer-events-none text-[#7ea9ea] group-focus-within:text-brand-500 transition-colors">
                <Search size={18} />
              </div>
            </div>
          </div>

          {/* Left Side: Actions (RTL End) */}
          <div className="flex items-center gap-4 shrink-0 justify-end flex-1 lg:flex-none">

            {/* Cart Icon */}
            <button onClick={() => setIsCartOpen(true)} className="relative p-2 text-gray-400 hover:text-brand-600 dark:text-gray-500 transition-colors hidden sm:block">
              <ShoppingCart size={20} />
              {cartCount > 0 && (
                <span className="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center bg-brand-600 text-white text-[10px] font-bold rounded-full border-2 border-[#edf6ff] dark:border-slate-950 px-1">
                  {cartCount > 99 ? '99+' : cartCount}
                </span>
              )}
            </button>

            {/* Auth Buttons */}
            <div className="flex items-center gap-2">
              {user ? (
                <div className="relative" ref={userDropdownRef}>
                  <button
                    onClick={() => setIsUserDropdownOpen((current) => !current)}
                    className="flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-gray-800 px-2 py-1.5 rounded-xl transition-all border border-transparent shadow-sm"
                  >
                    {(user as any).avatar ? (
                      <div className="w-8 h-8 rounded-full overflow-hidden shrink-0 border border-brand-200 dark:border-gray-600 shadow-sm">
                        <img src={resolveImage((user as any).avatar) || ''} alt={user.name} className="w-full h-full object-cover" />
                      </div>
                    ) : (
                      <div className="w-8 h-8 bg-brand-600 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-md shrink-0">
                        {user.name.charAt(0).toUpperCase()}
                      </div>
                    )}
                    <ChevronDown size={13} className={`hidden sm:block transition-transform duration-200 opacity-70 ${isUserDropdownOpen ? 'rotate-180 text-brand-600' : ''}`} />
                  </button>

                  <AnimatePresence>
                    {isUserDropdownOpen && (
                      <motion.div
                        initial={{ opacity: 0, y: -8, scale: 0.98 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: -8, scale: 0.98 }}
                        transition={{ duration: 0.18, ease: 'easeOut' }}
                        className="absolute top-[calc(100%+10px)] rtl:left-0 ltr:right-0 w-64 origin-top bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/70 dark:border-gray-700/60 py-2 z-50 overflow-hidden"
                      >
                        <div className="px-4 py-3 border-b border-gray-100 dark:border-gray-700/60">
                          <p className="text-sm font-semibold text-gray-900 dark:text-white truncate">{user.name}</p>
                          <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">{__('My Dashboard')}</p>
                        </div>

                        <div className="py-1">
                          {dashboardMenuItems.map((item) => {
                            const isActive = isDashboardPage && currentDashboardTab === item.key;

                            return (
                              <button
                                key={item.key}
                                type="button"
                                onClick={() => handleNavClick(item.href || `/dashboard?tab=${item.key}`)}
                                className={`w-full flex items-center gap-3 px-4 py-3 text-sm transition-colors ${isActive
                                  ? 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-300'
                                  : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60'
                                  }`}
                              >
                                <span className="shrink-0">{item.icon}</span>
                                <span className="font-medium">{item.label}</span>
                              </button>
                            );
                          })}
                        </div>

                        <div className="border-t border-gray-100 dark:border-gray-700/60 pt-1">
                          <button
                            type="button"
                            onClick={async () => {
                              setIsUserDropdownOpen(false);
                              await logout();
                            }}
                            className="w-full flex items-center gap-3 px-4 py-3 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                          >
                            <LogOut size={16} className="shrink-0" />
                            <span className="font-medium">{__('Logout')}</span>
                          </button>
                        </div>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>
              ) : (
                <div className="hidden sm:flex items-center">
                  <AppLink
                    to={'/login'}
                    state={{ from: location.pathname + location.search }}
                    className="bg-brand-600 text-white hover:bg-brand-700 px-5 py-2.5 rounded-xl text-[15px] font-bold transition-all shadow-md shadow-brand-500/20 active:scale-95 whitespace-nowrap"
                  >
                    {__('Login')}
                  </AppLink>
                </div>
              )}
            </div>

            {/* Dark/Light Theme Toggle */}
            <button
              onClick={toggleTheme}
              className="hidden sm:flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shadow-sm shrink-0"
              title={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
            >
              {theme === 'dark' ? <Sun size={16} /> : <Moon size={16} />}
            </button>

            {/* Separator */}
            <div className="hidden sm:block h-6 w-px bg-gray-200 dark:bg-gray-700 mx-1"></div>

            {/* Language Dropdown */}
            <div className="hidden sm:flex items-center relative" ref={langDropdownRef}>
              <button
                disabled={isLangLoading}
                onClick={() => setIsLangDropdownOpen(!isLangDropdownOpen)}
                className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm font-bold shadow-sm shrink-0 min-w-[56px] justify-center disabled:opacity-70 disabled:cursor-not-allowed border border-gray-100 dark:border-gray-700"
                title={`Current language: ${languages.find(l => l.code === language)?.label}`}
              >
                {isLangLoading ? (
                  <Loader2 size={16} className="animate-spin text-brand-600" />
                ) : (
                  <>
                    <span className="text-base leading-none">{languages.find(l => l.code === language)?.flag}</span>
                    <span className="text-xs font-bold uppercase tracking-wide">{languages.find(l => l.code === language)?.short}</span>
                    <ChevronDown size={12} className={`transition-transform duration-200 opacity-60 ${isLangDropdownOpen ? 'rotate-180 text-brand-600' : ''}`} />
                  </>
                )}
              </button>

              {isLangDropdownOpen && (
                <div className="absolute top-[calc(100%+8px)] rtl:left-0 ltr:right-0 w-48 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-xl shadow-2xl border border-gray-200/60 dark:border-gray-600/40 py-1.5 z-50 animate-fade-in-down overflow-hidden">
                  {languages.map((lang) => {
                    const isSelected = language === lang.code;
                    return (
                      <button
                        key={lang.code}
                        onClick={() => {
                          setIsLangDropdownOpen(false);
                          handleLanguageSelect(lang.code as Language);
                        }}
                        className={`w-full text-start px-3.5 py-2.5 text-[13px] transition-all duration-150 flex items-center gap-3 group ${isSelected
                            ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300'
                            : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60'
                          }`}
                      >
                        <span className="text-lg leading-none shrink-0">{lang.flag}</span>
                        <div className="flex flex-col flex-1 min-w-0">
                          <span className={`font-semibold text-[13px] ${isSelected ? 'text-brand-700 dark:text-brand-300' : 'text-gray-800 dark:text-gray-100'}`}>{lang.label}</span>
                          <span className="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">{lang.code}</span>
                        </div>
                        {isSelected && (
                          <svg className="w-4 h-4 text-brand-600 dark:text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        )}
                      </button>
                    );
                  })}
                </div>
              )}
            </div>

            {/* Mobile Menu Button */}
            <div className="flex items-center xl:hidden">
              <button
                onClick={() => setIsOpen(!isOpen)}
                className="p-2 rounded-full text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                {isOpen ? <X size={22} /> : <Menu size={22} />}
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Mobile Menu Overlay */}
      {isOpen && (
        <div className="xl:hidden bg-white dark:bg-slate-950 border-t border-[#d9e9ff] dark:border-slate-800 absolute w-full shadow-2xl animate-fade-in-down max-h-[85vh] overflow-y-auto z-50">
          <div className="p-4 flex flex-col gap-4">
            <div className="relative w-full">
              <input
                type="text"
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                onKeyDown={handleSearch}
                className="w-full bg-white border border-[#c6dcff] focus:border-brand-500 rounded-xl px-5 py-3 text-sm focus:outline-none"
                placeholder={__('Search courses')}
              />
            </div>
            <div className="flex flex-col gap-2">
              <button
                className="w-full text-start block px-4 py-3 rounded-md text-base font-bold text-gray-700 dark:text-gray-200"
              >
                {__('Nav courses')}
              </button>
              <div className="pl-6 pr-6 py-2 grid grid-cols-1 gap-2">
                {dbCategories.map((c) => (
                  <button key={c.id} onClick={() => handleNavClick(`/courses?category=${c.slug}`)} className="text-start text-sm text-gray-500 hover:text-brand-600 py-1">{t(c.name)}</button>
                ))}
              </div>
              <button
                onClick={() => handleNavClick('/paths')}
                className="w-full text-start block px-4 py-3 rounded-md text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-brand-50"
              >
                {__('Nav paths')}
              </button>
            </div>
            <div className="mt-4 border-t border-gray-100 pt-4">
              <button onClick={() => {
                  setIsOpen(false);
                  navigate('/login', { state: { from: location.pathname + location.search } });
              }} className="w-full bg-brand-600 text-white font-bold py-3 rounded-xl text-sm shadow-sm shadow-brand-500/20">{__('Login')}</button>
            </div>
          </div>
        </div>
      )}

      <CartSlideOver isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
    </nav>
  );
};

export default Header;
