import { useNavigate as useReactRouterNavigate, NavigateOptions } from 'react-router-dom';
import NProgress from 'nprogress';
import { preloadPage } from '../App';
import { useLanguage } from '../contexts/LanguageContext';

/**
 * A custom navigation hook that mimics Inertia.js behavior.
 * When called, it starts the progress bar, preloads the route's JS chunk and API data,
 * and ONLY performs the actual React Router navigation once everything is ready.
 */
export const useAppNavigate = () => {
    const navigate = useReactRouterNavigate();
    const { language } = useLanguage();

    return async (to: string | number, options?: NavigateOptions) => {
        // Handle go back/forward
        if (typeof to === 'number') {
            navigate(to);
            return;
        }

        // Build target path with locale prefix if needed
        let targetPath = to;
        if (language !== 'ar') {
            if (to.startsWith('/') && !to.startsWith(`/${language}`)) {
                targetPath = `/${language}${to === '/' ? '' : to}`;
            }
        }

        // 1. Start progress bar (URL remains unchanged)
        NProgress.start();

        try {
            // 2. Preload chunk & data 
            await preloadPage(to);
        } catch (error) {
            console.error('Failed to preload page:', error);
        }

        // 3. Complete progress and trigger concurrent URL change + render
        NProgress.done();
        navigate(targetPath, options);
    };
};
