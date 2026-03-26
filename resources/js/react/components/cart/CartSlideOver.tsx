import React, { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useCart } from '../../contexts/CartContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { X, Trash2, ShoppingCart, ArrowRight, ArrowLeft, Loader2 } from 'lucide-react';
import AppLink from '../common/AppLink';
import { motion, AnimatePresence } from 'framer-motion';

interface CartSlideOverProps {
    isOpen: boolean;
    onClose: () => void;
}

const CartSlideOver: React.FC<CartSlideOverProps> = ({ isOpen, onClose }) => {
    const { __, t } = useTranslation();
    const { cartItems, cartCount, cartTotal, removeFromCart } = useCart();
    const { dir } = useLanguage();
    const [removingItems, setRemovingItems] = useState<number[]>([]);

    const ArrowIcon = dir === 'rtl' ? ArrowLeft : ArrowRight;

    const handleRemove = async (courseId: number) => {
        setRemovingItems(prev => [...prev, courseId]);
        await removeFromCart(courseId);
        setRemovingItems(prev => prev.filter(id => id !== courseId));
    };

    // Prevent body scroll when open
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    // Slide animation parameters based on RTL/LTR
    // LTR (English): slides from right (100% to 0)
    // RTL (Arabic): slides from left (-100% to 0)
    const slideDirection = dir === 'rtl' ? -100 : 100;

    return createPortal(
        <AnimatePresence>
            {isOpen && (
                <div className="fixed inset-0 z-[100]">
                    {/* Backdrop */}
                    <motion.div 
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.2 }}
                        className="absolute inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm"
                        onClick={onClose}
                    />

                    {/* Slide-over panel ALWAYS ON THE LEFT in Arabic, RIGHT in English */}
                    <motion.div 
                        initial={{ x: `${slideDirection}%` }}
                        animate={{ x: 0 }}
                        exit={{ x: `${slideDirection}%` }}
                        transition={{ duration: 0.3, ease: 'easeInOut' }}
                        className="absolute top-0 bottom-0 ltr:right-0 rtl:left-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl flex flex-col"
                    >
                        {/* Header */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-full bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                    <ShoppingCart className="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold text-gray-900 dark:text-white">{__('Shopping Cart')}</h2>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">{cartCount} {__('items')}</p>
                                </div>
                            </div>
                            <button 
                                onClick={onClose}
                                className="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {/* Body - Cart Items */}
                        <div className="flex-1 overflow-y-auto p-6" style={{ scrollbarWidth: 'thin' }}>
                            {cartCount === 0 ? (
                                <div className="flex flex-col items-center justify-center h-full text-center opacity-70">
                                    <ShoppingCart className="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
                                    <p className="text-lg font-bold text-gray-900 dark:text-white mb-2">{__('Your cart is empty')}</p>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">{__('Browse our courses and find something you love!')}</p>
                                </div>
                            ) : (
                                <div className="flex flex-col gap-5">
                                    <AnimatePresence>
                                        {cartItems.map(item => (
                                            <motion.div 
                                                key={item.id} 
                                                layout
                                                initial={{ opacity: 0, scale: 0.95 }}
                                                animate={{ opacity: 1, scale: 1 }}
                                                exit={{ opacity: 0, scale: 0.95 }}
                                                transition={{ duration: 0.2 }}
                                                className="flex gap-4 group"
                                            >
                                                <AppLink 
                                                    to={`/courses/${item.course?.slug}`}
                                                    onClick={onClose}
                                                    className="w-24 h-20 shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 relative"
                                                >
                                                    <img 
                                                        src={item.course?.image} 
                                                        alt={t(item.course?.title)}
                                                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                    />
                                                </AppLink>
                                                
                                                <div className="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                    <div className="flex justify-between items-start gap-2">
                                                        <AppLink 
                                                            to={`/courses/${item.course?.slug}`}
                                                            onClick={onClose}
                                                            className="text-sm font-bold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 line-clamp-2 transition-colors"
                                                        >
                                                            {t(item.course?.title)}
                                                        </AppLink>
                                                        <button 
                                                            onClick={(e) => { e.preventDefault(); handleRemove(item.course_id); }}
                                                            disabled={removingItems.includes(item.course_id)}
                                                            className={`p-1.5 rounded-md transition-colors shrink-0 ${
                                                                removingItems.includes(item.course_id)
                                                                    ? 'text-red-300 cursor-not-allowed'
                                                                    : 'text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20'
                                                            }`}
                                                        >
                                                            {removingItems.includes(item.course_id) ? (
                                                                <Loader2 className="w-4 h-4 animate-spin" />
                                                            ) : (
                                                                <Trash2 className="w-4 h-4" />
                                                            )}
                                                        </button>
                                                    </div>
                                                    <div className="mt-2 flex items-end justify-between">
                                                        <span className="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 flex-1 rtl:pl-2 ltr:pr-2">
                                                            {item.course?.instructor_name}
                                                        </span>
                                                        <span className="text-sm font-bold text-brand-600 dark:text-brand-400 shrink-0">
                                                            {item.price} {__('Currency symbol')}
                                                        </span>
                                                    </div>
                                                </div>
                                            </motion.div>
                                        ))}
                                    </AnimatePresence>
                                </div>
                            )}
                        </div>

                        {/* Footer - Order Summary & Actions */}
                        {cartCount > 0 && (
                            <div className="border-t border-gray-100 dark:border-gray-700 p-6 bg-gray-50/50 dark:bg-gray-800/50 shrink-0">
                                <div className="flex items-center justify-between mb-4">
                                    <span className="text-base font-semibold text-gray-900 dark:text-white">{__('Subtotal')}</span>
                                    <span className="text-lg font-bold text-brand-600 dark:text-brand-400">{cartTotal} {__('Currency symbol')}</span>
                                </div>
                                
                                <div className="flex flex-col gap-3">
                                    <button className="w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-bold hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-500/25 active:scale-[0.98]">
                                        {__('Proceed to Checkout')}
                                        <ArrowIcon className="w-4 h-4" />
                                    </button>
                                    
                                    <AppLink 
                                        to="/cart"
                                        onClick={onClose}
                                        className="flex items-center justify-center w-full py-3 text-sm font-bold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        {__('View Cart')}
                                    </AppLink>
                                </div>
                            </div>
                        )}
                    </motion.div>
                </div>
            )}
        </AnimatePresence>,
        document.body
    );
};

export default CartSlideOver;
