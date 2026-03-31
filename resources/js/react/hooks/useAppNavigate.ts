import { useNavigate as useReactRouterNavigate, NavigateOptions } from 'react-router-dom';
import NProgress from 'nprogress';
import { useLanguage } from '../contexts/LanguageContext';
import { useRouteBootstrap } from '../contexts/RouteBootstrapContext';
import { localizeAppPath } from '../routing/routeRegistry';

/**
 * A custom navigation hook that mimics Inertia.js behavior.
 * When called, it starts the progress bar, preloads the route's JS chunk and API data,
 * and ONLY performs the actual React Router navigation once everything is ready.
 */
export const useAppNavigate = () => {
    const navigate = useReactRouterNavigate();
    const { language } = useLanguage();
    const { prepareRoute } = useRouteBootstrap();

    return async (to: string | number, options?: NavigateOptions) => {
        // Handle go back/forward
        if (typeof to === 'number') {
            navigate(to);
            return;
        }

        // Build target path with locale prefix if needed
        const targetPath = localizeAppPath(to, language);

        // 1. Start progress bar (URL remains unchanged)
        NProgress.start();

        try {
            // 2. Preload chunk & route bootstrap payload
            await prepareRoute(to);
        } catch (error) {
            console.error('Failed to preload page:', error);
        }

        // 3. Complete progress and trigger concurrent URL change + render
        NProgress.done();
        navigate(targetPath, options);
    };
};
