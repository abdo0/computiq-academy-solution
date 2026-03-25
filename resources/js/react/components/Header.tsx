import React, { useState, useRef, useEffect } from 'react';
import { useLanguage, Language } from '../contexts/LanguageContext';
import { useAuth } from '../contexts/AuthContext';
import { useSettings } from '../contexts/SettingsContext';
import { useLocation, useNavigate as useRawNavigate } from 'react-router-dom';
import { useTheme } from '../contexts/ThemeContext';
import { userAuthService, orgAuthService, dataService } from '../services/dataService';
import { Menu, X, ChevronDown, ChevronLeft, Search, ShoppingCart, Sun, Moon, Loader2 } from 'lucide-react';
import { useTranslation } from '../contexts/TranslationProvider';
import { useAppNavigate } from '../hooks/useAppNavigate';
import Logo from './Logo';
import AppLink from './common/AppLink';
import NProgress from 'nprogress';
import { preloadPage } from '../App';

interface HeaderProps {
  onLoginClick: () => void;
}

const Header: React.FC<HeaderProps> = ({ onLoginClick }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isCoursesDropdownOpen, setIsCoursesDropdownOpen] = useState(false);
  const [isLangDropdownOpen, setIsLangDropdownOpen] = useState(false);
  const [isLangLoading, setIsLangLoading] = useState(false);
  const [activeCourseType, setActiveCourseType] = useState<string | null>(null);
  
  const [dbCategories, setDbCategories] = useState<any[]>([]);
  const [dbCourses, setDbCourses] = useState<any[]>([]);
  
  const [isScrolled, setIsScrolled] = useState(false);
  const { language, setLanguage } = useLanguage();
  const { __, t, prefetchLocale, applyFetchedLocale } = useTranslation();
  const { user } = useAuth();
  const { settings } = useSettings();
  const { theme, toggleTheme } = useTheme();
  const dropdownRef = useRef<HTMLDivElement>(null);
  const langDropdownRef = useRef<HTMLDivElement>(null);

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
    dataService.getHomeData().then((data) => {
      const cats = data.course_categories || [];
      setDbCategories(cats);
      setDbCourses(data.courses || []);
      if (cats.length > 0) setActiveCourseType(cats[0].slug);
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
    navigate(path);
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

      // Fetch translations + preload page chunk/data in PARALLEL — no state changes yet
      const [prefetched] = await Promise.all([
        prefetchLocale(lang),
        preloadPage(currentPath).catch(() => {}),
      ]);

      // Fire-and-forget: update backend session + persist for logged-in users
      fetch(`/lang/${lang}`, { redirect: 'manual' }).catch(() => {});
      if (user) {
        userAuthService.updateLocale(lang).catch(console.error);
      }

      // === PHASE 2: Apply everything atomically ===
      // Apply translations to React state + window globals
      applyFetchedLocale(prefetched);
      // Update language context (also flips DOM dir)
      setLanguage(lang);
      // Navigate (SPA push — no reload)
      rawNavigate(targetPath);
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

  return (
    <nav
      className={`fixed top-0 w-full z-50 transition-all duration-300 ${isScrolled
        ? 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-md shadow-sm py-2'
        : 'bg-[#f4f7fb] dark:bg-gray-900 py-3 border-b border-[#eef2fc] dark:border-gray-800'
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
                    <div className="absolute top-full rtl:right-2 ltr:left-2 w-[550px] bg-white dark:bg-gray-800 rounded-md shadow-xl hover:shadow-2xl border border-brand-50 dark:border-gray-700 flex z-50 animate-fade-in-down overflow-hidden pointer-events-auto">
                        
                        {/* Right Column: Categories */}
                        <div className="w-[40%] bg-white dark:bg-gray-800 p-4 border-l rtl:border-l rtl:border-r-0 ltr:border-r border-gray-100 dark:border-gray-700">
                           <h3 className="font-bold text-base text-gray-900 dark:text-white mb-3 text-center">{__('Nav categories')}</h3>
                           <div className="flex flex-col gap-1 max-h-[380px] overflow-y-auto" style={{ scrollbarWidth: 'thin' }}>
                              {dbCategories.map(cat => (
                                <button 
                                  key={cat.id}
                                  onMouseEnter={() => setActiveCourseType(cat.slug)}
                                  className={`w-full text-start px-4 py-3 rounded-md text-sm font-bold flex items-center justify-between transition-colors ${activeCourseType === cat.slug ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'}`}
                                >
                                  {t(cat.name)}
                                  <ChevronLeft size={16} className={`transition-transform ${activeCourseType === cat.slug ? 'text-brand-600' : 'text-transparent'}`} />
                                </button>
                              ))}
                              {dbCategories.length === 0 && (
                                <div className="text-center py-4 text-xs text-gray-400">Loading...</div>
                              )}
                           </div>
                        </div>

                        {/* Left Column: Courses in Category */}
                        <div className="w-[60%] bg-white dark:bg-gray-800 p-4 py-6">
                           <div className="flex flex-col gap-3 max-h-[360px] overflow-y-auto" style={{ scrollbarWidth: 'thin' }}>
                              {dbCourses.filter(c => c.category_slug === activeCourseType).slice(0, 5).map(course => {
                                 const img = course.image?.startsWith('http') || course.image?.startsWith('/assets/') ? course.image : `/storage/${course.image}`;
                                 return (
                                 <button 
                                    key={course.id}
                                    onClick={() => handleNavClick(`/courses/${course.slug}`)}
                                    className="w-full text-start p-2 rounded-md flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group/item"
                                 >
                                    <div className="w-12 h-12 rounded-md bg-gray-100 dark:bg-gray-800 overflow-hidden shrink-0">
                                      {img && <img src={img} className="w-full h-full object-cover" alt="" />}
                                    </div>
                                    <div className="flex flex-col flex-1">
                                      <span className="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">{t(course.title)}</span>
                                      <span className="text-xs text-brand-600 dark:text-brand-400 mt-0.5">{t(course.instructor_name)}</span>
                                    </div>
                                 </button>
                                 );
                              })}
                              
                              {activeCourseType && dbCourses.filter(c => c.category_slug === activeCourseType).length === 0 && (
                                <div className="text-center py-10 text-gray-400 text-sm w-full">لا توجد دورات في هذا القسم حالياً</div>
                              )}
                              
                              {activeCourseType && dbCourses.filter(c => c.category_slug === activeCourseType).length > 0 && (
                                <button
                                  onClick={() => handleNavClick(`/courses?category=${activeCourseType}`)}
                                  className="mt-2 text-center text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors"
                                >
                                  عرض كل دورات القسم
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
                className="w-full bg-white dark:bg-gray-900 border border-[#b4c8f0] dark:border-gray-700 focus:border-brand-500 dark:focus:border-brand-700 rounded-xl px-5 py-2.5 text-sm font-medium text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition-all shadow-sm placeholder-[#a3b1c6] dark:placeholder-gray-500 rtl:pl-10 ltr:pr-10" 
                placeholder={__('Search placeholder')} 
              />
              <div className="absolute inset-y-0 rtl:left-0 ltr:right-0 flex items-center rtl:pl-4 ltr:pr-4 pointer-events-none text-[#93b0ef] group-focus-within:text-brand-500 transition-colors">
                <Search size={18} />
              </div>
            </div>
          </div>

          {/* Left Side: Actions (RTL End) */}
          <div className="flex items-center gap-4 shrink-0 justify-end flex-1 lg:flex-none">

            {/* Cart Icon */}
            <button className="relative p-2 text-gray-400 hover:text-brand-600 dark:text-gray-500 transition-colors hidden sm:block">
               <ShoppingCart size={20} />
               <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 border border-white dark:border-gray-900 rounded-full"></span>
            </button>

            {/* Auth Buttons */}
            <div className="flex items-center gap-2">
              {user ? (
                <button
                  onClick={() => handleNavClick('/dashboard')}
                  className="flex items-center gap-2 text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-gray-800 px-2 py-1.5 rounded-xl transition-all border border-transparent shadow-sm"
                >
                  <div className="w-8 h-8 bg-brand-600 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-md">
                    {user.name.charAt(0).toUpperCase()}
                  </div>
                </button>
              ) : (
                <div className="hidden sm:flex items-center gap-3">
                  <AppLink
                    to={'/login'}
                    className="text-brand-600 dark:text-brand-400 bg-transparent border-transparent hover:bg-brand-50 dark:hover:bg-brand-900/30 px-4 py-2 text-[15px] font-bold transition-colors whitespace-nowrap"
                  >
                    {__('Login')}
                  </AppLink>
                  <AppLink
                    to={'/signup'}
                    className="bg-brand-600 text-white hover:bg-brand-700 px-5 py-2.5 rounded-md text-[15px] font-bold transition-all shadow-md active:scale-95 whitespace-nowrap"
                  >
                    {__('Sign up')}
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
                        className={`w-full text-start px-3.5 py-2.5 text-[13px] transition-all duration-150 flex items-center gap-3 group ${
                          isSelected
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
        <div className="xl:hidden bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 absolute w-full shadow-2xl animate-fade-in-down max-h-[85vh] overflow-y-auto z-50">
           <div className="p-4 flex flex-col gap-4">
              <div className="relative w-full">
                <input 
                  type="text" 
                  className="w-full bg-white border border-[#b4c8f0] focus:border-brand-500 rounded-xl px-5 py-3 text-sm focus:outline-none" 
                  placeholder={__('Search placeholder')} 
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
              <div className="grid grid-cols-2 gap-3 mt-4 border-t border-gray-100 pt-4">
                 <button onClick={() => handleNavClick('/login')} className="bg-brand-50 text-brand-600 font-bold py-3 rounded-md text-sm">{__('Login')}</button>
                 <button onClick={() => handleNavClick('/signup')} className="bg-brand-600 text-white font-bold py-3 rounded-md text-sm shadow-sm">{__('Sign up')}</button>
              </div>
           </div>
        </div>
      )}
    </nav>
  );
};

export default Header;