import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import axios from 'axios';

// Declare the global variables injected by the Blade template
declare global {
  interface Window {
    __translations?: Record<string, string>;
    __locale?: 'ar' | 'en' | 'ku';
  }
}

/** Pre-fetched translation data that can be applied later */
export interface PrefetchedTranslations {
  translations: Record<string, string>;
  hash: string;
  locale: 'ar' | 'en' | 'ku';
}

interface TranslationContextType {
  translations: Record<string, string>;
  locale: 'ar' | 'en' | 'ku';
  isLoading: boolean;
  refreshTranslations: () => Promise<void>;
  changeLocale: (newLocale: 'ar' | 'en' | 'ku') => Promise<void>;
  /** Fetch translations without applying — returns data to apply later */
  prefetchLocale: (targetLocale: 'ar' | 'en' | 'ku') => Promise<PrefetchedTranslations>;
  /** Apply pre-fetched translations atomically (state + globals) */
  applyFetchedLocale: (data: PrefetchedTranslations) => void;
  t: (val: any) => string;
}

const TranslationContext = createContext<TranslationContextType | undefined>(undefined);

export const TranslationProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  // Use SSR injected values or fallback to Arabic
  const initialLocale = window.__locale || (document.documentElement.lang as 'ar' | 'en' | 'ku') || 'ar';
  
  const [locale, setLocale] = useState<'ar' | 'en' | 'ku'>(initialLocale);
  const [translations, setTranslations] = useState<Record<string, string>>(window.__translations || {});
  const [isLoading, setIsLoading] = useState<boolean>(!window.__translations);
  const [hash, setHash] = useState<string | null>(null);

  const fetchTranslationsForLocale = async (targetLocale: 'ar' | 'en' | 'ku') => {
    setIsLoading(true);
    try {
      const response = await axios.get(`/api/v1/translations?locale=${targetLocale}`);
      const data = response.data;
      
      if (data.hash !== hash || locale !== targetLocale) {
        setTranslations(data.translations);
        setHash(data.hash);
        setLocale(targetLocale);
        window.__translations = data.translations;
        window.__locale = targetLocale;
      }
    } catch (error) {
      console.error('Failed to fetch translations:', error);
    } finally {
      setIsLoading(false);
    }
  };

  /**
   * Fetch translations for a locale WITHOUT updating any state.
   * Returns the data so the caller can apply it later (e.g. after preloading).
   */
  const prefetchLocale = async (targetLocale: 'ar' | 'en' | 'ku'): Promise<PrefetchedTranslations> => {
    const response = await axios.get(`/api/v1/translations?locale=${targetLocale}`);
    return {
      translations: response.data.translations,
      hash: response.data.hash,
      locale: targetLocale,
    };
  };

  /**
   * Apply pre-fetched translations atomically. 
   * Updates React state + global window variables in one go.
   */
  const applyFetchedLocale = (data: PrefetchedTranslations) => {
    setTranslations(data.translations);
    setHash(data.hash);
    setLocale(data.locale);
    window.__translations = data.translations;
    window.__locale = data.locale;
  };

  const refreshTranslations = async () => {
    await fetchTranslationsForLocale(locale);
  };

  const changeLocale = async (newLocale: 'ar' | 'en' | 'ku') => {
    await fetchTranslationsForLocale(newLocale);
  };

  // If no SSR translations were provided, fetch them on mount
  useEffect(() => {
    if (Object.keys(translations).length === 0) {
      refreshTranslations();
    }
  }, []);

  const t = (val: any) => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    return val[locale] || val['ar'] || val['en'] || '';
  };

  return (
    <TranslationContext.Provider value={{ translations, locale, isLoading, refreshTranslations, changeLocale, prefetchLocale, applyFetchedLocale, t }}>
      {children}
    </TranslationContext.Provider>
  );
};

export const useTranslation = () => {
  const context = useContext(TranslationContext);

  if (!context) {
    // Graceful fallback if used outside provider (e.g. tests)
    const fallbackLocale = window.__locale || (document.documentElement.lang as 'ar' | 'en' | 'ku') || 'ar';
    const fallbackTranslations = window.__translations || {};
    
    return {
      __: (key: string, params: Record<string, string | number> = {}) => {
        let text = fallbackTranslations[key] || key;
        
        // Parameter interpolation
        if (Object.keys(params).length > 0) {
          Object.entries(params).forEach(([param, value]) => {
            text = text.replace(new RegExp(`:${param}`, 'g'), String(value));
          });
        }
        
        return text;
      },
      t: (val: any) => {
        if (!val) return '';
        if (typeof val === 'string') return val;
        return val[fallbackLocale] || val['ar'] || val['en'] || '';
      },
      locale: fallbackLocale,
      isLoading: false,
      refreshTranslations: async () => {},
      changeLocale: async (_l: any) => {},
      prefetchLocale: async (_l: any) => ({ translations: {}, hash: '', locale: 'ar' as const }),
      applyFetchedLocale: (_d: any) => {},
    };
  }

  const { translations, locale, isLoading, refreshTranslations, changeLocale, prefetchLocale, applyFetchedLocale } = context;

  /**
   * Translate a key with parameter interpolation
   * Example: __('pagination.showing', { from: 1, to: 10 })
   */
  const __ = (key: string, params: Record<string, string | number> = {}) => {
    let text = translations[key] || key;

    // Parameter interpolation
    if (Object.keys(params).length > 0) {
      Object.entries(params).forEach(([param, value]) => {
        text = text.replace(new RegExp(`:${param}`, 'g'), String(value));
      });
    }

    return text;
  };

  // Safe helper for API translation objects { ar: '', en: '' }
  const t = (val: any) => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    return val[locale] || val['ar'] || val['en'] || '';
  };

  return { __, t, locale, isLoading, refreshTranslations, changeLocale, prefetchLocale, applyFetchedLocale };
};

export default TranslationProvider;
