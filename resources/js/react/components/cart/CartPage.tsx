import React, { useState } from 'react';
import { useCart } from '../../contexts/CartContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import { Trash2, ShoppingCart, BookOpen, ArrowRight, ArrowLeft, Clock, Loader2 } from 'lucide-react';
import AppLink from '../common/AppLink';
import { useCurrency } from '../../utils/currency';

const CartPage: React.FC = () => {
    const { cartItems, cartCount, cartTotal, removeFromCart, clearCart } = useCart();
    const { __, t } = useTranslation();
    const { dir } = useLanguage();
    const { formatAmount } = useCurrency();
    const [removingItems, setRemovingItems] = useState<number[]>([]);

    const handleRemove = async (courseId: number) => {
        setRemovingItems(prev => [...prev, courseId]);
        await removeFromCart(courseId);
        setRemovingItems(prev => prev.filter(id => id !== courseId));
    };

    const ArrowIcon = dir === 'rtl' ? ArrowLeft : ArrowRight;

    if (cartCount === 0) {
        return (
            <div className="min-h-[60vh] flex items-center justify-center py-16">
                <div className="text-center max-w-md mx-auto px-4">
                    <div className="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-6">
                        <ShoppingCart className="w-10 h-10 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">{__('Your cart is empty')}</h2>
                    <p className="text-gray-500 dark:text-gray-400 mb-8">{__('Browse our courses and find something you love!')}</p>
                    <AppLink
                        to="/courses"
                        className="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-bold hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-500/25"
                    >
                        <BookOpen className="w-5 h-5" />
                        {__('Browse Courses')}
                    </AppLink>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen py-8">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Page Title */}
                <div className="mb-8">
                    <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{__('Shopping Cart')}</h1>
                    <p className="text-gray-500 dark:text-gray-400 mt-1">{cartCount} {__('courses in your cart')}</p>
                </div>

                <div className="flex flex-col lg:flex-row gap-8">
                    {/* Cart Items List */}
                    <div className="flex-1 min-w-0">
                        <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 overflow-hidden">
                            {/* Header */}
                            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                                <span className="text-sm font-semibold text-gray-500 dark:text-gray-400">{cartCount} {__('items')}</span>
                                <button
                                    onClick={clearCart}
                                    className="text-sm text-red-500 hover:text-red-600 font-medium flex items-center gap-1.5 transition-colors"
                                >
                                    <Trash2 className="w-4 h-4" />
                                    {__('Clear All')}
                                </button>
                            </div>

                            {/* Items */}
                            <div className="divide-y divide-gray-100 dark:divide-gray-700">
                                {cartItems.map(item => (
                                    <div key={item.id} className="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        {/* Course Image */}
                                        <AppLink to={`/courses/${item.course?.slug}`} className="shrink-0 w-full sm:w-40 aspect-video sm:aspect-[16/10] rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                                            <img
                                                src={item.course?.image}
                                                alt={t(item.course?.title)}
                                                className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                            />
                                        </AppLink>

                                        {/* Course Info */}
                                        <div className="flex-1 min-w-0">
                                            <AppLink to={`/courses/${item.course?.slug}`}>
                                                <h3 className="text-base font-bold text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition-colors line-clamp-2">
                                                    {t(item.course?.title)}
                                                </h3>
                                            </AppLink>
                                            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{item.course?.instructor_name}</p>
                                            <div className="flex items-center gap-2 mt-2 text-xs text-gray-400 dark:text-gray-500">
                                                <Clock className="w-3.5 h-3.5" />
                                                <span>{item.course?.duration_hours} {__('Hours')}</span>
                                            </div>
                                        </div>

                                        {/* Price & Remove */}
                                        <div className="flex sm:flex-col items-center sm:items-end gap-3 sm:gap-2 w-full sm:w-auto justify-between sm:justify-start shrink-0">
                                            <div className="text-end">
                                                {item.course?.old_price && (
                                                    <p className="text-xs text-gray-400 line-through">{formatAmount(item.course.old_price)}</p>
                                                )}
                                                <p className="text-lg font-bold text-brand-600 dark:text-brand-400">
                                                    {formatAmount(item.price)}
                                                </p>
                                            </div>
                                            <button
                                                onClick={() => handleRemove(item.course_id)}
                                                disabled={removingItems.includes(item.course_id)}
                                                className={`p-2 rounded-lg transition-colors ${
                                                    removingItems.includes(item.course_id)
                                                        ? 'text-red-300 cursor-not-allowed'
                                                        : 'text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20'
                                                }`}
                                                title={__('Remove')}
                                            >
                                                {removingItems.includes(item.course_id) ? (
                                                    <Loader2 className="w-4.5 h-4.5 animate-spin" />
                                                ) : (
                                                    <Trash2 className="w-4.5 h-4.5" />
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Order Summary Sidebar */}
                    <div className="lg:w-80 shrink-0">
                        <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 sticky top-24">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-6">{__('Order Summary')}</h3>

                            <div className="space-y-3 mb-6">
                                <div className="flex justify-between text-sm">
                                    <span className="text-gray-500 dark:text-gray-400">{__('Subtotal')} ({cartCount} {__('items')})</span>
                                    <span className="font-semibold text-gray-900 dark:text-white">{formatAmount(cartTotal)}</span>
                                </div>
                            </div>

                            <div className="border-t border-gray-100 dark:border-gray-700 pt-4 mb-6">
                                <div className="flex justify-between">
                                    <span className="text-base font-bold text-gray-900 dark:text-white">{__('Total')}</span>
                                    <span className="text-xl font-black text-brand-600 dark:text-brand-400">{formatAmount(cartTotal)}</span>
                                </div>
                            </div>

                            <AppLink
                                to="/checkout"
                                className="w-full flex items-center justify-center gap-2 py-3.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-bold hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-500/25 active:scale-[0.98]"
                            >
                                {__('Proceed to Checkout')}
                                <ArrowIcon className="w-4 h-4" />
                            </AppLink>

                            <AppLink
                                to="/courses"
                                className="block text-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 mt-4 transition-colors"
                            >
                                {__('Continue Shopping')}
                            </AppLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CartPage;
