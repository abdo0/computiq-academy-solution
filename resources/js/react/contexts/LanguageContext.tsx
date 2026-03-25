import React, { createContext, useContext, useState, ReactNode, useMemo, useEffect } from 'react';
import { useTranslation } from '../contexts/TranslationProvider';

export type Language = 'ar' | 'en' | 'ku';

interface LanguageContextType {
  language: Language;
  setLanguage: (lang: Language) => void;
  dir: 'rtl' | 'ltr';
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export const LanguageProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  // Use SSR injected value, or HTML lang attribute, or fallback to Arabic
  const initialLang = (window.__locale || document.documentElement.lang || 'ar') as Language;
  
  const [language, setLanguageState] = useState<Language>(initialLang);

  // Set language — updates state & DOM. Caller handles navigation/redirect.
  const setLanguage = (lang: Language) => {
    setLanguageState(lang);
    localStorage.setItem('language', lang);
    
    // Update local DOM immediately for better UX
    document.documentElement.lang = lang;
    document.documentElement.dir = (lang === 'ar' || lang === 'ku') ? 'rtl' : 'ltr';
  };

  const dir = useMemo(() => {
    return (language === 'ar' || language === 'ku') ? 'rtl' : 'ltr';
  }, [language]);

  return (
    <LanguageContext.Provider value={{ language, setLanguage, dir }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => {
  const context = useContext(LanguageContext);

  // Safe fallback: allow usage outside provider with sensible defaults
  if (!context) {
    const domLang = typeof document !== 'undefined' ? document.documentElement.lang : 'ar';
    const fallbackLanguage: Language = 
      ['ar', 'en', 'ku'].includes(domLang) ? (domLang as Language) : 'ar';

    const dir = fallbackLanguage === 'ar' || fallbackLanguage === 'ku' ? 'rtl' : 'ltr';

    return {
      language: fallbackLanguage,
      setLanguage: () => { window.location.href = `/lang/${fallbackLanguage}`; },
      dir,
    };
  }

  return context;
};