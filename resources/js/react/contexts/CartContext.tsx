import React, { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import { userAuthService } from '../services/dataService';
import { useAuth } from './AuthContext';
import { toast } from 'react-toastify';
import { useTranslation } from './TranslationProvider';

interface CartItemData {
    id: number | string;
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
    isInitialized: boolean;
    addToCart: (courseId: number) => Promise<boolean>;
    removeFromCart: (courseId: number) => Promise<void>;
    clearCart: () => Promise<void>;
    isInCart: (courseId: number) => boolean;
    refreshCart: () => Promise<void>;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

/**
 * CartProvider — manages cart state for the entire React app.
 *
 * ## Initialization
 * Fetches cart from the API once on mount, then re-fetches whenever `user`
 * changes (login / logout / registration).  This single useEffect is
 * intentionally simple.
 *
 * ## ⚠️  DO NOT add `setIsInitialized(false)` at the top of the useEffect.
 * That was a past bug. It causes `isInitialized` to briefly flip to false
 * on every dependency change, which can:
 *   - Make `shouldBlockForInitialBoot` in App.tsx re-trigger the
 *     FullScreenLoader during SPA navigation.
 *   - Cause CartPage to flash "empty cart" before data arrives.
 *
 * ## ⚠️  DO NOT add this context to the FullScreenLoader's blocking condition.
 * The cart loads in the background.  Route-specific cart data is already
 * pre-fetched by `routeBootstrap.ts` for /cart and /checkout pages.
 * Adding cart to the blocker creates circular timing issues with NProgress.
 */
export const CartProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [cartItems, setCartItems] = useState<CartItemData[]>([]);
    const [cartCount, setCartCount] = useState(0);
    const [cartTotal, setCartTotal] = useState('0.00');
    const [isLoading, setIsLoading] = useState(false);
    const [isInitialized, setIsInitialized] = useState(false);
    const { user } = useAuth();
    const { __ } = useTranslation();

    const applyCartState = useCallback((data?: { items?: CartItemData[]; count?: number; total?: string } | null) => {
        setCartItems(data?.items || []);
        setCartCount(data?.count || 0);
        setCartTotal(data?.total || '0.00');
    }, []);

    const refreshCart = useCallback(async () => {
        try {
            setIsLoading(true);
            const data = await userAuthService.getCart();
            applyCartState(data);
        } catch {
            // Silently fail — user may not be authenticated
        } finally {
            setIsLoading(false);
            setIsInitialized(true);
        }
    }, [applyCartState]);

    // Fetch cart on mount and whenever `user` changes (login/logout).
    // See class-level doc for why we must NOT reset isInitialized here.
    useEffect(() => {
        let isMounted = true;

        const initializeCart = async () => {
            if (isMounted) {
                setIsLoading(true);
            }

            try {
                const data = await userAuthService.getCart();
                if (isMounted) {
                    applyCartState(data);
                }
            } catch {
                if (isMounted) {
                    applyCartState(null);
                }
            } finally {
                if (isMounted) {
                    setIsInitialized(true);
                    setIsLoading(false);
                }
            }
        };

        void initializeCart();

        return () => {
            isMounted = false;
        };
    }, [applyCartState, user]);

    const addToCart = useCallback(async (courseId: number): Promise<boolean> => {
        const result = await userAuthService.addToCart(courseId);
        if (result.success) {
            toast.success(result.message || __('Course added to cart successfully.'));
            if (result.cart) {
                applyCartState(result.cart);
                setIsInitialized(true);
            } else {
                await refreshCart();
            }
            return true;
        } else {
            if (result.already_exists) {
                toast.info(result.error || __('This course is already in your cart.'));
            } else {
                toast.error(result.error || __('Something went wrong. Please try again.'));
            }
            return false;
        }
    }, [applyCartState, refreshCart, __]);

    const removeFromCart = useCallback(async (courseId: number) => {
        const result = await userAuthService.removeFromCart(courseId);
        if (result.success) {
            toast.success(result.message || __('Course removed from cart.'));
            if (result.cart) {
                applyCartState(result.cart);
                setIsInitialized(true);
            } else {
                await refreshCart();
            }
        } else {
            toast.error(result.error || __('Something went wrong. Please try again.'));
        }
    }, [applyCartState, refreshCart, __]);

    const clearCart = useCallback(async () => {
        const result = await userAuthService.clearCart();
        if (result.success) {
            toast.success(result.message || __('Your cart has been cleared.'));
            applyCartState(result.cart || null);
            setIsInitialized(true);
        }
    }, [applyCartState, __]);

    const isInCart = useCallback((courseId: number) => {
        return cartItems.some(item => item.course_id === courseId);
    }, [cartItems]);

    return (
        <CartContext.Provider value={{
            cartItems, cartCount, cartTotal, isLoading, isInitialized,
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
