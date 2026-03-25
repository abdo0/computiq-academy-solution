import React from 'react';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// Configure NProgress styling and behavior
NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });

/**
 * ProgressBar Component
 * This component simply injects the NProgress CSS overrides into the DOM.
 * Navigation progress starts and stops explicitly in Header.tsx (mimicking Inertia.js).
 */
const ProgressBar: React.FC = () => {
    return (
        <style>{`
            #nprogress .bar {
                background: var(--color-brand-600) !important;
                height: 3px !important;
                z-index: 9999 !important;
            }
            #nprogress .peg {
                box-shadow: 0 0 10px var(--color-brand-600), 0 0 5px var(--color-brand-600) !important;
            }
            #nprogress .spinner-icon {
                border-top-color: var(--color-brand-600) !important;
                border-left-color: var(--color-brand-600) !important;
            }
        `}</style>
    );
};

export default ProgressBar;
