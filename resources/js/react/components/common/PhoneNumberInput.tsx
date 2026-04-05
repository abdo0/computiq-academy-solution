import React, { useEffect, useState, useRef, useMemo } from 'react';
import { usePhoneInput, defaultCountries, parseCountry, FlagImage } from 'react-international-phone';
import { Search, ChevronDown, Check } from 'lucide-react';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import 'react-international-phone/style.css';

export type PhoneFieldValue = {
    phone: string;
    countryCode: string;
};

type PhoneInputVariant = 'auth' | 'compact' | 'dashboard';

const DEFAULT_PHONE_COUNTRY = 'iq';

const classNames = (...parts: Array<string | false | null | undefined>) => parts.filter(Boolean).join(' ');

export const normalizePhoneCountryCode = (countryCode?: string | null): string => {
    const normalized = (countryCode || '').trim().slice(0, 2).toUpperCase();
    return normalized.length === 2 ? normalized : '';
};

export const normalizePhoneForStorage = (phone?: string | null): string => {
    const digits = (phone || '').replace(/\D/g, '');
    return digits ? `+${digits}` : '';
};

export const createPhoneFieldValue = (phone?: string | null, countryCode?: string | null): PhoneFieldValue => ({
    phone: normalizePhoneForStorage(phone),
    countryCode: normalizePhoneCountryCode(countryCode),
});

const getInitialCountry = (countryCode?: string | null): string => {
    const normalized = normalizePhoneCountryCode(countryCode);
    return normalized ? normalized.toLowerCase() : DEFAULT_PHONE_COUNTRY;
};

const buildPhoneFieldValue = (
    rawValue: string,
    countryData?: { countryCode?: string; dialCode?: string }
): PhoneFieldValue => {
    const digits = rawValue.replace(/\D/g, '');
    const dialCode = String(countryData?.dialCode || '').replace(/\D/g, '');
    const hasSubscriberDigits = digits.length > dialCode.length;
    const nextCountryCode = normalizePhoneCountryCode(countryData?.countryCode);

    return {
        phone: hasSubscriberDigits ? `+${digits}` : '',
        countryCode: nextCountryCode,
    };
};

const buildVariantClasses = (variant: PhoneInputVariant) => {
    const variants = {
        auth: {
            container: 'h-12 rounded-[0.75rem]',
            input: 'px-4 text-sm font-medium',
            button: 'w-16 rounded-l-[0.75rem]',
        },
        compact: {
            container: 'h-[42px] rounded-md',
            input: 'px-3 text-sm font-medium',
            button: 'w-14 rounded-l-md',
        },
        dashboard: {
            container: 'h-[54px] rounded-2xl',
            input: 'px-4 text-sm font-medium',
            button: 'w-16 rounded-l-2xl',
        },
    };
    return variants[variant as keyof typeof variants] || variants.auth;
};

interface PhoneNumberInputProps {
    value?: string | null;
    countryCode?: string | null;
    onChange: (value: PhoneFieldValue) => void;
    variant?: PhoneInputVariant;
    invalid?: boolean;
    disabled?: boolean;
    placeholder?: string;
}

const PREFERRED_COUNTRIES = ['iq', 'jo', 'tr', 'ae', 'sa'];

const parsedCountries = defaultCountries.map(c => parseCountry(c));

const allCountriesOrdered = [
    ...PREFERRED_COUNTRIES.map(iso => parsedCountries.find(c => c.iso2 === iso)!).filter(Boolean),
    ...parsedCountries.filter(c => !PREFERRED_COUNTRIES.includes(c.iso2))
];

const PhoneNumberInput: React.FC<PhoneNumberInputProps> = ({
    value = '',
    countryCode = '',
    onChange,
    variant = 'auth',
    invalid = false,
    disabled = false,
    placeholder,
}) => {
    const { __ } = useTranslation();
    const { language } = useLanguage();
    const classes = buildVariantClasses(variant as PhoneInputVariant);
    const displayNameLocales = language === 'ar'
        ? ['ar', 'en']
        : language === 'ku'
            ? ['ckb', 'ku', 'ar', 'en']
            : ['en', 'ar'];
    const regionDisplayNames = useMemo(() => {
        try {
            return new Intl.DisplayNames(displayNameLocales, { type: 'region' });
        } catch {
            return null;
        }
    }, [displayNameLocales]);
    
    // Core hook for masking and formatting phone numbers
    const { inputValue, handlePhoneValueChange, inputRef, country, setCountry } = usePhoneInput({
        defaultCountry: getInitialCountry(countryCode),
        value: value || '',
        onChange: (data) => {
            onChange(buildPhoneFieldValue(data.phone, {
                countryCode: data.country.iso2,
                dialCode: data.country.dialCode,
            }));
        },
    });

    // Sync external countryCode prop changes
    useEffect(() => {
        const nextTarget = getInitialCountry(countryCode);
        if (country.iso2 !== nextTarget) {
            setCountry(nextTarget);
        }
    }, [countryCode]);

    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const wrapperRef = useRef<HTMLDivElement>(null);
    const searchInputRef = useRef<HTMLInputElement>(null);
    const getCountryName = (iso2: string, fallbackName: string) => {
        return regionDisplayNames?.of(iso2.toUpperCase()) || fallbackName;
    };

    // Filter countries dynamically based on the search query
    const filteredCountries = useMemo(() => {
        if (!searchQuery) return allCountriesOrdered;
        const q = searchQuery.toLowerCase();

        return allCountriesOrdered.filter(c => {
            const localizedName = getCountryName(c.iso2, c.name).toLowerCase();
            const fallbackName = c.name.toLowerCase();

            return localizedName.includes(q) ||
                fallbackName.includes(q) ||
            c.dialCode.includes(q)
        });
    }, [searchQuery, regionDisplayNames]);

    // Handle clicks outside the dropdown to close it
    useEffect(() => {
        const handleClickOutside = (e: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Focus the search input when the dropdown opens
    useEffect(() => {
        if (isOpen && searchInputRef.current) {
            searchInputRef.current.focus();
        } else {
            setSearchQuery('');
        }
    }, [isOpen]);

    return (
        <div 
            dir="ltr" 
            ref={wrapperRef}
            className={classNames(
                'relative flex items-stretch w-full transition-shadow border bg-white dark:bg-slate-900',
                classes.container,
                invalid 
                    ? 'border-red-500 focus-within:border-red-500 focus-within:ring-4 focus-within:ring-red-500/20' 
                    : 'border-slate-200 dark:border-slate-700 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/20',
                disabled && 'opacity-70 cursor-not-allowed bg-slate-50 dark:bg-slate-800/50'
            )}
        >
            <button
                type="button"
                disabled={disabled}
                onClick={() => setIsOpen(!isOpen)}
                className={classNames(
                    'flex items-center justify-center shrink-0 border-r border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/80 focus:outline-none transition-colors group',
                    classes.button,
                    isOpen && 'bg-slate-50 dark:bg-slate-800/80',
                    disabled && 'pointer-events-none'
                )}
            >
                <div className="w-[28px] h-[20px] rounded-[3px] overflow-hidden shadow-sm border border-black/5 dark:border-white/10 shrink-0 relative">
                    <FlagImage 
                        iso2={country.iso2} 
                        style={{ width: '100%', height: '100%', objectFit: 'cover', position: 'absolute', top: 0, left: 0 }} 
                    />
                </div>
                <ChevronDown className="w-3.5 h-3.5 ml-1.5 opacity-60 group-hover:opacity-100 transition-opacity text-slate-700 dark:text-slate-300 shrink-0" />
            </button>
            
            <input
                ref={inputRef}
                name="phone"
                autoComplete="tel"
                disabled={disabled}
                placeholder={placeholder}
                value={inputValue}
                onChange={handlePhoneValueChange}
                className={classNames(
                    'flex-1 w-full bg-transparent text-slate-900 dark:text-slate-100 focus:outline-none min-w-0 disabled:pointer-events-none',
                    classes.input
                )}
            />
            
            {isOpen && (
                <div className="absolute z-[100] top-[calc(100%+0.5rem)] left-0 w-full bg-white dark:bg-[#0b172a] border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[320px] ring-1 ring-black/5 dark:ring-white/10 animate-in fade-in zoom-in-95 duration-150">
                    <div className="p-2 border-b border-slate-100 dark:border-slate-800/60 shrink-0 bg-slate-50/50 dark:bg-slate-900/50">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                            <input
                                ref={searchInputRef}
                                type="text"
                                placeholder={__('Search country or dial code...')}
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg outline-none focus:border-brand-500 dark:focus:border-brand-500 transition-colors dark:text-slate-200 shadow-sm"
                            />
                        </div>
                    </div>
                    
                    <div className="overflow-y-auto app-scrollbar-soft p-1.5 flex-1 space-y-0.5">
                        {filteredCountries.map(c => {
                            const isSelected = c.iso2 === country.iso2;
                            const countryName = getCountryName(c.iso2, c.name);
                            return (
                                <button
                                    key={c.iso2}
                                    type="button"
                                    onClick={() => {
                                        setCountry(c.iso2);
                                        setIsOpen(false);
                                        inputRef.current?.focus();
                                    }}
                                    className={classNames(
                                        'w-full flex items-center px-3 py-2 text-sm rounded-lg transition-colors group',
                                        isSelected 
                                            ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-700 dark:text-brand-300 font-semibold' 
                                            : 'hover:bg-slate-100 dark:hover:bg-slate-800/80 text-slate-700 dark:text-slate-300'
                                    )}
                                    >
                                        <div className="w-[20px] h-[14px] rounded-[2px] overflow-hidden shadow-[0_0_0_1px_rgba(0,0,0,0.05)] dark:shadow-[0_0_0_1px_rgba(255,255,255,0.1)] shrink-0 relative">
                                            <FlagImage 
                                                iso2={c.iso2} 
                                                style={{ width: '100%', height: '100%', objectFit: 'cover', position: 'absolute', top: 0, left: 0 }} 
                                            />
                                        </div>
                                    <span className="ml-3 truncate">{countryName}</span>
                                    <span className="ml-auto pl-2 text-slate-400 dark:text-slate-500 font-mono text-xs opacity-80 group-hover:opacity-100 transition-opacity">+{c.dialCode}</span>
                                    {isSelected && <Check className="w-4 h-4 ml-3 opacity-90 shrink-0" />}
                                </button>
                            );
                        })}
                        
                        {filteredCountries.length === 0 && (
                            <div className="py-8 text-center text-sm text-slate-500 flex flex-col items-center">
                                <Search className="w-8 h-8 opacity-20 mb-2" />
                                <span>{__('No countries found for ":query"', { query: searchQuery })}</span>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default PhoneNumberInput;
