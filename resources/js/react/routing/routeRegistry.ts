import * as React from 'react';

const SUPPORTED_LOCALES = new Set(['ar', 'en', 'ku']);

/**
 * Each eagerRoute() call registers itself here so loadRouteModule()
 * can resolve the same closure — no separate cache needed.
 */
interface RouteEntry {
    importFn: () => Promise<any>;
    component: React.ComponentType<any> | null;
    promise: Promise<any> | null;
}

const routeEntries = new Map<string, RouteEntry>();

/**
 * Replaces React.lazy(). If the JS chunk was already loaded (by loadRouteModule
 * during the progress bar), the component renders synchronously — no Suspense
 * delay, no empty flash.  If it hasn't been loaded yet (first visit, direct URL),
 * it behaves exactly like React.lazy and throws a promise that Suspense catches.
 */
export function eagerRoute(moduleKey: string, importFn: () => Promise<any>) {
    const entry: RouteEntry = { importFn, component: null, promise: null };
    routeEntries.set(moduleKey, entry);

    return function EagerComponent(props: any) {
        // Already resolved → render synchronously
        if (entry.component) {
            return React.createElement(entry.component, props);
        }

        // Not resolved yet → kick off import & throw for Suspense
        if (!entry.promise) {
            entry.promise = importFn().then((mod) => {
                entry.component = mod.default || mod;
            });
        }
        throw entry.promise;
    };
}

// ─── Path helpers ────────────────────────────────────────────────────

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

// ─── Module preloader (called by useAppNavigate during the progress bar) ─────

export const loadRouteModule = async (path: string): Promise<void> => {
    const { pathname } = normalizeRouteTarget(path);

    let moduleKey = pathname;

    // For dynamic routes, map the actual path to its pattern key
    if (!routeEntries.has(moduleKey)) {
        if (pathname.startsWith('/courses/')) moduleKey = '/courses/:slug';
        else if (pathname.startsWith('/learn/')) moduleKey = '/learn/:courseSlug';
        else if (pathname.startsWith('/instructors/')) moduleKey = '/instructors/:slug';
        else if (pathname.startsWith('/blog/')) moduleKey = '/blog/:slug';
        else if (pathname.startsWith('/paths/')) moduleKey = '/paths/:slug';
        else if (pathname.startsWith('/page/')) moduleKey = '/page/:slug';
    }

    const entry = routeEntries.get(moduleKey);
    if (!entry || entry.component) return;            // already loaded

    if (!entry.promise) {
        entry.promise = entry.importFn().then((mod) => {
            entry.component = mod.default || mod;
        });
    }

    await entry.promise;
};
