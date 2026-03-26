import React, { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import { userAuthService } from '../services/dataService';
import { useAuth } from './AuthContext';
import { toast } from 'react-toastify';
import { useTranslation } from './TranslationProvider';

interface CartItemData {
    id: number;
    course_id: number;
    price: string;
    added_at: string;
    course: {
        id: number;
        title: any;
        slug: string;
        image: string;
        price: string;
        old_price: string | null;
        instructor_name: string;
        duration_hours: number;
    } | null;
}

interface CartContextType {
    cartItems: CartItemData[];
    cartCount: number;
    cartTotal: string;
    isLoading: boolean;
    addToCart: (courseId: number) => Promise<boolean>;
    removeFromCart: (courseId: number) => Promise<void>;
    clearCart: () => Promise<void>;
    isInCart: (courseId: number) => boolean;
    refreshCart: () => Promise<void>;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

export const CartProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [cartItems, setCartItems] = useState<CartItemData[]>([]);
    const [cartCount, setCartCount] = useState(0);
    const [cartTotal, setCartTotal] = useState('0.00');
    const [isLoading, setIsLoading] = useState(false);
    const { user } = useAuth();
    const { __ } = useTranslation();

    const refreshCart = useCallback(async () => {
        if (!user) {
            setCartItems([]);
            setCartCount(0);
            setCartTotal('0.00');
            return;
        }
        try {
            const data = await userAuthService.getCart();
            setCartItems(data.items || []);
            setCartCount(data.count || 0);
            setCartTotal(data.total || '0.00');
        } catch {
            // Silently fail — user may not be authenticated
        }
    }, [user]);

    useEffect(() => {
        refreshCart();
    }, [refreshCart]);

    const addToCart = useCallback(async (courseId: number): Promise<boolean> => {
        if (!user) {
            toast.error(__('Please log in to add items to your cart.'));
            return false;
        }
        const result = await userAuthService.addToCart(courseId);
        if (result.success) {
            toast.success(result.message || __('Course added to cart successfully.'));
            await refreshCart();
            return true;
        } else {
            if (result.already_exists) {
                toast.info(result.error || __('This course is already in your cart.'));
            } else {
                toast.error(result.error || __('Something went wrong. Please try again.'));
            }
            return false;
        }
    }, [user, refreshCart, __]);

    const removeFromCart = useCallback(async (courseId: number) => {
        const result = await userAuthService.removeFromCart(courseId);
        if (result.success) {
            toast.success(result.message || __('Course removed from cart.'));
            await refreshCart();
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }
    }, [refreshCart, __]);

    const clearCart = useCallback(async () => {
        const result = await userAuthService.clearCart();
        if (result.success) {
            toast.success(result.message || __('Your cart has been cleared.'));
            setCartItems([]);
            setCartCount(0);
            setCartTotal('0.00');
        }
    }, [__]);

    const isInCart = useCallback((courseId: number) => {
        return cartItems.some(item => item.course_id === courseId);
    }, [cartItems]);

    return (
        <CartContext.Provider value={{
            cartItems, cartCount, cartTotal, isLoading,
            addToCart, removeFromCart, clearCart, isInCart, refreshCart,
        }}>
            {children}
        </CartContext.Provider>
    );
};

export const useCart = () => {
    const ctx = useContext(CartContext);
    if (!ctx) throw new Error('useCart must be used within CartProvider');
    return ctx;
};
