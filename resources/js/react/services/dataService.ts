import { api, initializeCsrf } from './api';
import {
    AppSettings,
    HeroContent,
    Stat,
    BlogPost,
    FaqItem,
    User,
    ApiResponse,
    SeoConfig,
    Category,
    Testimonial
} from '../types';

// Helper to delay response for simulation if needed when using mock data
const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

// Short-lived cache for preloading route data seamlessly without double fetches
const cache = new Map<string, { promise: Promise<any>, time: number }>();
const CACHE_TTL = 3000; // 3 seconds is enough to span a route transition

const fetchWithCache = <T>(key: string, fetchFn: () => Promise<T>): Promise<T> => {
    const cached = cache.get(key);
    if (cached && Date.now() - cached.time < CACHE_TTL) {
        return cached.promise;
    }
    const promise = fetchFn();
    cache.set(key, { promise, time: Date.now() });
    
    // Clean up rejected promises so they aren't cached
    promise.catch(() => cache.delete(key));
    return promise;
};

export const dataService = {
    getSettings: async (): Promise<AppSettings> => {
        try {
            const response = await api.get<{ data: AppSettings }>('/settings');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for settings, using default.', error);
            await delay(500);
            return {} as AppSettings;
        }
    },

    getHeroContent: async (): Promise<HeroContent> => {
        try {
            const response = await api.get<{ data: HeroContent }>('/content/hero');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for hero content, using default.', error);
            return {} as HeroContent;
        }
    },

    getHomeData: async (): Promise<{ stats: Stat[], sections: Record<string, any>, testimonials: any[], sponsors: { partners: any[], employment: any[] }, courses: any[], course_categories: any[] }> => {
        return fetchWithCache('/home', async () => {
            try {
                const response = await api.get<{ data: { stats: Stat[], sections: Record<string, any>, testimonials: any[], sponsors?: { partners: any[], employment: any[] }, courses?: any[], course_categories?: any[] } }>('/home');
                const data = response.data.data;

                return {
                    stats: data.stats || [],
                    sections: data.sections || {},
                    testimonials: data.testimonials || [],
                    sponsors: data.sponsors || { partners: [], employment: [] },
                    courses: data.courses || [],
                    course_categories: data.course_categories || []
                };
            } catch (error) {
                console.error('API fetch failed for home data.', error);
                return { stats: [], sections: {}, testimonials: [], sponsors: { partners: [], employment: [] }, courses: [], course_categories: [] };
            }
        });
    },

    getCourses: async (params?: { category?: string; search?: string; sort?: string; page?: number; per_page?: number }): Promise<any> => {
        const key = `/courses?${new URLSearchParams(params as any || {}).toString()}`;
        return fetchWithCache(key, async () => {
            try {
                const response = await api.get<any>('/courses', { params });
                return response.data.data;
            } catch (error) {
                console.error('API fetch failed for courses list.', error);
                return { data: [], meta: { current_page: 1, last_page: 1, total: 0 } };
            }
        });
    },

    getCourseBySlug: async (slug: string): Promise<any> => {
        return fetchWithCache(`/courses/${slug}`, async () => {
            try {
                const response = await api.get<{ data: any }>(`/courses/${slug}`);
                return response.data.data;
            } catch (error) {
                console.error('API fetch failed for course details.', error);
                return null;
            }
        });
    },

    getInstructorBySlug: async (slug: string): Promise<any> => {
        return fetchWithCache(`/instructors/${slug}`, async () => {
            try {
                const response = await api.get<{ data: any }>(`/instructors/${slug}`);
                return response.data.data;
            } catch (error) {
                console.error('API fetch failed for instructor profile.', error);
                return null;
            }
        });
    },

    getCategories: async (): Promise<Category[]> => {
        try {
            const response = await api.get<{ data: Category[] }>('/categories');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for categories.', error);
            return [];
        }
    },

    searchGlobal: async (q: string, page: number = 1): Promise<any> => {
        const key = `/search?q=${encodeURIComponent(q)}&page=${page}`;
        return fetchWithCache(key, async () => {
            try {
                const response = await api.get<any>('/search', { params: { q, page } });
                return response.data.data;
            } catch (error) {
                console.error('API fetch failed for search.', error);
                return { courses: { data: [], meta: { total: 0 } }, instructors: [], query: q };
            }
        });
    },

    getCountries: async (): Promise<any[]> => {
        try {
            const response = await api.get<{ data: any[] }>('/countries');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for countries.', error);
            return [];
        }
    },

    getStates: async (countryId: number): Promise<any[]> => {
        try {
            const response = await api.get<{ data: any[] }>(`/countries/${countryId}/states`);
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for states.', error);
            return [];
        }
    },

    getStats: async (): Promise<Stat[]> => {
        try {
            const response = await api.get<{ data: Stat[] }>('/stats');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for stats.', error);
            return [];
        }
    },

    getBlogPosts: async (params?: { category?: string; search?: string; per_page?: number }): Promise<BlogPost[]> => {
        const key = `/articles?${new URLSearchParams(params as any || {}).toString()}`;
        return fetchWithCache(key, async () => {
            try {
                const response = await api.get<any>('/articles', { params });
                // Handle Laravel Resource Collection response (paginated or not)
                // Response structure: { data: [...], links: {...}, meta: {...} }
                // Or if wrapped: { success: true, data: { data: [...], ... } }
                const articles = Array.isArray(response.data.data)
                    ? response.data.data
                    : (response.data.data?.data || []);
                // Map to include legacy fields for backward compatibility
                return articles.map((article: BlogPost) => ({
                    ...article,
                    imageUrl: article.featuredImageUrl || article.imageUrl || 'https://picsum.photos/seed/article/1200/630',
                    contentImageUrl: article.contentImageUrl || 'https://picsum.photos/seed/article-content/800/600',
                    date: article.publishedAt || article.createdAt || article.date,
                    author: article.authorName || article.author,
                }));
            } catch (error) {
                console.warn('API fetch failed for blog posts.', error);
                return [];
            }
        });
    },

    getBlogPost: async (id: string): Promise<BlogPost | undefined> => {
        try {
            const response = await api.get<any>(`/articles/${id}`);
            // API returns: { success: true, data: { article: {...} } }
            const article = response.data.data?.article || response.data.data;
            if (!article) return undefined;
            // Map to include legacy fields for backward compatibility
            return {
                ...article,
                imageUrl: article.featuredImageUrl || article.imageUrl || 'https://picsum.photos/seed/article/1200/630',
                contentImageUrl: article.contentImageUrl || 'https://picsum.photos/seed/article-content/800/600',
                date: article.publishedAt || article.createdAt || article.date,
                author: article.authorName || article.author,
            };
        } catch (error) {
            console.warn(`API fetch failed for post ${id}.`, error);
            return undefined;
        }
    },



    getBlogPostBySlug: async (slug: string): Promise<{ data: BlogPost, meta?: { seo?: SeoConfig } } | undefined> => {
        try {
            const response = await api.get<any>(`/articles/${slug}`);
            // Extract article from response.data.data.article (based on API structure)
            // API returns: { success: true, data: { article: {...} }, meta: { seo: {...} } }
            const article = response.data.data?.article || response.data.data;
            const meta = response.data.meta;
            // Map to include legacy fields for backward compatibility
            const mappedArticle = {
                ...article,
                imageUrl: article.featuredImageUrl || article.imageUrl || 'https://picsum.photos/seed/article/1200/630',
                contentImageUrl: article.contentImageUrl || 'https://picsum.photos/seed/article-content/800/600',
                date: article.publishedAt || article.createdAt || article.date,
                author: article.authorName || article.author,
            };
            return { data: mappedArticle, meta };
        } catch (error) {
            console.warn(`API fetch failed for post ${slug}.`, error);
            return undefined;
        }
    },

    getFaqs: async (): Promise<FaqItem[]> => {
        return fetchWithCache('/faqs', async () => {
            try {
                const response = await api.get<{ data: FaqItem[] }>('/faqs');
                return response.data.data;
            } catch (error) {
                console.warn('API fetch failed for FAQs.', error);
                return [];
            }
        });
    },

    getTestimonials: async (): Promise<Testimonial[]> => {
        try {
            const response = await api.get<{ data: Testimonial[] }>('/content/testimonials');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for testimonials.', error);
            return [];
        }
    },


    getHomeSections: async (): Promise<Record<string, any>> => {
        try {
            const response = await api.get<{ data: Record<string, any> }>('/content/home-sections');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for home sections.', error);
            return {};
        }
    },

    getFooterPages: async (): Promise<string[]> => {
        try {
            const response = await api.get<{ data: { slug: string }[] }>('/content/footer-pages');
            return (response.data.data || []).map(page => page.slug);
        } catch (error) {
            console.warn('API fetch failed for footer pages.', error);
            return [];
        }
    },

    getOtherPages: async (): Promise<Array<{ slug: string; title: Record<string, string> | string }>> => {
        try {
            const response = await api.get<{ data: Array<{ slug: string; title: Record<string, string> | string }> }>('/content/other-pages');
            return response.data.data || [];
        } catch (error) {
            console.warn('API fetch failed for other pages.', error);
            return [];
        }
    },

    getDynamicPage: async (slug: string): Promise<any> => {
        return fetchWithCache(`/page/${slug}`, async () => {
            try {
                const response = await api.get<{ data: any }>(`/content/page/${slug}`);
                return response.data.data;
            } catch (error) {
                console.error(`API fetch failed for page ${slug}.`, error);
                return null;
            }
        });
    },

    // Page SEO endpoints
    fetchSeo: async (type: string, slug?: string): Promise<any> => {
        const key = `/seo?type=${type}${slug ? '&slug=' + slug : ''}`;
        return fetchWithCache(key, async () => {
            try {
                const params: any = { type };
                if (slug) params.slug = slug;
                const response = await api.get('/seo', { params });
                return response.data?.data || null;
            } catch (error) {
                console.warn(`Failed to load SEO for type: ${type}`, error);
                return null;
            }
        });
    },

    loadPageSeo: async (page: 'home' | 'about' | 'contact' | 'volunteer' | 'faq' | 'zakat'): Promise<void> => {
        try {
            await api.get(`/pages/${page}`);
        } catch (error) {
            console.warn(`Failed to load SEO for ${page} page`, error);
        }
    },

    getPage: async (slug: string): Promise<{ slug: string; title: Record<string, string>; content: Record<string, string>; meta_title?: string | null; meta_description?: string | null } | null> => {
        try {
            const response = await api.get<{ data: any }>(`/pages/${slug}`);
            return response.data.data;
        } catch (error) {
            console.warn(`API fetch failed for page ${slug}.`, error);
            return null;
        }
    },

    getPaymentGateways: async (): Promise<any[]> => {
        try {
            // Locale is now sent automatically via Accept-Language header in api.ts
            const response = await api.get<{ data: any[] }>('/payment-gateways');
            return response.data.data;
        } catch (error) {
            console.warn('API fetch failed for payment gateways.', error);
            return [];
        }
    },

    // Forms Submissions
    submitContactForm: async (data: any): Promise<boolean> => {
        try {
            await api.post('/contact', data);
            return true;
        } catch (error: any) {
            console.error('API call failed for contact form.', error);
            throw error;
        }
    },

    submitVolunteerForm: async (data: any): Promise<boolean> => {
        try {
            await api.post('/volunteer', data);
            return true;
        } catch (error) {
            console.warn('API call failed for volunteer form, simulating success.', error);
            await delay(1500);
            return true;
        }
    },

    subscribeNewsletter: async (email: string): Promise<{ success: boolean; message?: string; error?: string }> => {
        try {
            const response = await api.post('/subscribe', { email });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Subscription failed',
            };
        }
    },

    // ── Learning Paths ── //

    getPaths: async (params?: any): Promise<any> => {
        try {
            const response = await api.get('/paths', { params });
            return response.data.data;
        } catch (error) {
            console.error('Error fetching paths:', error);
            throw error;
        }
    },

    getPathBySlug: async (slug: string): Promise<any> => {
        try {
            const response = await api.get(`/paths/${slug}`);
            return response.data.data;
        } catch (error: any) {
            if (error.response?.status === 404) {
                return null;
            }
            console.error(`Error fetching path with slug ${slug}:`, error);
            throw error;
        }
    },
};

// Auth Service for User
export const userAuthService = {
    login: async (email: string, password: string, remember: boolean = false, turnstileToken?: string): Promise<{ success: boolean; user?: any; error?: string; requires2FA?: boolean }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/user/login', { email, password, remember, 'cf-turnstile-response': turnstileToken });

            // Our API wraps payload in `data`, with user under `data.user`
            const user = response.data?.data?.user || response.data?.user;

            return { success: true, user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Login failed',
            };
        }
    },

    register: async (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string; locale?: string; 'cf-turnstile-response'?: string }): Promise<{ success: boolean; user?: any; error?: string; errors?: any }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/user/register', data);

            // Registration response also uses success macro: user is under data.user
            const user = response.data?.data?.user || response.data?.user;

            return { success: true, user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Registration failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    logout: async (): Promise<void> => {
        try {
            await api.post('/user/logout');
        } catch (error) {
            console.warn('Logout failed', error);
        }
    },

    getUser: async (): Promise<any | null> => {
        try {
            const response = await api.get('/user/profile');
            return response.data.user;
        } catch (error) {
            return null;
        }
    },

    forgotPassword: async (email: string, turnstileToken?: string): Promise<{ success: boolean; otpCode?: string; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/user/forgot-password', { email, 'cf-turnstile-response': turnstileToken });
            return {
                success: true,
                otpCode: response.data?.data?.otp_code,
            };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to send password reset code',
            };
        }
    },

    resetPassword: async (email: string, otpCode: string, password: string, passwordConfirmation: string, turnstileToken?: string): Promise<{ success: boolean; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/user/reset-password', {
                email,
                otp_code: otpCode,
                password,
                password_confirmation: passwordConfirmation,
                'cf-turnstile-response': turnstileToken,
            });
            return { success: true };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to reset password',
            };
        }
    },

    getDashboardStats: async (): Promise<any> => {
        const response = await api.get('/user/dashboard/stats');
        return response.data.data;
    },

    getDonationHistory: async (params?: { page?: number; status?: string }): Promise<any> => {
        const response = await api.get('/user/dashboard/history', { params });
        return response.data;
    },

    requestRefund: async (transactionId: number, reason: string): Promise<{ success: boolean; message?: string; error?: string }> => {
        try {
            const response = await api.post(`/user/dashboard/transactions/${transactionId}/refund`, { reason });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Refund request failed',
            };
        }
    },

    verifyPayment: async (transactionId: number): Promise<{ success: boolean; transaction?: any; status?: string; message?: string; error?: string }> => {
        try {
            const response = await api.post(`/user/dashboard/transactions/${transactionId}/verify`);
            return {
                success: true,
                transaction: response.data?.data?.transaction,
                status: response.data?.data?.status,
                message: response.data?.message,
            };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Payment verification failed',
            };
        }
    },

    getVerificationStatus: async (): Promise<any> => {
        const response = await api.get('/user/verification/status');
        return response.data.data;
    },

    updateLocale: async (locale: string): Promise<{ success: boolean; user?: any; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/user/locale', { locale });
            return { success: true, user: response.data.data?.user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to update language',
            };
        }
    },

    submitVerification: async (formData: FormData): Promise<{ success: boolean; message?: string; error?: string }> => {
        try {
            const response = await api.post('/user/verification/submit', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Verification submission failed',
            };
        }
    },

    updateProfile: async (formData: FormData): Promise<{ success: boolean; data?: any; message?: string; error?: string; errors?: any }> => {
        try {
            const response = await api.post('/user/profile', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            return { success: true, data: response.data.data, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Profile update failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    updateEmail: async (newEmail: string, currentPassword: string): Promise<{ success: boolean; message?: string; error?: string; errors?: any; otpCode?: string }> => {
        try {
            const response = await api.post('/user/profile/email', {
                new_email: newEmail,
                current_password: currentPassword,
            });
            return {
                success: true,
                message: response.data.message,
                otpCode: response.data.data?.otp_code,
            };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Email update failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    verifyEmailOTP: async (otpCode: string): Promise<{ success: boolean; message?: string; error?: string; errors?: any }> => {
        try {
            const response = await api.post('/user/profile/email/verify-otp', {
                otp_code: otpCode,
            });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'OTP verification failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    updatePassword: async (currentPassword: string, newPassword: string): Promise<{ success: boolean; message?: string; error?: string; errors?: any }> => {
        try {
            const response = await api.post('/user/profile/password', {
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: newPassword,
            });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Password update failed',
                errors: error.response?.data?.errors,
            };
        }
    },



    // ── Cart ──

    getCart: async (): Promise<any> => {
        const response = await api.get('/user/cart');
        return response.data.data;
    },

    addToCart: async (courseId: number): Promise<{ success: boolean; count?: number; already_exists?: boolean; message?: string; error?: string }> => {
        try {
            const response = await api.post('/user/cart', { course_id: courseId });
            return { success: true, count: response.data.data?.count, message: response.data.message };
        } catch (error: any) {
            if (error.response?.status === 409) {
                return { success: false, already_exists: true, error: error.response?.data?.message };
            }
            return { success: false, error: error.response?.data?.message || 'Failed to add to cart' };
        }
    },

    removeFromCart: async (courseId: number): Promise<{ success: boolean; count?: number; message?: string; error?: string }> => {
        try {
            const response = await api.delete(`/user/cart/${courseId}`);
            return { success: true, count: response.data.data?.count, message: response.data.message };
        } catch (error: any) {
            return { success: false, error: error.response?.data?.message || 'Failed to remove from cart' };
        }
    },

    clearCart: async (): Promise<{ success: boolean; message?: string; error?: string }> => {
        try {
            const response = await api.delete('/user/cart');
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return { success: false, error: error.response?.data?.message || 'Failed to clear cart' };
        }
    },
};

// Auth Service for Organization
export const orgAuthService = {
    login: async (email: string, password: string, remember: boolean = false): Promise<{ success: boolean; user?: any; requiresVerification?: boolean; verificationCode?: string; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/login', { email, password, remember });

            // Check if 2FA is required
            if (response.data.data?.requires_verification) {
                return {
                    success: true,
                    requiresVerification: true,
                    verificationCode: response.data.data.verification_code
                };
            }

            return { success: true, user: response.data.data?.user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Login failed',
            };
        }
    },

    register: async (data: { name: string; email: string; password: string; password_confirmation: string; phone?: string; address?: string; locale?: string }): Promise<{ success: boolean; user?: any; requiresVerification?: boolean; verificationCode?: string; error?: string; errors?: any }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/register', data);

            // Check if 2FA is required
            if (response.data.data?.requires_verification) {
                return {
                    success: true,
                    requiresVerification: true,
                    verificationCode: response.data.data.verification_code
                };
            }

            return { success: true, user: response.data.data?.user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Registration failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    verify2FA: async (code: string, remember: boolean = false): Promise<{ success: boolean; user?: any; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/verify-2fa', { code, remember });
            return { success: true, user: response.data.data?.user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Verification failed',
            };
        }
    },

    resend2FA: async (): Promise<{ success: boolean; verificationCode?: string; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/resend-2fa');

            return {
                success: true,
                verificationCode: response.data.data?.verification_code
            };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to resend code',
            };
        }
    },

    logout: async (): Promise<void> => {
        try {
            await api.post('/org/logout');
        } catch (error) {
            console.warn('Logout failed', error);
        }
    },

    getUser: async (): Promise<any | null> => {
        try {
            const response = await api.get('/org/user');
            return response.data.user;
        } catch (error) {
            return null;
        }
    },

    getDashboardStats: async (): Promise<any> => {
        const response = await api.get('/org/dashboard/stats');
        return response.data.data;
    },



    getVerificationStatus: async (): Promise<any> => {
        const response = await api.get('/org/verification/status');
        return response.data.data;
    },

    submitVerification: async (formData: FormData): Promise<{ success: boolean; message?: string; error?: string }> => {
        try {
            const response = await api.post('/org/verification/submit', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Verification submission failed',
            };
        }
    },

    updateProfile: async (formData: FormData): Promise<{ success: boolean; data?: any; message?: string; error?: string; errors?: any }> => {
        try {
            // Use POST directly since API routes don't support _method
            const response = await api.post('/org/profile', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            return { success: true, data: response.data.data, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Profile update failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    updateEmail: async (newEmail: string, currentPassword: string): Promise<{ success: boolean; message?: string; error?: string; errors?: any; otpCode?: string }> => {
        try {
            // Use POST directly since API routes don't support _method
            const response = await api.post('/org/profile/email', {
                new_email: newEmail,
                current_password: currentPassword,
            });
            return {
                success: true,
                message: response.data.message,
                otpCode: response.data.data?.otp_code,
            };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Email update failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    verifyEmailOTP: async (otpCode: string): Promise<{ success: boolean; message?: string; error?: string; errors?: any }> => {
        try {
            const response = await api.post('/org/profile/email/verify-otp', {
                otp_code: otpCode,
            });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'OTP verification failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    forgotPassword: async (email: string): Promise<{ success: boolean; otpCode?: string; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/forgot-password', { email });
            return {
                success: true,
                otpCode: response.data?.data?.otp_code,
            };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to send password reset code',
            };
        }
    },

    resetPassword: async (email: string, otpCode: string, password: string, passwordConfirmation: string): Promise<{ success: boolean; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/reset-password', {
                email,
                otp_code: otpCode,
                password,
                password_confirmation: passwordConfirmation,
            });
            return { success: true };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to reset password',
            };
        }
    },

    updatePassword: async (currentPassword: string, newPassword: string): Promise<{ success: boolean; message?: string; error?: string; errors?: any }> => {
        try {
            // Use POST directly since API routes don't support _method
            const response = await api.post('/org/profile/password', {
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: newPassword,
            });
            return { success: true, message: response.data.message };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Password update failed',
                errors: error.response?.data?.errors,
            };
        }
    },

    updateLocale: async (locale: string): Promise<{ success: boolean; user?: any; error?: string }> => {
        try {
            await initializeCsrf();
            const response = await api.post('/org/locale', { locale });
            return { success: true, user: response.data.data?.user };
        } catch (error: any) {
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to update language',
            };
        }
    },
};
