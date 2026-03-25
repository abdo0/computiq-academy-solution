export const determineSeoParams = (path: string): { type: string, slug?: string } => {
    let type = 'home';
    let slug = undefined;

    if (path === '/' || path === '') {
        type = 'home';
    } else if (path.startsWith('/courses/')) {
        slug = path.replace('/courses/', '');
        type = slug ? 'course' : 'courses';
    } else if (path === '/courses') {
        type = 'courses';
    } else if (path.startsWith('/blog/')) {
        slug = path.replace('/blog/', '');
        type = slug ? 'article' : 'blog';
    } else if (path === '/blog') {
        type = 'blog';
    } else if (path.startsWith('/page/')) {
        slug = path.replace('/page/', '');
        type = 'page';
    } else if (path === '/about') {
        type = 'about';
    } else if (path === '/how-it-works') {
        type = 'how-it-works';
    } else if (path === '/guide') {
        type = 'guide';
    } else if (path === '/success-stories') {
        type = 'success-stories';
    } else if (path === '/volunteer') {
        type = 'volunteer';
    } else if (path === '/whitelist') {
        type = 'whitelist';
    } else if (path === '/contact') {
        type = 'contact';
    } else if (path === '/faq') {
        type = 'faq';
    } else if (path === '/zakat') {
        type = 'zakat';
    } else if (path === '/login') {
        type = 'login';
    } else if (path === '/signup') {
        type = 'signup';
    } else if (path.includes('forgot-password')) {
        type = 'forgot-password';
    } else if (path.includes('reset-password')) {
        type = 'reset-password';
    } else if (path === '/verify-email') {
        type = 'verify-email';
    } else if (path.startsWith('/student/dashboard') || path === '/student/dashboard') {
        type = 'student-dashboard';
    } else if (path.startsWith('/instructor/dashboard') || path === '/instructor/dashboard') {
        type = 'instructor-dashboard';
    } else if (path.startsWith('/dashboard')) {
        type = 'dashboard';
    } else if (path.startsWith('/org/dashboard')) {
        type = 'org-dashboard';
    }

    return { type, slug };
};
