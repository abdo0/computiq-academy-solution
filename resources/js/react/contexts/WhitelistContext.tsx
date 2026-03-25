import React, { createContext, useContext, useState, ReactNode } from 'react';
import { userAuthService } from '../services/dataService';
import { useTranslation } from '../contexts/TranslationProvider';

interface WhitelistContextType {
    whitelist: string[];
    isLoading: boolean;
    toggleWhitelist: (identifier: string) => Promise<void>;
    isInWhitelist: (identifier: string) => boolean;
}

const WhitelistContext = createContext<WhitelistContextType | undefined>(undefined);

export const WhitelistProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [whitelist, setWhitelist] = useState<string[]>([]);
    const [isLoading, setIsLoading] = useState(false);

    const toggleWhitelist = async (identifier: string) => {
        setIsLoading(true);
        try {
            const result = await userAuthService.toggleWhitelist(identifier);
            if (result.success) {
                setWhitelist(prev => 
                    prev.includes(identifier) 
                        ? prev.filter(id => id !== identifier)
                        : [...prev, identifier]
                );
            }
        } catch (error) {
            console.error("Failed to toggle whitelist:", error);
        } finally {
            setIsLoading(false);
        }
    };

    const isInWhitelist = (identifier: string) => {
        return whitelist.includes(identifier);
    };

    return (
        <WhitelistContext.Provider value={{ whitelist, isLoading, toggleWhitelist, isInWhitelist }}>
            {children}
        </WhitelistContext.Provider>
    );
};

export const useWhitelist = () => {
    const context = useContext(WhitelistContext);
    if (context === undefined) {
        throw new Error('useWhitelist must be used within a WhitelistProvider');
    }
    return context;
};
