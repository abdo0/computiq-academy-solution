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
const SignupPage = React.lazy(() => import('./components/auth/SignupPage'));
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
const PathDetailPage = React.lazy(() => import('./components/PathDetailPage'));
const DashboardPage = React.lazy(() => import('./components/dashboard/DashboardPage'));
const CartPage = React.lazy(() => import('./components/cart/CartPage'));
const CheckoutPage = React.lazy(() => import('./components/cart/CheckoutPage'));
const SearchPage = React.lazy(() => import('./components/SearchPage'));

import { LanguageProvider, useLanguage } from './contexts/LanguageContext';
import { TranslationProvider, useTranslation } from './contexts/TranslationProvider';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { SettingsProvider, useSettings } from './contexts/SettingsContext';
import { WhitelistProvider } from './contexts/WhitelistContext';
import { CartProvider, useCart } from './contexts/CartContext';
import { RouteBootstrapProvider, useRouteBootstrap } from './contexts/RouteBootstrapContext';
import { ToastContainer } from 'react-toastify';
import { HelmetProvider } from 'react-helmet-async';
import { ThemeProvider, useTheme } from './contexts/ThemeContext';
import { normalizeRouteTarget } from './routing/routeRegistry';

// Common Components for new architecture
import SeoWrapper from './components/common/SeoWrapper';
import LocaleSync from './components/common/LocaleSync';
import ProgressBar from './components/common/ProgressBar';
import FullScreenLoader from './components/common/FullScreenLoader';

/**
 * GlobalPageLoader:
 * Renders a centered spinner inside the Outlet while React.lazy is downloading 
 * the target page chunk. It directly links the chunk download time to NProgress.
 */
const GlobalPageLoader: React.FC = () => {
  const { __ } = useTranslation();

  return <FullScreenLoader progress={85} label={__('Loading page...')} />;
};



const LayoutWrapper: React.FC<{ onLoginClick: () => void }> = ({ onLoginClick }) => {
  const { dir } = useLanguage();

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 dark:text-white font-sans text-gray-900 flex flex-col transition-all duration-300" dir={dir}>
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
    className="ml-3 rtl:mr-3 rtl:ml-0 flex-shrink-0 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 bg-gray-50/50 hover:bg-gray-100 dark:bg-gray-800/50 dark:hover:bg-gray-700 rounded-lg p-1.5 transition-colors pointer-events-auto cursor-pointer flex items-center justify-center self-start"
    aria-label="Close"
  >
    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
    </svg>
  </button>
);

const isProtectedRoute = (pathname: string): boolean => {
  const normalized = normalizeRouteTarget(pathname.replace(/\/+$/, '') || '/').pathname;

  return normalized === '/dashboard'
    || normalized === '/cart'
    || normalized === '/checkout';
};

const AppContent: React.FC = () => {
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const location = useLocation();
  const { dir } = useLanguage();
  const { theme } = useTheme();
  const { __, isLoading: isTranslationsLoading } = useTranslation();
  const { isLoading: isSettingsLoading } = useSettings();
  const { user, isInitialized: isAuthInitialized, show2FAModal, setShow2FAModal, verify2FA, resend2FA, isLoading, dev2FACode, refreshUser } = useAuth();
  const { isInitialized: isCartInitialized } = useCart();
  const { state: routeBootstrapState, prepareRoute, getPayloadForPath } = useRouteBootstrap();
  const [isRouteReady, setIsRouteReady] = useState(false);

  const handleAuthSuccess = async () => {
    await refreshUser();
    setIsAuthModalOpen(false);
  };

  useEffect(() => {
    let isMounted = true;
    const currentTarget = `${location.pathname}${location.search}`;
    const requiresAuth = isProtectedRoute(location.pathname);

    const bootstrapRoute = async () => {
      if (isTranslationsLoading || isSettingsLoading || !isAuthInitialized) {
        if (isMounted) {
          setIsRouteReady(false);
        }
        return;
      }

      if (requiresAuth && !user) {
        if (isMounted) {
          setIsRouteReady(true);
        }
        return;
      }

      if (getPayloadForPath(currentTarget)) {
        if (isMounted) {
          setIsRouteReady(true);
        }
        return;
      }

      if (isMounted) {
        setIsRouteReady(false);
      }

      try {
        await prepareRoute(currentTarget);
      } catch (error) {
        console.error('Initial route bootstrap failed:', error);
      } finally {
        if (isMounted) {
          setIsRouteReady(true);
        }
      }
    };

    void bootstrapRoute();

    return () => {
      isMounted = false;
    };
  }, [getPayloadForPath, isAuthInitialized, isSettingsLoading, isTranslationsLoading, location.pathname, location.search, prepareRoute, user]);

  const currentTarget = `${location.pathname}${location.search}`;
  const currentBootstrapPath = normalizeRouteTarget(currentTarget).fullPath;
  const shouldWaitForCart = Boolean(user);

  const loaderSteps = [
    {
      key: 'translations',
      label: __('Loading translations...'),
      done: !isTranslationsLoading,
      active: isTranslationsLoading,
    },
    {
      key: 'settings',
      label: __('Loading settings...'),
      done: !isSettingsLoading,
      active: !isTranslationsLoading && isSettingsLoading,
    },
    {
      key: 'auth',
      label: __('Checking account...'),
      done: isAuthInitialized,
      active: !isTranslationsLoading && !isSettingsLoading && !isAuthInitialized,
    },
    {
      key: 'cart',
      label: __('Loading cart...'),
      done: !shouldWaitForCart || isCartInitialized,
      active: !isTranslationsLoading && !isSettingsLoading && isAuthInitialized && shouldWaitForCart && !isCartInitialized,
    },
    {
      key: 'route-module',
      label: __('Preparing page...'),
      done: routeBootstrapState.path === currentBootstrapPath && routeBootstrapState.moduleStatus === 'ready',
      active: routeBootstrapState.path === currentBootstrapPath && routeBootstrapState.moduleStatus === 'loading',
    },
    {
      key: 'route-data',
      label: __('Loading page data...'),
      done: routeBootstrapState.path === currentBootstrapPath && routeBootstrapState.dataStatus === 'ready',
      active: routeBootstrapState.path === currentBootstrapPath && routeBootstrapState.dataStatus === 'loading',
    },
  ];

  const completedSteps = loaderSteps.filter((step) => step.done).length;
  const loaderProgress = Math.round((completedSteps / loaderSteps.length) * 100);
  const activeStep = loaderSteps.find((step) => !step.done);
  const loaderLabel = activeStep?.label || __('Almost ready...');

  if (isTranslationsLoading || isSettingsLoading || !isAuthInitialized || (shouldWaitForCart && !isCartInitialized) || !isRouteReady) {
    return (
      <FullScreenLoader
        progress={loaderProgress}
        label={loaderLabel}
        steps={loaderSteps.map((step) => ({
          key: step.key,
          label: step.label,
          status: step.done ? 'done' : step.active ? 'active' : 'pending',
        }))}
      />
    );
  }

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
      <Route path="paths/:slug" element={<PathDetailPage />} />
      <Route path="instructors/:slug" element={<InstructorProfilePage />} />
      <Route path="page/:slug" element={<CmsPage />} />
      <Route path="contact" element={<ContactPage />} />
      <Route path="blog" element={<BlogPage />} />
      <Route path="blog/:slug" element={<BlogPostDetail />} />
      <Route path="faq" element={<FaqPage />} />
      
      {/* Auth Routes */}
      <Route path="login" element={<LoginPage />} />
      <Route path="signup" element={<SignupPage />} />
      <Route path="forgot-password" element={<ForgotPasswordPage />} />
      <Route path="reset-password" element={<ResetPasswordPage />} />
      
      {/* Dashboard (authenticated) */}
      <Route path="dashboard" element={<DashboardPage />} />
      <Route path="cart" element={<CartPage />} />
      <Route path="checkout" element={<CheckoutPage />} />
      <Route path="search" element={<SearchPage />} />

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
        theme={theme === 'dark' ? 'dark' : 'light'}
        toastClassName="!rounded-2xl !shadow-2xl dark:!shadow-gray-900/50 !font-medium !p-4 !border !border-gray-100 dark:!border-gray-800 !bg-white/95 dark:!bg-gray-900/95 !backdrop-blur-md flex justify-between items-start !font-sans"
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
                <RouteBootstrapProvider>
                  <AuthProvider>
                    <WhitelistProvider>
                      <CartProvider>
                        <AppContent />
                      </CartProvider>
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
                </RouteBootstrapProvider>
              </SettingsProvider>
            </TranslationProvider>
          </LanguageProvider>
        </ThemeProvider>
      </HelmetProvider>
    </Router>
  );
}

export default App;
