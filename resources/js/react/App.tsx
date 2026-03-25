import React, { useState, useEffect, Suspense } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useLocation, Outlet } from 'react-router-dom';

// Layout components loaded eagerly (always visible)
import Header from './components/Header';
import Footer from './components/Footer';
import ScrollToTop from './components/ScrollToTop';
import TwoFactorModal from './components/auth/TwoFactorModal';
import AuthModal from './components/auth/AuthModal';

// Lazy-loaded page components (code-split, like Inertia.js)
const Home = React.lazy(() => import('./components/Home'));
const AboutPage = React.lazy(() => import('./components/AboutPage'));
const LoginPage = React.lazy(() => import('./components/auth/LoginPage'));
const ContactPage = React.lazy(() => import('./components/ContactPage'));
const BlogPage = React.lazy(() => import('./components/BlogPage'));
const BlogPostDetail = React.lazy(() => import('./components/BlogPostDetail'));
const FaqPage = React.lazy(() => import('./components/FaqPage'));
const ForgotPasswordPage = React.lazy(() => import('./components/auth/ForgotPasswordPage'));
const ResetPasswordPage = React.lazy(() => import('./components/auth/ResetPasswordPage'));
const VerifyEmailPage = React.lazy(() => import('./components/auth/VerifyEmailPage'));
const SuccessStoriesPage = React.lazy(() => import('./components/SuccessStoriesPage'));
const HowItWorksPage = React.lazy(() => import('./components/HowItWorksPage'));
const GuidePage = React.lazy(() => import('./components/GuidePage'));
const CmsPage = React.lazy(() => import('./components/CmsPage'));
const CoursesPage = React.lazy(() => import('./components/CoursesPage'));
const CourseDetailsPage = React.lazy(() => import('./components/CourseDetailsPage'));
const InstructorProfilePage = React.lazy(() => import('./components/InstructorProfilePage'));
const PathsPage = React.lazy(() => import('./components/PathsPage'));

import { LanguageProvider, useLanguage } from './contexts/LanguageContext';
import { TranslationProvider, useTranslation } from './contexts/TranslationProvider';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { SettingsProvider } from './contexts/SettingsContext';
import { WhitelistProvider } from './contexts/WhitelistContext';
import { ToastContainer } from 'react-toastify';
import { HelmetProvider } from 'react-helmet-async';
import { ThemeProvider, useTheme } from './contexts/ThemeContext';

// Common Components for new architecture
import SeoWrapper from './components/common/SeoWrapper';
import LocaleSync from './components/common/LocaleSync';
import ProgressBar from './components/common/ProgressBar';
import { determineSeoParams } from './utils/seoUtils';

/**
 * Preload map: lets Header.tsx preload the JS chunk BEFORE navigating.
 * This is the core of the Inertia.js-like behavior.
 */
export const pageImportMap: Record<string, () => Promise<any>> = {
  '/': () => import('./components/Home'),
  '/about': () => import('./components/AboutPage'),
  '/contact': () => import('./components/ContactPage'),
  '/blog': () => import('./components/BlogPage'),
  '/faq': () => import('./components/FaqPage'),
  '/how-it-works': () => import('./components/HowItWorksPage'),
  '/guide': () => import('./components/GuidePage'),
  '/success-stories': () => import('./components/SuccessStoriesPage'),
  '/courses': () => import('./components/CoursesPage'),
  '/paths': () => import('./components/PathsPage'),
  '/login': () => import('./components/auth/LoginPage'),
  '/signup': () => import('./components/auth/LoginPage'),
  '/forgot-password': () => import('./components/auth/ForgotPasswordPage'),
  '/reset-password': () => import('./components/auth/ResetPasswordPage'),
};

/**
 * Preload a page's JS chunk by its path (strips locale prefix automatically),
 * and ALSO preload its required API data to mimic Inertia.js route blocking.
 */
export const preloadPage = (path: string): Promise<any> => {
  // Strip locale prefix like /en/about -> /about
  const normalized = path.replace(/^\/[a-z]{2}(\/|$)/, '/$1').replace(/\/+$/, '') || '/';
  
  const promises: Promise<any>[] = [];

  // 1. Preload JS Component Chunk
  let importFn = pageImportMap[normalized];
  if (!importFn) {
      if (normalized.startsWith('/courses/')) importFn = () => import('./components/CourseDetailsPage');
      else if (normalized.startsWith('/instructors/')) importFn = () => import('./components/InstructorProfilePage');
      else if (normalized.startsWith('/blog/')) importFn = () => import('./components/BlogPostDetail');
  }

  if (importFn) {
      promises.push(importFn());
  }
  
  // 2. Preload API Data (using dataService's built-in fetchWithCache deduplication)
  // We push the dynamic import's promise into the array so Promise.all waits for the data fetches.
  const dataPromise = import('./services/dataService').then(({ dataService }) => {
      const dataPromises = [];
      
      // Fetch SEO metadata concurrently during page preload for smooth SPA transition
      const { type, slug } = determineSeoParams(normalized);
      dataPromises.push(dataService.fetchSeo(type, slug).catch(() => null));
      
      if (normalized === '/') {
          dataPromises.push(dataService.getHomeData().catch(() => null));
      } else if (normalized === '/courses') {
          dataPromises.push(dataService.getCourses().catch(() => null));
          dataPromises.push(dataService.getCategories().catch(() => null));
      } else if (normalized.startsWith('/courses/')) {
          const slug = normalized.replace('/courses/', '');
          // @ts-ignore
          dataPromises.push(dataService.getCourseBySlug(slug).catch(() => null));
      } else if (normalized.startsWith('/instructors/')) {
          const slug = normalized.replace('/instructors/', '');
          // @ts-ignore
          dataPromises.push(dataService.getInstructorBySlug(slug).catch(() => null));
      } else if (normalized.startsWith('/blog/')) {
          const slug = normalized.replace('/blog/', '');
          // @ts-ignore
          dataPromises.push(dataService.getBlogPostBySlug(slug).catch(() => null));
      } else if (normalized === '/blog') {
          dataPromises.push(dataService.getBlogPosts().catch(() => null));
          dataPromises.push(dataService.getDynamicPage('blog').catch(() => null));
      } else if (normalized === '/faq') {
          dataPromises.push(dataService.getFaqs().catch(() => null));
          dataPromises.push(dataService.getDynamicPage('faq').catch(() => null));
      } else if (['/about', '/contact', '/guide', '/how-it-works', '/success-stories'].includes(normalized)) {
          dataPromises.push(dataService.getDynamicPage(normalized.substring(1)).catch(() => null));
      }
      return Promise.all(dataPromises);
  }).catch(() => {});
  
  promises.push(dataPromise);
  
  // Return when EVERYTHING (chunk + data) is ready
  return Promise.all(promises);
};

/**
 * GlobalPageLoader:
 * Renders a centered spinner inside the Outlet while React.lazy is downloading 
 * the target page chunk. It directly links the chunk download time to NProgress.
 */
const GlobalPageLoader: React.FC = () => {
  return (
    <div className="flex justify-center items-center min-h-[60vh] animate-fade-in">
      <div className="relative">
        <div className="absolute inset-0 rounded-full border-t-2 border-brand-200 animate-pulse"></div>
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-brand-600"></div>
      </div>
    </div>
  );
};



const LayoutWrapper: React.FC<{ onLoginClick: () => void }> = ({ onLoginClick }) => {
  const { dir, language } = useLanguage();
  const { theme } = useTheme();
  const { isLoading: isTranslationsLoading } = useTranslation();

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 dark:text-white font-sans text-gray-900 flex flex-col transition-all duration-300" dir={dir}>
      
      {/* Full-page loading overlay while translations fetch */}
      {isTranslationsLoading && (
        <div className="fixed inset-0 z-[9999] flex flex-col justify-center items-center bg-white dark:bg-gray-900 overflow-hidden">
          <div className="flex flex-col items-center gap-8 animate-fade-in">
            <img 
               src={
                 language === 'ar'
                   ? theme === 'dark' ? '/images/PNG/image_1124.png' : '/images/PNG/2T.png'
                   : theme === 'dark' ? '/images/SVG/MainLogo_01_PNG.svg' : '/images/SVG/MainLogo_03_PNG.svg'
               } 
               alt="Computiq Academy" 
               className="h-12 sm:h-16 w-auto object-contain animate-pulse" 
            />
            {/* Loading progress spinner under the logo */}
            <div className="relative">
              <div className="absolute inset-0 rounded-full border-t-2 border-brand-200 animate-pulse"></div>
              <div className="animate-spin rounded-full h-8 w-8 sm:h-10 sm:w-10 border-t-2 border-b-2 border-brand-600"></div>
            </div>
          </div>
        </div>
      )}

      <LocaleSync />
      <SeoWrapper>
        <Header onLoginClick={onLoginClick} />
        <ScrollToTop />
        <main className="flex-grow animate-fade-in pt-20">
          {/* Show spinning loader & progress bar while downloading JS chunk */}
          <Suspense fallback={<GlobalPageLoader />}>
            <Outlet />
          </Suspense>
        </main>
        <Footer />
      </SeoWrapper>
    </div>
  );
};

const CustomCloseButton = ({ closeToast }: { closeToast?: (e: React.MouseEvent<HTMLElement>) => void }) => (
  <button
    type="button"
    onClick={closeToast}
    className="ml-4 flex-shrink-0 text-gray-500 hover:text-gray-700 bg-transparent rounded-md p-1 pointer-events-auto cursor-pointer flex items-center justify-center self-start mt-1"
    aria-label="Close"
  >
    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
    </svg>
  </button>
);

const AppContent: React.FC = () => {
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const { dir } = useLanguage();
  const { user, show2FAModal, setShow2FAModal, verify2FA, resend2FA, isLoading, dev2FACode, refreshUser } = useAuth();

  const handleAuthSuccess = async () => {
    await refreshUser();
    setIsAuthModalOpen(false);
  };

  const commonRoutes = (
    <>
      <Route path="" element={<Home />} />
      <Route path="about" element={<AboutPage />} />
      <Route path="how-it-works" element={<HowItWorksPage />} />
      <Route path="guide" element={<GuidePage />} />
      <Route path="success-stories" element={<SuccessStoriesPage />} />
      <Route path="courses" element={<CoursesPage />} />
      <Route path="courses/:slug" element={<CourseDetailsPage />} />
      <Route path="paths" element={<PathsPage />} />
      <Route path="instructors/:slug" element={<InstructorProfilePage />} />
      <Route path="page/:slug" element={<CmsPage />} />
      <Route path="contact" element={<ContactPage />} />
      <Route path="blog" element={<BlogPage />} />
      <Route path="blog/:slug" element={<BlogPostDetail />} />
      <Route path="faq" element={<FaqPage />} />
      
      {/* Auth Routes */}
      <Route path="login" element={<LoginPage />} />
      <Route path="signup" element={<LoginPage />} />
      <Route path="forgot-password" element={<ForgotPasswordPage />} />
      <Route path="reset-password" element={<ResetPasswordPage />} />
      
      {/* Fallback */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </>
  );

  return (
    <>
      <Routes>
        <Route element={<LayoutWrapper onLoginClick={() => setIsAuthModalOpen(true)} />}>
          {/* Default (no prefix) */}
          {commonRoutes}

          {/* With Locale Prefix */}
          <Route path=":locale">
            {commonRoutes}
          </Route>
        </Route>
      </Routes>

      <AuthModal
        isOpen={isAuthModalOpen}
        onClose={() => {
          setIsAuthModalOpen(false);
        }}
        onSuccess={handleAuthSuccess}
      />

      <TwoFactorModal
        isOpen={show2FAModal}
        onClose={() => setShow2FAModal(false)}
        onVerify={verify2FA}
        onResend={resend2FA}
        isLoading={isLoading}
        devCode={dev2FACode}
      />

      <ToastContainer
        position={dir === 'rtl' ? "bottom-right" : "bottom-left"}
        rtl={dir === 'rtl'}
        aria-label="Notifications"
        autoClose={3000}
        hideProgressBar={true}
        closeButton={CustomCloseButton}
        newestOnTop={true}
        closeOnClick
        pauseOnHover
        draggable
        theme="colored"
        toastClassName="!rounded-sm !shadow-xl !font-medium flex items-start"
        progressClassName="!rounded-b-2xl"
      />
    </>
  );
};

function App() {
  return (
    <Router>
      <ProgressBar />
      <HelmetProvider>
        <ThemeProvider>
          <LanguageProvider>
            <TranslationProvider>
              <SettingsProvider>
                <AuthProvider>
                  <WhitelistProvider>
                    <AppContent />
                    <TwoFactorModal
                      isOpen={false}
                      onClose={() => { }}
                      onVerify={async () => true}
                      onResend={async () => { }}
                      isLoading={false}
                      devCode={null}
                    />
                  </WhitelistProvider>
                </AuthProvider>
              </SettingsProvider>
            </TranslationProvider>
          </LanguageProvider>
        </ThemeProvider>
      </HelmetProvider>
    </Router>
  );
}

export default App;