import React, { useEffect, useState } from 'react';
import { Navigate, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useCart } from '../../contexts/CartContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import { dataService } from '../../services/dataService';
import { toast } from 'react-toastify';
import AppLink from '../common/AppLink';
import FullScreenLoader from '../common/FullScreenLoader';
import { ArrowLeft, ArrowRight, BookOpen, CheckCircle2, Clock, CreditCard, Loader2, ShieldCheck, ShoppingCart, TicketPercent, Trash2, XCircle } from 'lucide-react';
import { formatCurrencyAmount, useCurrency } from '../../utils/currency';
import { useCurrentRouteBootstrap } from '../../contexts/RouteBootstrapContext';
import { buildCheckoutBootstrap } from '../../services/routeBootstrap';

const CheckoutPage: React.FC = () => {
    const { user, refreshUser } = useAuth();
    const { cartItems, cartCount, removeFromCart, refreshCart } = useCart();
    const { __, t } = useTranslation();
    const { dir, language } = useLanguage();
    const { currency, formatAmount } = useCurrency();
    const location = useLocation();
    const navigate = useNavigate();
    const routeBootstrap = useCurrentRouteBootstrap<any>();
    const initialCheckoutBootstrap = user ? routeBootstrap?.checkout : undefined;
    const bootstrapCart = initialCheckoutBootstrap?.cart;

    const [removingItems, setRemovingItems] = useState<number[]>([]);
    const [gateways, setGateways] = useState<any[]>(() => initialCheckoutBootstrap?.gateways || []);
    const [loadingGateways, setLoadingGateways] = useState<boolean>(() => !!user && !initialCheckoutBootstrap);
    const [loadingQuote, setLoadingQuote] = useState<boolean>(() => !!user && !initialCheckoutBootstrap?.quote && !!initialCheckoutBootstrap?.selectedGatewayId);
    const [applyingPromo, setApplyingPromo] = useState(false);
    const [removingPromo, setRemovingPromo] = useState(false);
    const [selectedGatewayId, setSelectedGatewayId] = useState<number | null>(() => initialCheckoutBootstrap?.selectedGatewayId ?? null);
    const [processingCheckout, setProcessingCheckout] = useState(false);
    const [promoCodeInput, setPromoCodeInput] = useState('');
    const [appliedPromoCode, setAppliedPromoCode] = useState<string | null>(null);
    const [promoError, setPromoError] = useState<string | null>(null);
    const [quote, setQuote] = useState<any | null>(() => initialCheckoutBootstrap?.quote ?? null);

    useEffect(() => {
        if (!user) {
            return;
        }

        let isMounted = true;

        const loadCheckoutBootstrap = async () => {
            if (initialCheckoutBootstrap) {
                setGateways(initialCheckoutBootstrap.gateways || []);
                setSelectedGatewayId((current) => current ?? initialCheckoutBootstrap.selectedGatewayId ?? null);
                setQuote((current) => current ?? initialCheckoutBootstrap.quote ?? null);
                setLoadingGateways(false);
                setLoadingQuote(false);
                return;
            }

            if (!initialCheckoutBootstrap) {
                setLoadingGateways(true);
            }

            const bootstrap = await buildCheckoutBootstrap();

            if (!isMounted) {
                return;
            }

            setGateways(bootstrap.gateways || []);
            setSelectedGatewayId((current) => current ?? bootstrap.selectedGatewayId);

            if (!appliedPromoCode && bootstrap.quote) {
                setQuote(bootstrap.quote);
            }

            setLoadingGateways(false);
            setLoadingQuote(false);
        };

        void loadCheckoutBootstrap();

        return () => {
            isMounted = false;
        };
    }, [initialCheckoutBootstrap, user]);

    const loadQuote = async (gatewayId: number, promoCode?: string | null) => {
        setLoadingQuote(true);

        const result = await dataService.getCheckoutQuote({
            payment_gateway_id: gatewayId,
            promo_code: promoCode || undefined,
        });

        if (result.success && result.quote) {
            setQuote(result.quote);
            setPromoError(null);
            return { success: true as const, quote: result.quote };
        }

        if (promoCode) {
            setPromoError(result.error || __('Promo code could not be applied.'));
        } else if (gateways.length > 0) {
            toast.error(result.error || __('Checkout quote could not be loaded.'));
        }

        return { success: false as const, error: result.error };
    };

    const effectiveCartItems = cartItems.length > 0 ? cartItems : (bootstrapCart?.items || []);
    const effectiveCartCount = cartCount > 0 ? cartCount : Number(bootstrapCart?.count || 0);

    useEffect(() => {
        if (!user || !selectedGatewayId || effectiveCartCount === 0) {
            return;
        }

        const quoteGatewayId = quote?.gateway?.id ? Number(quote.gateway.id) : null;
        const quotePromoCode = quote?.promo?.code || null;
        const quoteItemCount = Number(quote?.count ?? -1);

        if (
            quote
            && quoteGatewayId === selectedGatewayId
            && quotePromoCode === appliedPromoCode
            && quoteItemCount === effectiveCartCount
        ) {
            return;
        }

        const syncQuote = async () => {
            await loadQuote(selectedGatewayId, appliedPromoCode);
            setLoadingQuote(false);
        };

        void syncQuote();
    }, [appliedPromoCode, effectiveCartCount, quote, selectedGatewayId, user]);

    useEffect(() => {
        if (!user) {
            return;
        }

        const params = new URLSearchParams(location.search);
        const payment = params.get('payment');
        const transactionId = params.get('transactionId');

        if (!payment) return;

        const finalize = async () => {
            try {
                if (payment === 'success') {
                    await Promise.all([refreshCart(), refreshUser()]);
                    toast.success(__('Payment completed successfully. Your courses are now unlocked.'));
                } else if (payment === 'pending' && transactionId) {
                    const result = await dataService.verifyPayment(Number(transactionId));
                    await Promise.all([refreshCart(), refreshUser()]);

                    if (result.success) {
                        toast.success(__('Payment completed successfully. Your courses are now unlocked.'));
                    } else if (result.status === 'processing' || result.status === 'pending') {
                        toast.info(__('Your payment is still processing. We will update your access once it is confirmed.'));
                    } else {
                        toast.error(result.error || __('Payment could not be confirmed.'));
                    }
                } else {
                    toast.error(__('Payment failed or was cancelled.'));
                }
            } catch (error) {
                toast.error(__('We could not finish syncing your payment status. Please refresh the page.'));
            } finally {
                navigate(location.pathname, { replace: true });
            }
        };

        void finalize();
    }, [__, location.pathname, location.search, navigate, refreshCart, refreshUser, user]);

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    const handleRemove = async (courseId: number) => {
        setRemovingItems(prev => [...prev, courseId]);
        await removeFromCart(courseId);
        setRemovingItems(prev => prev.filter(id => id !== courseId));
    };

    const handleApplyPromo = async () => {
        if (!selectedGatewayId) return;

        const normalizedPromoCode = promoCodeInput.trim().toUpperCase();

        if (!normalizedPromoCode) {
            setPromoError(__('Please enter a promo code.'));
            return;
        }

        setApplyingPromo(true);
        const result = await loadQuote(selectedGatewayId, normalizedPromoCode);

        if (result.success) {
            const appliedCode = result.quote?.promo?.code || normalizedPromoCode;
            setAppliedPromoCode(appliedCode);
            setPromoCodeInput(appliedCode);
            toast.success(__('Promo code applied successfully.'));
        }

        setLoadingQuote(false);
        setApplyingPromo(false);
    };

    const handleRemovePromo = async () => {
        if (!selectedGatewayId) return;

        setRemovingPromo(true);
        setPromoCodeInput('');
        setAppliedPromoCode(null);
        setPromoError(null);
        await loadQuote(selectedGatewayId, null);
        setLoadingQuote(false);
        setRemovingPromo(false);
    };

    const handleCheckout = async () => {
        if (!selectedGatewayId) return;

        setProcessingCheckout(true);
        const result = await dataService.initiateCheckout({
            payment_gateway_id: selectedGatewayId,
            promo_code: appliedPromoCode || undefined,
        });

        if (result.success && result.paymentUrl) {
            window.location.href = result.paymentUrl;
            return;
        }

        setProcessingCheckout(false);
        toast.error(result.error || __('Checkout initiation failed'));
    };

    const summaryTotals = quote?.totals || {
        subtotal_before_discount: '0.00',
        discount_amount: '0.00',
        subtotal_after_discount: '0.00',
        gateway_processing_fee: '0.00',
        total_amount: '0.00',
    };
    const hasUnappliedPromo = promoCodeInput.trim() !== '' && promoCodeInput.trim().toUpperCase() !== (appliedPromoCode || '');
    const selectedGateway = gateways.find(gateway => Number(gateway.id) === selectedGatewayId);
    const activeCurrency = quote?.currency || currency;
    const ForwardArrowIcon = dir === 'rtl' ? ArrowLeft : ArrowRight;
    const BackArrowIcon = dir === 'rtl' ? ArrowRight : ArrowLeft;
    const formatQuoteAmount = (amount: number | string | null | undefined) => formatCurrencyAmount(amount, activeCurrency, language);

    if ((loadingGateways || loadingQuote) && effectiveCartCount === 0 && !bootstrapCart) {
        return <FullScreenLoader label={__('Loading checkout...')} />;
    }

    if (effectiveCartCount === 0) {
        return (
            <div className="min-h-[70vh] flex items-center justify-center py-16">
                <div className="text-center max-w-lg mx-auto px-4">
                    <div className="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-6">
                        <ShoppingCart className="w-10 h-10 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-3">{__('Your cart is empty')}</h1>
                    <p className="text-gray-500 dark:text-gray-400 mb-8">{__('Add courses to your cart before heading to checkout.')}</p>
                    <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <AppLink
                            to="/cart"
                            className="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl font-bold text-gray-700 dark:text-gray-200 hover:border-brand-400 transition-colors"
                        >
                            <BackArrowIcon className="w-4 h-4" />
                            {__('Back to Cart')}
                        </AppLink>
                        <AppLink
                            to="/courses"
                            className="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl font-bold hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-500/25"
                        >
                            <BookOpen className="w-5 h-5" />
                            {__('Browse Courses')}
                        </AppLink>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen py-8">
            <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{__('Checkout')}</h1>
                        <p className="text-gray-500 dark:text-gray-400 mt-1">{effectiveCartCount} {__('items ready for purchase')}</p>
                    </div>
                    <AppLink
                        to="/cart"
                        className="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 hover:border-brand-400 transition-colors"
                    >
                        <BackArrowIcon className="w-4 h-4" />
                        {__('Back to Cart')}
                    </AppLink>
                </div>

                <div className="grid xl:grid-cols-[1.15fr_0.85fr] gap-8">
                    <div className="space-y-6">
                        <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 overflow-hidden">
                            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                                <span className="text-sm font-semibold text-gray-500 dark:text-gray-400">{effectiveCartCount} {__('items')}</span>
                                <AppLink
                                    to="/cart"
                                    className="text-sm font-medium text-brand-600 dark:text-brand-400 hover:text-brand-700"
                                >
                                    {__('Edit Cart')}
                                </AppLink>
                            </div>

                            <div className="divide-y divide-gray-100 dark:divide-gray-700">
                                {effectiveCartItems.map(item => (
                                    <div key={item.id} className="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <AppLink to={`/courses/${item.course?.slug}`} className="shrink-0 w-full sm:w-40 aspect-video sm:aspect-[16/10] rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700">
                                            <img
                                                src={item.course?.image}
                                                alt={t(item.course?.title)}
                                                className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                            />
                                        </AppLink>

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

                        <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                            <div className="flex items-center gap-2 mb-5 text-sm font-bold text-brand-600 dark:text-brand-400">
                                <ShieldCheck className="w-4 h-4" />
                                {__('Choose Payment Method')}
                            </div>

                            {loadingGateways ? (
                                <div className="flex items-center justify-center min-h-48">
                                    <Loader2 className="w-8 h-8 animate-spin text-brand-600" />
                                </div>
                            ) : gateways.length > 0 ? (
                                <div className="grid sm:grid-cols-2 gap-4">
                                    {gateways.map((gateway) => {
                                        const isSelected = selectedGatewayId === Number(gateway.id);

                                        return (
                                            <button
                                                key={gateway.id}
                                                onClick={() => setSelectedGatewayId(Number(gateway.id))}
                                                disabled={processingCheckout}
                                                className={`rounded-2xl border bg-gray-50 dark:bg-gray-800/80 p-5 text-start transition-colors disabled:opacity-70 ${
                                                    isSelected
                                                        ? 'border-brand-500 ring-2 ring-brand-200 dark:ring-brand-900/60 bg-brand-50 dark:bg-gray-800'
                                                        : 'border-gray-200 dark:border-gray-700 hover:border-brand-400 hover:bg-brand-50 dark:hover:bg-gray-800'
                                                }`}
                                            >
                                                <div className="flex items-center gap-3 mb-4">
                                                    {gateway.logo ? (
                                                        <img src={gateway.logo} alt={t(gateway.name)} className="h-10 w-auto object-contain" />
                                                    ) : (
                                                        <div className="w-10 h-10 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                                            <CreditCard className="w-5 h-5 text-brand-600" />
                                                        </div>
                                                    )}
                                                    <div className="min-w-0">
                                                        <div className="font-bold text-gray-900 dark:text-white">{t(gateway.name)}</div>
                                                        <div className="text-xs text-gray-500 dark:text-gray-400">{gateway.code}</div>
                                                    </div>
                                                </div>

                                                <div className="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 min-h-10">
                                                    {gateway.description ? t(gateway.description) : __('Secure payment gateway')}
                                                </div>

                                                <div className="mt-4 text-sm font-bold text-brand-600 dark:text-brand-400 flex items-center gap-2">
                                                    {isSelected ? <CheckCircle2 className="w-4 h-4" /> : null}
                                                    {isSelected ? __('Selected') : __('Select')}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            ) : (
                                <div className="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
                                    {__('No payment methods are currently available.')}
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="xl:sticky xl:top-24 self-start">
                        <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
                            <h3 className="text-lg font-black text-gray-900 dark:text-white mb-5">{__('Order Summary')}</h3>

                            <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-950/40 p-4 mb-5">
                                <div className="flex items-center gap-2 mb-3 text-sm font-bold text-gray-900 dark:text-white">
                                    <TicketPercent className="w-4 h-4 text-brand-600 dark:text-brand-400" />
                                    {__('Promo Code')}
                                </div>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        value={promoCodeInput}
                                        onChange={(event) => {
                                            setPromoCodeInput(event.target.value.toUpperCase());
                                            if (promoError) {
                                                setPromoError(null);
                                            }
                                        }}
                                        placeholder={__('Enter promo code')}
                                        className="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none"
                                    />
                                    {appliedPromoCode ? (
                                        <button
                                            type="button"
                                            onClick={() => void handleRemovePromo()}
                                            disabled={loadingQuote || processingCheckout || removingPromo}
                                            className="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 hover:border-brand-400"
                                        >
                                            <XCircle className="w-4 h-4" />
                                            {removingPromo ? __('Removing...') : __('Remove')}
                                        </button>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() => void handleApplyPromo()}
                                            disabled={loadingQuote || processingCheckout || !selectedGatewayId || applyingPromo}
                                            className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700 disabled:opacity-70"
                                        >
                                            {applyingPromo ? <Loader2 className="w-4 h-4 animate-spin" /> : <TicketPercent className="w-4 h-4" />}
                                            {applyingPromo ? __('Applying...') : __('Apply')}
                                        </button>
                                    )}
                                </div>
                                {promoError ? (
                                    <p className="mt-2 text-sm text-red-500">{promoError}</p>
                                ) : appliedPromoCode ? (
                                    <p className="mt-2 text-sm text-green-600 dark:text-green-400">{__('Promo code applied successfully.')}</p>
                                ) : null}
                                {hasUnappliedPromo ? (
                                    <p className="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                        {__('Apply or clear the promo code before completing your purchase.')}
                                    </p>
                                ) : null}
                            </div>

                            <div className="space-y-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-500 dark:text-gray-400">{__('Items')}</span>
                                    <span className="font-bold text-gray-900 dark:text-white">{effectiveCartCount}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-500 dark:text-gray-400">{__('Subtotal')}</span>
                                    <span className="font-bold text-gray-900 dark:text-white">{formatQuoteAmount(summaryTotals.subtotal_before_discount)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-500 dark:text-gray-400">{__('Discount')}</span>
                                    <span className="font-bold text-green-600 dark:text-green-400">- {formatQuoteAmount(summaryTotals.discount_amount)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-gray-500 dark:text-gray-400">{__('Gateway Fee')}</span>
                                    <span className="font-bold text-gray-900 dark:text-white">{formatQuoteAmount(summaryTotals.gateway_processing_fee)}</span>
                                </div>
                            </div>

                            <div className="mt-5 pt-5 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                                <span className="text-base font-black text-gray-900 dark:text-white">{__('Total')}</span>
                                <span className="text-2xl font-black text-brand-600 dark:text-brand-400">{formatQuoteAmount(summaryTotals.total_amount)}</span>
                            </div>

                            <p className="mt-5 text-xs leading-6 text-gray-500 dark:text-gray-400">
                                {__('You will be redirected to the selected payment provider to complete your purchase securely.')}
                            </p>

                            <button
                                type="button"
                                onClick={() => void handleCheckout()}
                                disabled={!selectedGatewayId || processingCheckout || loadingQuote || hasUnappliedPromo}
                                className="mt-5 w-full rounded-2xl bg-gradient-to-r from-brand-600 to-brand-700 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition-all hover:from-brand-700 hover:to-brand-800 disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                {processingCheckout ? (
                                    <span className="inline-flex items-center gap-2">
                                        <Loader2 className="w-4 h-4 animate-spin" />
                                        {__('Redirecting...')}
                                    </span>
                                ) : loadingQuote ? (
                                    __('Updating total...')
                                ) : selectedGateway ? (
                                    <span className="inline-flex items-center gap-2">
                                        {__('Complete Purchase with :gateway', { gateway: t(selectedGateway.name) })}
                                        <ForwardArrowIcon className="w-4 h-4" />
                                    </span>
                                ) : (
                                    __('Complete Purchase')
                                )}
                            </button>

                            <AppLink
                                to="/courses"
                                className="mt-4 inline-flex w-full items-center justify-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                            >
                                <BookOpen className="w-4 h-4" />
                                {__('Continue Shopping')}
                            </AppLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CheckoutPage;
