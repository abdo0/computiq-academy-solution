const SUPPORTED_LOCALES = new Set(['ar', 'en', 'ku']);

export const pageImportMap: Record<string, () => Promise<any>> = {
    '/': () => import('../components/Home'),
    '/about': () => import('../components/AboutPage'),
    '/contact': () => import('../components/ContactPage'),
    '/blog': () => import('../components/BlogPage'),
    '/faq': () => import('../components/FaqPage'),
    '/how-it-works': () => import('../components/HowItWorksPage'),
    '/guide': () => import('../components/GuidePage'),
    '/success-stories': () => import('../components/SuccessStoriesPage'),
    '/courses': () => import('../components/CoursesPage'),
    '/paths': () => import('../components/PathsPage'),
    '/paths/:slug': () => import('../components/PathDetailPage'),
    '/page/:slug': () => import('../components/CmsPage'),
    '/login': () => import('../components/auth/LoginPage'),
    '/signup': () => import('../components/auth/SignupPage'),
    '/forgot-password': () => import('../components/auth/ForgotPasswordPage'),
    '/reset-password': () => import('../components/auth/ResetPasswordPage'),
    '/dashboard': () => import('../components/dashboard/DashboardPage'),
    '/cart': () => import('../components/cart/CartPage'),
    '/checkout': () => import('../components/cart/CheckoutPage'),
    '/search': () => import('../components/SearchPage'),
};

export const normalizeRouteTarget = (path: string): { pathname: string; fullPath: string; searchParams: URLSearchParams } => {
    const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'http://localhost';
    const url = new URL(path, baseUrl);
    const pathSegments = url.pathname.split('/').filter(Boolean);

    if (pathSegments.length > 0 && SUPPORTED_LOCALES.has(pathSegments[0])) {
        pathSegments.shift();
    }

    const pathname = pathSegments.length > 0 ? `/${pathSegments.join('/')}` : '/';
    const queryString = url.searchParams.toString();

    return {
        pathname,
        fullPath: queryString ? `${pathname}?${queryString}` : pathname,
        searchParams: url.searchParams,
    };
};

export const localizeAppPath = (to: string, language: string): string => {
    if (language === 'ar') {
        return to;
    }

    if (to.startsWith('/') && !to.startsWith(`/${language}`)) {
        return `/${language}${to === '/' ? '' : to}`;
    }

    return to;
};

export const loadRouteModule = async (path: string): Promise<void> => {
    const { pathname } = normalizeRouteTarget(path);

    let importFn = pageImportMap[pathname];

    if (!importFn) {
        if (pathname.startsWith('/courses/')) importFn = () => import('../components/CourseDetailsPage');
        else if (pathname.startsWith('/instructors/')) importFn = () => import('../components/InstructorProfilePage');
        else if (pathname.startsWith('/blog/')) importFn = () => import('../components/BlogPostDetail');
        else if (pathname.startsWith('/paths/')) importFn = () => import('../components/PathDetailPage');
        else if (pathname.startsWith('/page/')) importFn = () => import('../components/CmsPage');
    }

    if (importFn) {
        await importFn();
    }
};
