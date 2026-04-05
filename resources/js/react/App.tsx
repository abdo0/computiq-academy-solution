import React, { useState, useEffect, Suspense } from 'react';
import { flushSync } from 'react-dom';
import { BrowserRouter as Router, Routes, Route, Navigate, useLocation, Outlet } from 'react-router-dom';

// Layout components loaded eagerly (always visible)
import Header from './components/Header';
import Footer from './components/Footer';
import ScrollToTop from './components/ScrollToTop';
import TwoFactorModal from './components/auth/TwoFactorModal';
import AuthModal from './components/auth/AuthModal';

// Lazy-loaded page components (code-split, like Inertia.js)
const Home = eagerRoute('/', () => import('./components/Home'));
const AboutPage = eagerRoute('/about', () => import('./components/AboutPage'));
const LoginPage = eagerRoute('/login', () => import('./components/auth/LoginPage'));
const SignupPage = eagerRoute('/signup', () => import('./components/auth/SignupPage'));
const ContactPage = eagerRoute('/contact', () => import('./components/ContactPage'));
const BlogPage = eagerRoute('/blog', () => import('./components/BlogPage'));
const BlogPostDetail = eagerRoute('/blog/:slug', () => import('./components/BlogPostDetail'));
const FaqPage = eagerRoute('/faq', () => import('./components/FaqPage'));
const ForgotPasswordPage = eagerRoute('/forgot-password', () => import('./components/auth/ForgotPasswordPage'));
const ResetPasswordPage = eagerRoute('/reset-password', () => import('./components/auth/ResetPasswordPage'));
const VerifyEmailPage = eagerRoute('/verify-email', () => import('./components/auth/VerifyEmailPage'));
const SuccessStoriesPage = eagerRoute('/success-stories', () => import('./components/SuccessStoriesPage'));
const HowItWorksPage = eagerRoute('/how-it-works', () => import('./components/HowItWorksPage'));
const GuidePage = eagerRoute('/guide', () => import('./components/GuidePage'));
const CmsPage = eagerRoute('/page/:slug', () => import('./components/CmsPage'));
const CoursesPage = eagerRoute('/courses', () => import('./components/CoursesPage'));
const CourseDetailsPage = eagerRoute('/courses/:slug', () => import('./components/CourseDetailsPage'));
const LearnCoursePage = eagerRoute('/learn/:courseSlug', () => import('./components/learning/LearnCoursePage'));
const InstructorProfilePage = eagerRoute('/instructors/:slug', () => import('./components/InstructorProfilePage'));
const PathsPage = eagerRoute('/paths', () => import('./components/PathsPage'));
const PathDetailPage = eagerRoute('/paths/:slug', () => import('./components/PathDetailPage'));
const DashboardPage = eagerRoute('/dashboard', () => import('./components/dashboard/DashboardPage'));
const CartPage = eagerRoute('/cart', () => import('./components/cart/CartPage'));
const CheckoutPage = eagerRoute('/checkout', () => import('./components/cart/CheckoutPage'));
const SearchPage = eagerRoute('/search', () => import('./components/SearchPage'));

import { LanguageProvider, useLanguage } from './contexts/LanguageContext';
import { TranslationProvider, useTranslation } from './contexts/TranslationProvider';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { SettingsProvider, useSettings } from './contexts/SettingsContext';
import { WhitelistProvider } from './contexts/WhitelistContext';
import { CartProvider } from './contexts/CartContext';
import { RouteBootstrapProvider, useRouteBootstrap } from './contexts/RouteBootstrapContext';
import { ToastContainer } from 'react-toastify';
import { HelmetProvider } from 'react-helmet-async';
import { ThemeProvider, useTheme } from './contexts/ThemeContext';
import { normalizeRouteTarget, eagerRoute } from './routing/routeRegistry';

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
  return null;
};



const LayoutWrapper: React.FC<{ onLoginClick: () => void; routeKey: string }> = ({ onLoginClick, routeKey }) => {
  const { dir } = useLanguage();

  return (
    <div className="min-h-screen bg-[#f8fbff] dark:bg-slate-950 dark:text-white font-sans text-[#22314d] flex flex-col transition-all duration-300" dir={dir}>
      <LocaleSync />
      <SeoWrapper>
        <Header onLoginClick={onLoginClick} />
        <ScrollToTop />
        <main className="flex-grow pt-20">
          {/* Show spinning loader & progress bar while downloading JS chunk */}
          <Suspense fallback={<GlobalPageLoader />}>
            <div key={routeKey}>
              <Outlet />
            </div>
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

/**
 * Determines which routes require authentication before rendering.
 *
 * ⚠️  IMPORTANT: /cart must NOT be listed here.
 *
 * When a route is "protected", the navigation flow in AppContent skips
 * `commitRenderedRoute()` for unauthenticated users.  This means
 * `waitForRenderedRoute()` inside useAppNavigate never resolves,
 * so NProgress.done() never fires and the progress bar hangs forever.
 *
 * /cart is accessible to guests (they can browse and add courses before
 * logging in).  The cart page itself handles the empty/guest state.
 * /checkout is protected because payment requires authentication.
 */
const isProtectedRoute = (pathname: string): boolean => {
  const normalized = normalizeRouteTarget(pathname.replace(/\/+$/, '') || '/').pathname;

  return normalized === '/dashboard'
    || normalized === '/checkout'
    || normalized.startsWith('/learn/');
};

const AppContent: React.FC = () => {
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const location = useLocation();
  const { dir } = useLanguage();
  const { theme } = useTheme();
  const { __, isLoading: isTranslationsLoading } = useTranslation();
  const { isLoading: isSettingsLoading } = useSettings();
  const { user, isInitialized: isAuthInitialized, show2FAModal, setShow2FAModal, verify2FA, resend2FA, isLoading, dev2FACode, refreshUser } = useAuth();
  const { state: routeBootstrapState, prepareRoute, commitRenderedRoute, getPreparedPayloadForPath } = useRouteBootstrap();
  const [isRouteReady, setIsRouteReady] = useState(false);
  const [hasCompletedInitialBoot, setHasCompletedInitialBoot] = useState(false);
  const [displayLocation, setDisplayLocation] = useState(location);

  const handleAuthSuccess = async () => {
    await refreshUser();
    setIsAuthModalOpen(false);
  };

  useEffect(() => {
    if (hasCompletedInitialBoot) {
      return;
    }

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
          flushSync(() => {
            commitRenderedRoute(currentTarget);
            setDisplayLocation(location);
            setIsRouteReady(true);
          });
        }
        return;
      }

      if (getPreparedPayloadForPath(currentTarget)) {
        if (isMounted) {
          flushSync(() => {
            commitRenderedRoute(currentTarget);
            setDisplayLocation(location);
            setIsRouteReady(true);
          });
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
          flushSync(() => {
            commitRenderedRoute(currentTarget);
            setDisplayLocation(location);
            setIsRouteReady(true);
          });
        }
      }
    };

    void bootstrapRoute();

    return () => {
      isMounted = false;
    };
  }, [commitRenderedRoute, getPreparedPayloadForPath, hasCompletedInitialBoot, isAuthInitialized, isSettingsLoading, isTranslationsLoading, location, prepareRoute, user]);

  const currentTarget = `${location.pathname}${location.search}`;
  const currentBootstrapPath = normalizeRouteTarget(currentTarget).fullPath;
  const bootstrapTrackedPath = routeBootstrapState.pendingTargetPath || routeBootstrapState.renderedPath;

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
      key: 'route-module',
      label: __('Preparing page...'),
      done: bootstrapTrackedPath === currentBootstrapPath && routeBootstrapState.moduleStatus === 'ready',
      active: bootstrapTrackedPath === currentBootstrapPath && routeBootstrapState.moduleStatus === 'loading',
    },
    {
      key: 'route-data',
      label: __('Loading page data...'),
      done: bootstrapTrackedPath === currentBootstrapPath && routeBootstrapState.dataStatus === 'ready',
      active: bootstrapTrackedPath === currentBootstrapPath && routeBootstrapState.dataStatus === 'loading',
    },
  ];

  const completedSteps = loaderSteps.filter((step) => step.done).length;
  const loaderProgress = Math.round((completedSteps / loaderSteps.length) * 100);
  const activeStep = loaderSteps.find((step) => !step.done);
  const loaderLabel = activeStep?.label || __('Almost ready...');
  /**
   * shouldBlockForInitialBoot — controls the FullScreenLoader visibility.
   *
   * ⚠️  DO NOT add cart initialization here.
   *
   * Previously, this included `(shouldWaitForCart && !isCartInitialized)`.
   * That caused a cascading bug:
   *   1. CartContext's useEffect set `isInitialized = false` on every
   *      `user` change (even null → null).
   *   2. During SPA navigation the cart would briefly flicker to
   *      uninitialized, causing the FullScreenLoader to reappear.
   *   3. The NProgress bar would hang because the loader blocked the
   *      route commit.
   *
   * The cart fetches data in the background via CartContext and does NOT
   * need to block the initial page render.  Route-specific cart data is
   * pre-fetched by routeBootstrap for /cart and /checkout pages.
   */
  const shouldBlockForInitialBoot = isTranslationsLoading || isSettingsLoading || !isAuthInitialized || !isRouteReady;

  useEffect(() => {
    if (!shouldBlockForInitialBoot && !hasCompletedInitialBoot) {
      React.startTransition(() => {
        setHasCompletedInitialBoot(true);
      });
    }
  }, [hasCompletedInitialBoot, shouldBlockForInitialBoot]);

  useEffect(() => {
    if (!hasCompletedInitialBoot) {
      return;
    }

    const actualTarget = `${location.pathname}${location.search}`;
    const displayedTarget = `${displayLocation.pathname}${displayLocation.search}`;

    if (actualTarget === displayedTarget) {
      return;
    }

    const requiresAuth = isProtectedRoute(location.pathname);

    // Fast path: auth redirect or data already prepared — commit immediately
    if (requiresAuth && !user) {
      flushSync(() => {
        commitRenderedRoute(actualTarget);
        setDisplayLocation(location);
      });
      return;
    }

    if (getPreparedPayloadForPath(actualTarget)) {
      flushSync(() => {
        commitRenderedRoute(actualTarget);
        setDisplayLocation(location);
      });
      return;
    }

    // Slow path: data not prepared (e.g. browser back/forward, direct URL entry)
    let isMounted = true;

    const syncDisplayedRoute = async () => {
      try {
        await prepareRoute(actualTarget);
      } catch (error) {
        console.error('Navigation bootstrap failed:', error);
      } finally {
        if (isMounted) {
          flushSync(() => {
            commitRenderedRoute(actualTarget);
            setDisplayLocation(location);
          });
        }
      }
    };

    void syncDisplayedRoute();

    return () => {
      isMounted = false;
    };
  }, [commitRenderedRoute, displayLocation.pathname, displayLocation.search, getPreparedPayloadForPath, hasCompletedInitialBoot, location, prepareRoute, user]);

  if (!hasCompletedInitialBoot && shouldBlockForInitialBoot) {
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
      <Route path="learn/:courseSlug" element={<LearnCoursePage />} />
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
      <Routes location={displayLocation}>
        <Route element={<LayoutWrapper onLoginClick={() => setIsAuthModalOpen(true)} routeKey={`${displayLocation.pathname}${displayLocation.search}`} />}>
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
