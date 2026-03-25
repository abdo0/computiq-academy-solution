import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { useTranslation } from '../contexts/TranslationProvider';

const ScrollToTop = () => {
    const { pathname } = useLocation();

    useEffect(() => {
        window.scrollTo(0, 0);
    }, [pathname]);

    return null;
};

export default ScrollToTop;
