import { useSettings } from '../contexts/SettingsContext';
import { useLanguage } from '../contexts/LanguageContext';

export interface CurrencyConfig {
    code: string;
    symbol: string;
    name?: string;
}

const FALLBACK_CURRENCY: CurrencyConfig = {
    code: 'IQD',
    symbol: 'د.ع',
    name: 'Iraqi Dinar',
};

export const normalizeCurrency = (currency?: Partial<CurrencyConfig> | null): CurrencyConfig => ({
    code: currency?.code || FALLBACK_CURRENCY.code,
    symbol: currency?.symbol || FALLBACK_CURRENCY.symbol,
    name: currency?.name || FALLBACK_CURRENCY.name,
});

const normalizeAmount = (amount: number | string | null | undefined): number => {
    if (amount === null || amount === undefined || amount === '') {
        return 0;
    }

    if (typeof amount === 'number') {
        return Number.isFinite(amount) ? amount : 0;
    }

    const parsed = Number(String(amount).replace(/,/g, '').trim());

    return Number.isFinite(parsed) ? parsed : 0;
};

export const formatCurrencyAmount = (
    amount: number | string | null | undefined,
    currency?: Partial<CurrencyConfig> | null,
    locale: string = 'en'
): string => {
    const resolvedCurrency = normalizeCurrency(currency);
    const formattedAmount = new Intl.NumberFormat(locale === 'ar' ? 'ar-EG' : locale === 'ku' ? 'ckb-IQ' : 'en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(normalizeAmount(amount));

    return `${formattedAmount} ${resolvedCurrency.symbol}`;
};

export const useCurrency = () => {
    const { settings } = useSettings();
    const { language } = useLanguage();
    const currency = normalizeCurrency(settings.currency);

    return {
        ...currency,
        currency,
        formatAmount: (amount: number | string | null | undefined, overrideCurrency?: Partial<CurrencyConfig> | null) =>
            formatCurrencyAmount(amount, overrideCurrency ?? currency, language),
    };
};

