import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useLanguage } from '@/hooks/use-language';
import { Download, Printer, X } from 'lucide-react';
import { useRef } from 'react';

interface ReceiptItem {
    name: string;
    quantity: number;
    unit_price: number;
    line_subtotal: number;
}

interface ReceiptData {
    sale_reference: string;
    date: string;
    items: ReceiptItem[];
    subtotal: number;
    tax_total: number;
    discount_total: number;
    total: number;
    amount_received: number;
    change_due: number;
    payment_method: string;
    customer?: {
        name: string;
        phone?: string;
    } | null;
    store?: {
        name: string;
        address?: string;
        phone?: string;
    } | null;
}

interface ReceiptModalProps {
    isOpen: boolean;
    onClose: () => void;
    receipt: ReceiptData | null;
    autoPrint?: boolean;
}

export function ReceiptModal({ isOpen, onClose, receipt }: ReceiptModalProps) {
    const { __ } = useLanguage();
    const receiptRef = useRef<HTMLDivElement>(null);

    if (!receipt) return null;

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    };

    const handlePrint = () => {
        if (receiptRef.current) {
            const printWindow = window.open('', '_blank');
            if (printWindow) {
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Receipt - ${receipt.sale_reference}</title>
                        <style>
                            @media print {
                                body { margin: 0; padding: 20px; font-family: monospace; }
                                @page { size: 80mm auto; margin: 0; }
                            }
                            body {
                                font-family: 'Courier New', monospace;
                                font-size: 12px;
                                line-height: 1.4;
                                max-width: 300px;
                                margin: 0 auto;
                            }
                            .receipt-header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #000; padding-bottom: 10px; }
                            .receipt-header h1 { margin: 0; font-size: 18px; }
                            .receipt-header p { margin: 2px 0; font-size: 11px; }
                            .receipt-info { margin-bottom: 15px; font-size: 11px; }
                            .receipt-items { margin-bottom: 15px; }
                            .receipt-item { margin-bottom: 8px; }
                            .item-name { font-weight: bold; }
                            .item-details { display: flex; justify-content: space-between; font-size: 11px; }
                            .receipt-totals { border-top: 1px solid #000; padding-top: 10px; margin-bottom: 10px; }
                            .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
                            .total-row.grand-total { font-size: 14px; font-weight: bold; border-top: 2px solid #000; padding-top: 8px; margin-top: 8px; }
                            .receipt-footer { text-align: center; margin-top: 20px; border-top: 2px dashed #000; padding-top: 10px; font-size: 11px; }
                        </style>
                    </head>
                    <body>
                        ${receiptRef.current.innerHTML}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();

                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 250);
            }
        }
    };

    const handleDownload = () => {
        // Create a simple text receipt for download
        const textReceipt = `
${receipt.store?.name || 'Store Name'}
${receipt.store?.address || ''}
${receipt.store?.phone || ''}

Receipt: ${receipt.sale_reference}
Date: ${formatDate(receipt.date)}
${receipt.customer ? `Customer: ${receipt.customer.name}` : ''}

${'='.repeat(40)}
ITEMS
${'='.repeat(40)}

${receipt.items
    .map(
        (item) => `
${item.name}
${item.quantity} x ${formatPrice(item.unit_price)} = ${formatPrice(item.line_subtotal)}
`,
    )
    .join('')}

${'='.repeat(40)}
TOTALS
${'='.repeat(40)}

Subtotal: ${formatPrice(receipt.subtotal)}
${receipt.discount_total > 0 ? `Discount: -${formatPrice(receipt.discount_total)}\n` : ''}
Tax: ${formatPrice(receipt.tax_total)}
${'='.repeat(40)}
TOTAL: ${formatPrice(receipt.total)}
${'='.repeat(40)}

Payment: ${receipt.payment_method.toUpperCase()}
Amount Received: ${formatPrice(receipt.amount_received)}
Change: ${formatPrice(receipt.change_due)}

Thank you for your purchase!
        `.trim();

        const blob = new Blob([textReceipt], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `receipt-${receipt.sale_reference}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle className="text-xl font-semibold">
                        {__('Receipt')}
                    </DialogTitle>
                    <DialogDescription>
                        {__('Sale completed successfully')}
                    </DialogDescription>
                </DialogHeader>

                {/* Receipt Preview */}
                <div className="max-h-[500px] overflow-y-auto py-4">
                    <div
                        ref={receiptRef}
                        className="rounded-lg border bg-white p-6 font-mono text-sm dark:bg-gray-900"
                    >
                        {/* Header */}
                        <div className="mb-4 border-b-2 border-dashed border-gray-400 pb-4 text-center">
                            <h1 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {receipt.store?.name || 'Store Name'}
                            </h1>
                            {receipt.store?.address && (
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    {receipt.store.address}
                                </p>
                            )}
                            {receipt.store?.phone && (
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    {receipt.store.phone}
                                </p>
                            )}
                        </div>

                        {/* Receipt Info */}
                        <div className="mb-4 text-xs text-gray-700 dark:text-gray-300">
                            <p>
                                <strong>{__('Receipt')}:</strong>{' '}
                                {receipt.sale_reference}
                            </p>
                            <p>
                                <strong>{__('Date')}:</strong>{' '}
                                {formatDate(receipt.date)}
                            </p>
                            {receipt.customer && (
                                <p>
                                    <strong>{__('Customer')}:</strong>{' '}
                                    {receipt.customer.name}
                                </p>
                            )}
                        </div>

                        {/* Items */}
                        <div className="mb-4">
                            {receipt.items.map((item, index) => (
                                <div
                                    key={index}
                                    className="mb-3 text-gray-900 dark:text-gray-100"
                                >
                                    <div className="font-semibold">
                                        {item.name}
                                    </div>
                                    <div className="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                        <span>
                                            {item.quantity} x{' '}
                                            {formatPrice(item.unit_price)}
                                        </span>
                                        <span className="font-medium text-gray-900 dark:text-gray-100">
                                            {formatPrice(item.line_subtotal)}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Totals */}
                        <div className="space-y-2 border-t border-gray-400 pt-3 text-gray-900 dark:text-gray-100">
                            <div className="flex justify-between text-sm">
                                <span>{__('Subtotal')}:</span>
                                <span>{formatPrice(receipt.subtotal)}</span>
                            </div>

                            {receipt.discount_total > 0 && (
                                <div className="flex justify-between text-sm text-red-600">
                                    <span>{__('Discount')}:</span>
                                    <span>
                                        -{formatPrice(receipt.discount_total)}
                                    </span>
                                </div>
                            )}

                            {receipt.tax_total > 0 && (
                                <div className="flex justify-between text-sm">
                                    <span>{__('Tax')}:</span>
                                    <span>
                                        {formatPrice(receipt.tax_total)}
                                    </span>
                                </div>
                            )}

                            <div className="mt-2 flex justify-between border-t-2 border-gray-900 pt-2 text-base font-bold dark:border-gray-100">
                                <span>{__('TOTAL')}:</span>
                                <span>{formatPrice(receipt.total)}</span>
                            </div>
                        </div>

                        {/* Payment Info */}
                        <div className="mt-4 space-y-1 border-t border-dashed border-gray-400 pt-3 text-xs text-gray-700 dark:text-gray-300">
                            <div className="flex justify-between">
                                <span>{__('Payment Method')}:</span>
                                <span className="font-medium uppercase">
                                    {receipt.payment_method}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span>{__('Amount Received')}:</span>
                                <span>
                                    {formatPrice(receipt.amount_received)}
                                </span>
                            </div>
                            {receipt.change_due > 0 && (
                                <div className="flex justify-between font-semibold">
                                    <span>{__('Change')}:</span>
                                    <span>
                                        {formatPrice(receipt.change_due)}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Footer */}
                        <div className="mt-6 border-t-2 border-dashed border-gray-400 pt-4 text-center text-xs text-gray-600 dark:text-gray-400">
                            <p>{__('Thank you for your purchase!')}</p>
                            <p className="mt-2">
                                {__('Please visit us again')}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        className="flex-1"
                    >
                        <X className="mr-2 h-4 w-4" />
                        {__('Close')}
                    </Button>
                    <Button
                        variant="outline"
                        onClick={handleDownload}
                        className="flex-1"
                    >
                        <Download className="mr-2 h-4 w-4" />
                        {__('Download')}
                    </Button>
                    <Button onClick={handlePrint} className="flex-1">
                        <Printer className="mr-2 h-4 w-4" />
                        {__('Print')}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
