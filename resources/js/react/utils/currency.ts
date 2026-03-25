import { useSettings } from '../contexts/SettingsContext';
import { useTranslation } from '../contexts/TranslationProvider';

/**
 * Hook to get currency information from settings
 */
export const useCurrency = () => {
    const { settings } = useSettings();
    return {
        code: settings.currency?.code || 'USD',
        symbol: settings.currency?.symbol || '$',
    };
};

/**
 * Format amount with currency symbol
 */
export const formatCurrency = (amount: number, symbol: string, locale: string = 'en'): string => {
    const formatted = new Intl.NumberFormat(locale === 'ar' ? 'ar-EG' : locale === 'ku' ? 'ku' : 'en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
    
    // For RTL languages, put symbol after amount
    if (locale === 'ar' || locale === 'ku') {
        return `${formatted} ${symbol}`;
    }
    
    // For LTR languages, put symbol before amount
    return `${symbol}${formatted}`;
};

