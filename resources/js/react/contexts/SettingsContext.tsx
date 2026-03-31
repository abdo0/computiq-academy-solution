import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { AppSettings, HeroContent } from '../types';
import { dataService } from '../services/dataService';

interface SettingsContextType {
    settings: AppSettings;
    heroContent: HeroContent;
    isLoading: boolean;
}

const SettingsContext = createContext<SettingsContextType | undefined>(undefined);

export const SettingsProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [settings, setSettings] = useState<AppSettings>({} as AppSettings);
    const [heroContent, setHeroContent] = useState<HeroContent>({} as HeroContent);
    const [isLoading, setIsLoading] = useState(true);

    // Load settings ONCE on mount.
    // heroContent, footerPages, and otherPages are now bundled inside the settings response
    // so no separate API calls are needed for those.
    useEffect(() => {
        const loadSettings = async () => {
            // Try server-rendered initial data first
            const rootElement = document.getElementById('root');
            if (rootElement) {
                const initialDataAttr = rootElement.getAttribute('data-initial');
                if (initialDataAttr && initialDataAttr.trim() !== '') {
                    try {
                        const initialData = JSON.parse(initialDataAttr);
                        if (initialData.settings) {
                            setSettings(initialData.settings);
                            if (initialData.settings.heroContent) {
                                setHeroContent(initialData.settings.heroContent);
                            }
                            setIsLoading(false);
                            return;
                        }
                    } catch (error) {
                        console.error('Failed to parse initial settings data:', error);
                    }
                }
            }

            // Fallback to API — heroContent, footerPages, otherPages are all in this one call
            try {
                const fetchedSettings = await dataService.getSettings();
                setSettings(fetchedSettings);
                if ((fetchedSettings as any).heroContent) {
                    setHeroContent((fetchedSettings as any).heroContent);
                }
            } catch (error) {
                console.error('Failed to load global settings', error);
            } finally {
                setIsLoading(false);
            }
        };
        loadSettings();
    }, []);

    return (
        <SettingsContext.Provider value={{ settings, heroContent, isLoading }}>
            {children}
        </SettingsContext.Provider>
    );
};

export const useSettings = () => {
    const context = useContext(SettingsContext);
    if (!context) {
        throw new Error('useSettings must be used within a SettingsProvider');
    }
    return context;
};
