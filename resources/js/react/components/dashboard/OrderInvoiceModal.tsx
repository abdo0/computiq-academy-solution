import React, { useEffect, useRef } from 'react';
import { X, Printer, CheckCircle2, AlertCircle, RefreshCw, XCircle } from 'lucide-react';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useLanguage } from '../../contexts/LanguageContext';
import Logo from '../Logo';

interface OrderInvoiceModalProps {
    order: any;
    isOpen: boolean;
    autoPrint?: boolean;
    onAutoPrintHandled?: () => void;
    onClose: () => void;
}

const OrderInvoiceModal: React.FC<OrderInvoiceModalProps> = ({ order, isOpen, autoPrint = false, onAutoPrintHandled, onClose }) => {
    const { __ } = useTranslation();
    const { language } = useLanguage();
    const didAutoPrintRef = useRef(false);
    const safeOrder = order ?? {};

    const orderDate = new Date(order?.created_at ?? Date.now());
    const locale = language === 'ar' ? 'ar-IQ' : language === 'ku' ? 'ckb-IQ' : 'en-US';
    const isRtl = language === 'ar' || language === 'ku';

    const resolveLocalizedValue = (value: any) => {
        if (!value) return '';
        if (typeof value === 'string') return value;

        return String(
            value[language]
            ?? value.ar
            ?? value.en
            ?? value.ku
            ?? Object.values(value)[0]
            ?? ''
        );
    };

    const escapeHtml = (value: unknown) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const formatAmount = (value: unknown) => {
        const numericValue = Number(value ?? 0);
        return `${new Intl.NumberFormat(locale).format(Number.isFinite(numericValue) ? numericValue : 0)} ${__('IQD')}`;
    };

    const formatDate = (includeTime = false) => new Intl.DateTimeFormat(
        locale,
        includeTime ? { dateStyle: 'long', timeStyle: 'short' } : { dateStyle: 'medium' }
    ).format(orderDate);

    const translateStatus = (status: unknown) => {
        const normalizedStatus = String(status || '').trim().toLowerCase();

        if (!normalizedStatus) {
            return __('N/A');
        }

        return __(normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1));
    };

    let pmName = safeOrder.payment_method;
    if (!pmName && safeOrder.transactions?.[0]?.payment_gateway) {
        const nameData = safeOrder.transactions[0].payment_gateway.name;
        if (nameData) {
            pmName = resolveLocalizedValue(nameData);
        }
        if (!pmName) pmName = safeOrder.transactions[0].payment_gateway.code;
    }
    const paymentMethodName = pmName || __('N/A');
    const normalizedStatus = String(safeOrder.status || '').trim().toLowerCase();
    const translatedStatus = translateStatus(normalizedStatus);
    const orderReference = safeOrder.order_ref || __('N/A');
    const transactionId = safeOrder.transaction_id || __('N/A');
    const invoiceItems = Array.isArray(safeOrder.items)
        ? safeOrder.items.map((item: any) => ({
            title: resolveLocalizedValue(item.course?.title || item.title) || __('N/A'),
            amount: item.total_price || item.unit_price || item.amount || 0,
        }))
        : [];
    const statusMeta = normalizedStatus === 'paid'
        ? {
            icon: CheckCircle2,
            badgeClassName: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300',
            printBackground: '#ecfdf5',
            printBorder: '#a7f3d0',
            printColor: '#047857',
        }
        : normalizedStatus === 'processing'
            ? {
                icon: RefreshCw,
                badgeClassName: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-300',
                printBackground: '#eff6ff',
                printBorder: '#bae6fd',
                printColor: '#0369a1',
            }
            : normalizedStatus === 'failed' || normalizedStatus === 'cancelled'
                ? {
                    icon: XCircle,
                    badgeClassName: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300',
                    printBackground: '#fff1f2',
                    printBorder: '#fecdd3',
                    printColor: '#be123c',
                }
                : {
                    icon: AlertCircle,
                    badgeClassName: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300',
                    printBackground: '#fffbeb',
                    printBorder: '#fde68a',
                    printColor: '#b45309',
                };
    const StatusIcon = statusMeta.icon;

    const handlePrint = () => {
        const printWindow = window.open('', '_blank', 'width=800,height=900,toolbar=no,menubar=no,scrollbars=yes');

        if (printWindow) {
            const printRows = invoiceItems.length > 0
                ? invoiceItems.map((item) => `
                    <tr>
                        <td class="item-cell">${escapeHtml(item.title)}</td>
                        <td class="amount-cell" dir="ltr">${escapeHtml(formatAmount(item.amount))}</td>
                    </tr>
                `).join('')
                : `
                    <tr>
                        <td colspan="2" class="empty-state">${escapeHtml(__('No items'))}</td>
                    </tr>
                `;

            printWindow.document.write(`
                <!DOCTYPE html>
                <html dir="${isRtl ? 'rtl' : 'ltr'}" lang="${language}">
                <head>
                    <title></title>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
                    <style>
                        @page {
                            size: auto;
                            margin: 0;
                        }

                        * {
                            box-sizing: border-box;
                        }

                        html, body {
                            margin: 0;
                            padding: 0;
                            background: #ffffff;
                        }

                        body {
                            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            color: #0f172a;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }

                        .page-shell {
                            padding: 16mm 14mm;
                        }

                        .invoice-sheet {
                            max-width: 780px;
                            margin: 0 auto;
                            padding: 28px;
                            border: 1px solid #dbe7f5;
                            border-radius: 30px;
                            background:
                                radial-gradient(circle at top ${isRtl ? 'right' : 'left'}, rgba(45, 140, 255, 0.12), transparent 28%),
                                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                        }

                        .header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            gap: 24px;
                            border-bottom: 1px solid #dbe7f5;
                            padding-bottom: 24px;
                            margin-bottom: 24px;
                        }

                        .brand-name {
                            margin: 0;
                            font-size: 22px;
                            font-weight: 800;
                            color: #17305f;
                        }

                        .brand-subtitle {
                            margin: 6px 0 0;
                            color: #64748b;
                            font-size: 13px;
                        }

                        .invoice-heading {
                            text-align: ${isRtl ? 'left' : 'right'};
                        }

                        .invoice-title {
                            margin: 0;
                            font-size: 29px;
                            font-weight: 800;
                            color: #0f172a;
                        }

                        .reference-chip {
                            display: inline-flex;
                            margin-top: 10px;
                            padding: 8px 14px;
                            border-radius: 999px;
                            background: #eff6ff;
                            border: 1px solid #bfdbfe;
                            color: #1d4ed8;
                            font-size: 13px;
                            font-weight: 700;
                            direction: ltr;
                        }

                        .status-chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 8px;
                            margin-top: 14px;
                            padding: 8px 14px;
                            border-radius: 999px;
                            background: ${statusMeta.printBackground};
                            border: 1px solid ${statusMeta.printBorder};
                            color: ${statusMeta.printColor};
                            font-size: 13px;
                            font-weight: 700;
                        }

                        .meta-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 14px;
                            margin-bottom: 24px;
                        }

                        .meta-card {
                            padding: 16px 18px;
                            border-radius: 20px;
                            border: 1px solid #dbe7f5;
                            background: rgba(248, 251, 255, 0.92);
                            min-height: 92px;
                        }

                        .label {
                            margin: 0 0 8px;
                            font-size: 12px;
                            font-weight: 700;
                            color: #64748b;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                        }

                        .value {
                            margin: 0;
                            font-size: 15px;
                            font-weight: 700;
                            color: #0f172a;
                            word-break: break-word;
                        }

                        .value-muted {
                            color: #475569;
                        }

                        .table-shell {
                            overflow: hidden;
                            border-radius: 22px;
                            border: 1px solid #dbe7f5;
                            background: #ffffff;
                            margin-bottom: 24px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }

                        th {
                            padding: 14px 18px;
                            text-align: ${isRtl ? 'right' : 'left'};
                            font-size: 12px;
                            font-weight: 800;
                            color: #64748b;
                            text-transform: uppercase;
                            letter-spacing: 0.08em;
                            background: #eff6ff;
                            border-bottom: 1px solid #dbe7f5;
                        }

                        td {
                            padding: 18px;
                            border-bottom: 1px solid #e2e8f0;
                            font-size: 14px;
                        }

                        tbody tr:last-child td {
                            border-bottom: none;
                        }

                        .item-cell {
                            font-weight: 700;
                            color: #0f172a;
                        }

                        .amount-cell {
                            text-align: ${isRtl ? 'left' : 'right'};
                            white-space: nowrap;
                            font-weight: 800;
                            color: #17305f;
                        }

                        .empty-state {
                            text-align: center;
                            color: #94a3b8;
                            padding: 24px 18px;
                        }

                        .summary-card {
                            margin-${isRtl ? 'right' : 'left'}: auto;
                            width: 100%;
                            max-width: 320px;
                            padding: 18px 20px;
                            border-radius: 24px;
                            border: 1px solid #dbe7f5;
                            background: #ffffff;
                        }

                        .summary-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 16px;
                            font-size: 14px;
                            color: #475569;
                        }

                        .summary-total {
                            margin-top: 14px;
                            padding-top: 14px;
                            border-top: 1px solid #dbe7f5;
                            font-size: 18px;
                            font-weight: 800;
                            color: #17305f;
                        }

                        .summary-total .summary-label {
                            color: #0f172a;
                        }

                        .print-footer {
                            margin-top: 32px;
                            text-align: center;
                            color: #64748b;
                            font-size: 13px;
                        }

                        @media print {
                            .page-shell {
                                padding: 10mm;
                            }

                            .invoice-sheet {
                                max-width: none;
                                padding: 0;
                                border: none;
                                border-radius: 0;
                                background: #ffffff;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="page-shell">
                        <div class="invoice-sheet">
                            <div class="header">
                                <div>
                                    <p class="brand-name">${escapeHtml(__('Computiq Academy'))}</p>
                                    <p class="brand-subtitle">${escapeHtml(__('Invoice Details'))}</p>
                                </div>
                                <div class="invoice-heading">
                                    <h1 class="invoice-title">${escapeHtml(__('Invoice'))}</h1>
                                    <div class="reference-chip">#${escapeHtml(orderReference)}</div>
                                    <div class="status-chip">${escapeHtml(translatedStatus)}</div>
                                </div>
                            </div>

                            <div class="meta-grid">
                                <div class="meta-card">
                                    <p class="label">${escapeHtml(__('Reference'))}</p>
                                    <p class="value" dir="ltr">#${escapeHtml(orderReference)}</p>
                                </div>
                                <div class="meta-card">
                                    <p class="label">${escapeHtml(__('Date'))}</p>
                                    <p class="value">${escapeHtml(formatDate(true))}</p>
                                </div>
                                <div class="meta-card">
                                    <p class="label">${escapeHtml(__('Payment Method'))}</p>
                                    <p class="value value-muted">${escapeHtml(paymentMethodName)}</p>
                                </div>
                                <div class="meta-card">
                                    <p class="label">${escapeHtml(__('Transaction ID'))}</p>
                                    <p class="value value-muted" dir="ltr">${escapeHtml(transactionId)}</p>
                                </div>
                            </div>

                            <div class="table-shell">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>${escapeHtml(__('Description'))}</th>
                                            <th style="text-align: ${isRtl ? 'left' : 'right'};">${escapeHtml(__('Amount'))}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${printRows}
                                    </tbody>
                                </table>
                            </div>

                            <div class="summary-card">
                                <div class="summary-row">
                                    <span class="summary-label">${escapeHtml(__('Subtotal'))}</span>
                                    <strong dir="ltr">${escapeHtml(formatAmount(order.total_amount))}</strong>
                                </div>
                                <div class="summary-row summary-total">
                                    <span class="summary-label">${escapeHtml(__('Total'))}</span>
                                    <strong dir="ltr">${escapeHtml(formatAmount(order.total_amount))}</strong>
                                </div>
                            </div>

                            <div class="print-footer">
                                ${escapeHtml(__('Thank you for choosing Computiq Academy'))}
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.onload = () => {
                const triggerPrint = () => {
                    printWindow.focus();
                    printWindow.print();
                    setTimeout(() => printWindow.close(), 200);
                };

                if ('fonts' in printWindow.document) {
                    printWindow.document.fonts.ready.then(triggerPrint).catch(triggerPrint);
                    return;
                }

                setTimeout(triggerPrint, 300);
            };
        }
    };

    useEffect(() => {
        if (!isOpen || !order) {
            didAutoPrintRef.current = false;
            return;
        }

        if (!autoPrint || didAutoPrintRef.current) {
            return;
        }

        didAutoPrintRef.current = true;

        const timer = window.setTimeout(() => {
            handlePrint();
            onAutoPrintHandled?.();
        }, 120);

        return () => window.clearTimeout(timer);
    }, [autoPrint, handlePrint, isOpen, onAutoPrintHandled]);

    if (!isOpen || !order) return null;

    return (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div
                className="bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl w-full max-w-3xl overflow-hidden animate-fade-in-down border border-gray-100 dark:border-gray-800"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header Actions */}
                <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-white/90 dark:bg-gray-900/90">
                    <h3 className="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2 font-sans">
                        {__('Invoice Details')}
                    </h3>
                    <div className="flex items-center gap-2">
                        <button
                            onClick={handlePrint}
                            className="p-2 text-gray-500 hover:text-brand-600 dark:hover:text-brand-400 bg-white dark:bg-gray-800 hover:bg-brand-50 dark:hover:bg-gray-700 rounded-lg transition-colors border border-gray-200 dark:border-gray-700 tooltip flex items-center gap-2 px-3 text-sm font-medium"
                        >
                            <Printer className="w-4 h-4" />
                            <span className="hidden sm:inline">{__('Print')}</span>
                        </button>
                        <button
                            onClick={onClose}
                            className="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {/* Printable Content Wrapper */}
                <div className="p-6 md:p-8 font-sans">
                    {/* Invoice Header */}
                    <div className="relative overflow-hidden rounded-[1.75rem] border border-brand-100/80 dark:border-brand-900/40 bg-[radial-gradient(circle_at_top_left,_rgba(45,140,255,0.18),_transparent_30%),linear-gradient(180deg,_rgba(255,255,255,1)_0%,_rgba(248,251,255,1)_100%)] dark:bg-[linear-gradient(180deg,_rgba(15,23,42,0.96)_0%,_rgba(2,8,23,1)_100%)] p-6 md:p-7 mb-8">
                        <div className="flex flex-col md:flex-row md:items-start justify-between gap-6">
                            <div>
                                <Logo imageClassName="h-10 w-auto text-brand-600 mb-2" textClassName="text-xl font-bold dark:text-white" />
                                <p className="text-sm text-gray-500 dark:text-gray-400">{__('Computiq Academy')}</p>
                                <div className={`mt-4 inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold ${statusMeta.badgeClassName}`}>
                                    <StatusIcon className={`w-3.5 h-3.5 ${normalizedStatus === 'processing' ? 'animate-spin' : ''}`} />
                                    {translatedStatus}
                                </div>
                            </div>
                            <div className="text-left md:text-right">
                                <h2 className="text-3xl font-black text-gray-900 dark:text-white tracking-tight rtl:text-right">
                                    {__('Invoice')}
                                </h2>
                                <p className="mt-2 inline-flex rounded-full border border-brand-200/80 bg-white/90 px-3 py-1.5 text-sm font-bold text-brand-700 shadow-sm dark:border-brand-900/70 dark:bg-slate-900 dark:text-brand-300" dir="ltr">
                                    #{orderReference}
                                </p>
                                <p className="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    {formatDate(true)}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Metadata Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                        <div className="text-start rtl:text-right rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{__('Reference')}</p>
                            <p className="text-sm font-bold text-gray-900 dark:text-white font-mono" dir="ltr">
                                #{orderReference}
                            </p>
                        </div>
                        <div className="text-start rtl:text-right rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{__('Date')}</p>
                            <p className="text-sm font-medium text-gray-900 dark:text-white">
                                {formatDate()}
                            </p>
                        </div>
                        <div className="text-start rtl:text-right rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{__('Payment Method')}</p>
                            <p className={`text-sm font-medium text-gray-900 dark:text-white ${!isRtl ? 'capitalize' : ''}`}>
                                {paymentMethodName}
                            </p>
                        </div>
                        <div className="text-start rtl:text-right rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{__('Transaction ID')}</p>
                            <p className="text-sm font-medium text-gray-900 dark:text-white font-mono break-all" dir="ltr">
                                {transactionId}
                            </p>
                        </div>
                    </div>

                    {/* Items Table */}
                    <div className="mb-8 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700/60">
                        <table className="w-full text-start border-collapse">
                            <thead>
                                <tr className="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700/60">
                                    <th className="py-3 px-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-start">{__('Description')}</th>
                                    <th className="py-3 px-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider rtl:text-left ltr:text-right">{__('Amount')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                {Array.isArray(order.items) ? order.items.map((i: any, idx: number) => {
                                    const rawTitle = i.course?.title || i.title;
                                    let titleStr = '';
                                    if (typeof rawTitle === 'string') titleStr = rawTitle;
                                    else titleStr = rawTitle[language] || rawTitle['en'] || rawTitle['ar'] || Object.values(rawTitle)[0] || '';
                                    return (
                                        <tr key={idx} className="bg-white dark:bg-gray-900">
                                            <td className="py-4 px-4 text-sm font-medium text-gray-900 dark:text-white">
                                                {titleStr || __('N/A')}
                                            </td>
                                            <td className="py-4 px-4 text-sm font-bold text-gray-900 dark:text-white rtl:text-left ltr:text-right whitespace-nowrap">
                                                {formatAmount(i.total_price || i.unit_price || i.amount || 0)}
                                            </td>
                                        </tr>
                                    );
                                }) : (
                                    <tr><td colSpan={2} className="py-4 px-4 text-center text-gray-400">{__('No items')}</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Totals */}
                    <div className="flex justify-end pt-4">
                        <div className="w-full max-w-sm space-y-3 relative">
                            <div className="flex justify-between items-center text-sm">
                                <span className="text-gray-500 dark:text-gray-400 font-medium">{__('Subtotal')}</span>
                                <span className="text-gray-900 dark:text-white font-semibold">{formatAmount(order.total_amount)}</span>
                            </div>
                            <div className="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700/60">
                                <span className="text-base font-bold text-gray-900 dark:text-white">{__('Total')}</span>
                                <span className="text-xl font-black text-brand-600 dark:text-brand-400" dir="ltr">{formatAmount(order.total_amount)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default OrderInvoiceModal;
