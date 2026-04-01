import { dataService, userAuthService } from './dataService';
import { normalizeRouteTarget } from '../routing/routeRegistry';
import { determineSeoParams } from '../utils/seoUtils';

export interface CheckoutBootstrapPayload {
    cart: any;
    gateways: any[];
    selectedGatewayId: number | null;
    quote: any | null;
}

export const buildCheckoutBootstrap = async (): Promise<CheckoutBootstrapPayload> => {
    const [cart, gateways] = await Promise.all([
        userAuthService.getCart().catch(() => null),
        dataService.getPaymentGateways().catch(() => []),
    ]);

    const selectedGatewayId = gateways.length > 0 ? Number(gateways[0].id) : null;
    let quote: any | null = null;

    if (selectedGatewayId) {
        const quoteResult = await dataService.getCheckoutQuote({
            payment_gateway_id: selectedGatewayId,
        }).catch(() => ({ success: false }));

        if (quoteResult?.success) {
            quote = quoteResult.quote ?? null;
        }
    }

    return {
        cart,
        gateways,
        selectedGatewayId,
        quote,
    };
};

export const resolveRouteBootstrapData = async (path: string): Promise<any> => {
    const { pathname, fullPath, searchParams } = normalizeRouteTarget(path);
    const { type, slug } = determineSeoParams(pathname);
    const seoPromise = dataService.fetchSeo(type, slug).catch(() => null);

    if (pathname === '/') {
        const [seo, homeData] = await Promise.all([
            seoPromise,
            dataService.getHomeData().catch(() => null),
        ]);

        return { path: fullPath, seo, homeData };
    }

    if (pathname === '/courses') {
        const [seo, courses, categories] = await Promise.all([
            seoPromise,
            dataService.getCourses().catch(() => null),
            dataService.getCategories().catch(() => null),
        ]);

        return { path: fullPath, seo, courses, categories };
    }

    if (pathname.startsWith('/courses/')) {
        const [seo, course] = await Promise.all([
            seoPromise,
            dataService.getCourseBySlug(pathname.replace('/courses/', '')).catch(() => null),
        ]);

        return { path: fullPath, seo, course };
    }

    if (pathname.startsWith('/learn/')) {
        const [seo, learningCourse] = await Promise.all([
            seoPromise,
            dataService.getLearningCourse(pathname.replace('/learn/', '')).catch(() => null),
        ]);

        return { path: fullPath, seo, learningCourse };
    }

    if (pathname.startsWith('/instructors/')) {
        const [seo, instructor] = await Promise.all([
            seoPromise,
            dataService.getInstructorBySlug(pathname.replace('/instructors/', '')).catch(() => null),
        ]);

        return { path: fullPath, seo, instructor };
    }

    if (pathname === '/blog') {
        const [seo, posts, pageInfo] = await Promise.all([
            seoPromise,
            dataService.getBlogPosts().catch(() => null),
            dataService.getDynamicPage('blog').catch(() => null),
        ]);

        return { path: fullPath, seo, posts, pageInfo };
    }

    if (pathname.startsWith('/blog/')) {
        const currentSlug = pathname.replace('/blog/', '');
        const [seo, post, recentPosts] = await Promise.all([
            seoPromise,
            dataService.getBlogPostBySlug(currentSlug).catch(() => null),
            dataService.getBlogPosts({ per_page: 4 }).catch(() => []),
        ]);

        return { path: fullPath, seo, post, recentPosts };
    }

    if (pathname === '/faq') {
        const [seo, faqs, pageInfo] = await Promise.all([
            seoPromise,
            dataService.getFaqs().catch(() => null),
            dataService.getDynamicPage('faq').catch(() => null),
        ]);

        return { path: fullPath, seo, faqs, pageInfo };
    }

    if (pathname === '/paths') {
        const [seo, paths] = await Promise.all([
            seoPromise,
            dataService.getPaths().catch(() => null),
        ]);

        return { path: fullPath, seo, paths };
    }

    if (pathname.startsWith('/paths/')) {
        const currentSlug = pathname.replace('/paths/', '');
        const [seo, pathData, allPaths] = await Promise.all([
            seoPromise,
            dataService.getPathBySlug(currentSlug).catch(() => null),
            dataService.getPaths().catch(() => null),
        ]);

        return { path: fullPath, seo, path: pathData, allPaths };
    }

    if (pathname.startsWith('/page/')) {
        const [seo, page] = await Promise.all([
            seoPromise,
            dataService.getPage(pathname.replace('/page/', '')).catch(() => null),
        ]);

        return { path: fullPath, seo, page };
    }

    if (pathname === '/about') {
        const [seo, pageInfo] = await Promise.all([
            seoPromise,
            dataService.getDynamicPage('about-us').catch(() => null),
        ]);

        return { path: fullPath, seo, pageInfo };
    }

    if (pathname === '/contact') {
        const [seo, pageInfo] = await Promise.all([
            seoPromise,
            dataService.getDynamicPage('contact-us').catch(() => null),
        ]);

        return { path: fullPath, seo, pageInfo };
    }

    if (pathname === '/guide') {
        const [seo, page] = await Promise.all([
            seoPromise,
            dataService.getPage('guide').catch(() => null),
        ]);

        return { path: fullPath, seo, page };
    }

    if (pathname === '/success-stories') {
        const [seo, page] = await Promise.all([
            seoPromise,
            dataService.getPage('success-stories').catch(() => null),
        ]);

        return { path: fullPath, seo, page };
    }

    if (pathname === '/how-it-works') {
        const [seo, pageInfo] = await Promise.all([
            seoPromise,
            dataService.getDynamicPage('how-it-works').catch(() => null),
        ]);

        return { path: fullPath, seo, pageInfo };
    }

    if (pathname === '/search') {
        const query = searchParams.get('q');
        const [seo, results] = await Promise.all([
            seoPromise,
            query ? dataService.searchGlobal(query).catch(() => null) : Promise.resolve(null),
        ]);

        return { path: fullPath, seo, query, results };
    }

    if (pathname === '/cart') {
        const [seo, cart] = await Promise.all([
            seoPromise,
            userAuthService.getCart().catch(() => null),
        ]);

        return { path: fullPath, seo, cart };
    }

    if (pathname === '/checkout') {
        const [seo, checkout] = await Promise.all([
            seoPromise,
            buildCheckoutBootstrap().catch(() => null),
        ]);

        return { path: fullPath, seo, checkout };
    }

    if (pathname === '/dashboard') {
        const shouldLoadDashboardCourses = searchParams.get('tab') === 'courses' || searchParams.get('payment') === 'success';

        const [seo, dashboardStats, cart, dashboardCourses] = await Promise.all([
            seoPromise,
            userAuthService.getDashboardStats().catch(() => null),
            userAuthService.getCart().catch(() => null),
            shouldLoadDashboardCourses ? userAuthService.getMyCourses().catch(() => null) : Promise.resolve(null),
        ]);

        return { path: fullPath, seo, dashboardStats, cart, dashboardCourses };
    }

    return {
        path: fullPath,
        seo: await seoPromise,
    };
};
