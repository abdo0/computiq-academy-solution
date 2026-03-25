import React, { AnchorHTMLAttributes } from 'react';
import { useAppNavigate } from '../../hooks/useAppNavigate';

interface AppLinkProps extends AnchorHTMLAttributes<HTMLAnchorElement> {
    to: string;
    children: React.ReactNode;
    className?: string;
    onClick?: (e: React.MouseEvent<HTMLAnchorElement>) => void;
}

/**
 * A drop-in replacement for react-router-dom's <Link> that strictly enforces 
 * the Inertia.js-style navigation prefetching and NProgress bar behavior.
 */
const AppLink: React.FC<AppLinkProps> = ({ to, children, className, onClick, ...props }) => {
    const navigate = useAppNavigate();

    const handleClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
        // Prevent default browser navigation
        e.preventDefault();
        
        // Let the custom hook handle the progress bar, cache deduplication, and routing
        navigate(to);
        
        if (onClick) {
            onClick(e);
        }
    };

    return (
        <a href={to} onClick={handleClick} className={className} {...props}>
            {children}
        </a>
    );
};

export default AppLink;
