import axios from 'axios';
import { useTranslation } from '../contexts/TranslationProvider';

// Use relative URL for same-origin requests (Sanctum SPA mode)
const API_URL = '/api/v1';

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  timeout: 30000, // 30 seconds timeout
  withCredentials: true, // Required for Sanctum session-based auth
});

// CSRF Token handling for Sanctum SPA
let csrfInitialized = false;

export const initializeCsrf = async (): Promise<void> => {
  if (csrfInitialized) return;
  
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    csrfInitialized = true;
  } catch (error) {
    console.error('Failed to initialize CSRF token:', error);
  }
};

// Request interceptor to add CSRF token and locale header
api.interceptors.request.use(
  async (config) => {
    // Initialize CSRF for mutating requests
    if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
      await initializeCsrf();
    }
    
    // Get XSRF token from cookie and add to headers
    const xsrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];
    
    if (xsrfToken) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
    }
    
    // Get locale from document.documentElement.lang (updated by LanguageContext)
    // This reflects the currently selected language in the UI
    const locale = document.documentElement.lang
      || localStorage.getItem('language')
      || document.cookie.split('; ').find(row => row.startsWith('locale='))?.split('=')[1]
      || 'ar';
    
    // Normalize locale to supported languages
    const normalizedLocale = ['ar', 'en', 'ku'].includes(locale) ? locale : 'ar';
    config.headers['Accept-Language'] = normalizedLocale;
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Helper function to update meta tags in document head
const updateMetaTags = (seo: any) => {
  // Update title
  if (seo.title) {
    const titleElement = document.querySelector('title');
    if (titleElement) {
      titleElement.textContent = seo.title;
    }
  }
  
  // Update meta description
  let metaDescription = document.querySelector('meta[name="description"]');
  if (!metaDescription) {
    metaDescription = document.createElement('Meta');
    metaDescription.setAttribute('name', 'description');
    document.head.appendChild(metaDescription);
  }
  if (seo.description) {
    metaDescription.setAttribute('content', seo.description);
  }
  
  // Update meta keywords
  let metaKeywords = document.querySelector('meta[name="keywords"]');
  if (!metaKeywords) {
    metaKeywords = document.createElement('Meta');
    metaKeywords.setAttribute('name', 'keywords');
    document.head.appendChild(metaKeywords);
  }
  if (seo.keywords) {
    metaKeywords.setAttribute('content', seo.keywords);
  }
  
  // Update Open Graph tags
  const updateOgTag = (property: string, content: string) => {
    let ogTag = document.querySelector(`meta[property="${property}"]`);
    if (!ogTag) {
      ogTag = document.createElement('Meta');
      ogTag.setAttribute('property', property);
      document.head.appendChild(ogTag);
    }
    ogTag.setAttribute('content', content);
  };
  
  if (seo.title) {
    updateOgTag('og:title', seo.title);
  }
  if (seo.description) {
    updateOgTag('og:description', seo.description);
  }
  if (seo.ogImage) {
    updateOgTag('og:image', seo.ogImage);
  }
  
  // Update Twitter Card tags
  const updateTwitterTag = (name: string, content: string) => {
    let twitterTag = document.querySelector(`meta[name="twitter:${name}"]`);
    if (!twitterTag) {
      twitterTag = document.createElement('Meta');
      twitterTag.setAttribute('name', `twitter:${name}`);
      document.head.appendChild(twitterTag);
    }
    twitterTag.setAttribute('content', content);
  };
  
  if (seo.title) {
    updateTwitterTag('title', seo.title);
  }
  if (seo.description) {
    updateTwitterTag('description', seo.description);
  }
  if (seo.ogImage) {
    updateTwitterTag('image', seo.ogImage);
  }
};

// Response interceptor for error handling and data updates
api.interceptors.response.use(
  (response) => {
    // Check and update meta (SEO) from response
    if (response.data?.meta?.seo) {
      updateMetaTags(response.data.meta.seo);
    }
    
    // Check and update user from response
    if (response.data?.user !== undefined) {
      if (response.data.user === null) {
        window.dispatchEvent(new CustomEvent('auth:user-update', {
          detail: null
        }));
      } else if (response.data.user) {
        window.dispatchEvent(new CustomEvent('auth:user-update', {
          detail: response.data.user
        }));
      }
    }
    
    return response;
  },
  (error) => {
    // Handle 401 Unauthorized - session expired
    if (error.response?.status === 401) {
      // Clear any stored auth state
      window.dispatchEvent(new CustomEvent('auth:logout'));
    }
    
    // Handle 419 CSRF token mismatch - refresh token
    if (error.response?.status === 419) {
      csrfInitialized = false;
      // Could retry the request here after refreshing CSRF
    }
    
    // Handle 422 Validation errors
    if (error.response?.status === 422) {
      // Validation errors are passed through for form handling
    }
    
    // Even in error responses, check for meta updates
    if (error.response?.data) {
      // Check and update meta (SEO) from error response
      if (error.response.data?.meta?.seo) {
        updateMetaTags(error.response.data.meta.seo);
      }
      
      // Check and update user from error response
      if (error.response.data?.user !== undefined) {
        window.dispatchEvent(new CustomEvent('auth:user-update', {
          detail: error.response.data.user
        }));
      }
    }
    
    return Promise.reject(error);
  }
);
