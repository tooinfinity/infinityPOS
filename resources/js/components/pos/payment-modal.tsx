import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useLanguage } from '@/hooks/use-language';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import axios from 'axios';
import {
    ArrowLeftRight,
    Banknote,
    Check,
    CreditCard,
    Loader2,
} from 'lucide-react';
import { useEffect, useState } from 'react';

interface PaymentModalProps {
    isOpen: boolean;
    onClose: () => void;
    totalAmount: number;
    storeId: number;
    defaultPaymentMethod?: string;
    onSuccess?: () => void;
}

type PaymentMethod = 'cash' | 'card' | 'transfer';

const paymentMethods = [
    {
        value: 'cash' as PaymentMethod,
        label: 'Cash',
        icon: Banknote,
        description: 'Physical cash payment',
    },
    {
        value: 'card' as PaymentMethod,
        label: 'Card',
        icon: CreditCard,
        description: 'Credit or debit card',
    },
    {
        value: 'transfer' as PaymentMethod,
        label: 'Transfer',
        icon: ArrowLeftRight,
        description: 'Bank transfer',
    },
];

export function PaymentModal({
    isOpen,
    onClose,
    totalAmount,
    storeId,
    defaultPaymentMethod = 'cash',
    onSuccess,
}: PaymentModalProps) {
    const { __ } = useLanguage();
    const [selectedMethod, setSelectedMethod] = useState<PaymentMethod>(
        defaultPaymentMethod as PaymentMethod,
    );
    const [amountReceived, setAmountReceived] = useState<string>('');
    const [isProcessing, setIsProcessing] = useState(false);

    // Auto-populate amount when modal opens
    useEffect(() => {
        if (isOpen) {
            setAmountReceived((totalAmount / 100).toFixed(2));
            setSelectedMethod(defaultPaymentMethod as PaymentMethod);
        }
    }, [isOpen, totalAmount, defaultPaymentMethod]);

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const receivedAmountCents = Math.round(
        parseFloat(amountReceived || '0') * 100,
    );
    const changeDue = Math.max(0, receivedAmountCents - totalAmount);
    const isValidAmount = receivedAmountCents >= totalAmount;

    const handlePayment = async () => {
        if (!isValidAmount) return;

        setIsProcessing(true);

        try {
            const response = await axios.post('/pos/payments', {
                store_id: storeId,
                method: selectedMethod,
                amount: receivedAmountCents,
            });

            // Show success message
            const saleReference = response.data.data.sale_reference;

            // Close modal and notify parent
            onClose();
            if (onSuccess) {
                onSuccess();
            }

            // Show success message
            alert(
                `${__('Payment successful!')} \n${__('Sale')}: ${saleReference}`,
            );

            // Refresh the page to clear cart
            router.reload();
        } catch (error: unknown) {
            console.error('Payment failed:', error);
            const errorMessage =
                error instanceof Error && 'response' in error
                    ? (error as { response?: { data?: { message?: string } } })
                          .response?.data?.message
                    : undefined;
            alert(errorMessage || __('Payment failed. Please try again.'));
        } finally {
            setIsProcessing(false);
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && isValidAmount && !isProcessing) {
            handlePayment();
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-xl font-semibold">
                        {__('Complete Payment')}
                    </DialogTitle>
                    <DialogDescription>
                        {__('Select payment method and confirm amount')}
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-6 py-4">
                    {/* Total Amount Display */}
                    <div className="rounded-lg bg-muted/50 p-4">
                        <div className="text-sm text-muted-foreground">
                            {__('Total Amount')}
                        </div>
                        <div className="text-3xl font-bold text-primary">
                            {formatPrice(totalAmount)}
                        </div>
                    </div>

                    {/* Payment Method Selection */}
                    <div className="space-y-3">
                        <Label className="text-sm font-medium">
                            {__('Payment Method')}
                        </Label>
                        <div className="grid grid-cols-3 gap-3">
                            {paymentMethods.map((method) => {
                                const Icon = method.icon;
                                const isSelected =
                                    selectedMethod === method.value;

                                return (
                                    <button
                                        key={method.value}
                                        onClick={() =>
                                            setSelectedMethod(method.value)
                                        }
                                        className={cn(
                                            'flex flex-col items-center gap-2 rounded-lg border-2 p-4 transition-all',
                                            'hover:border-primary/50 hover:bg-primary/5',
                                            isSelected
                                                ? 'border-primary bg-primary/10'
                                                : 'border-border',
                                        )}
                                    >
                                        <Icon
                                            className={cn(
                                                'h-6 w-6',
                                                isSelected
                                                    ? 'text-primary'
                                                    : 'text-muted-foreground',
                                            )}
                                        />
                                        <span
                                            className={cn(
                                                'text-xs font-medium',
                                                isSelected
                                                    ? 'text-primary'
                                                    : 'text-muted-foreground',
                                            )}
                                        >
                                            {__(method.label)}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Amount Received (for cash) */}
                    {selectedMethod === 'cash' && (
                        <div className="space-y-2">
                            <Label htmlFor="amount-received">
                                {__('Amount Received')}
                            </Label>
                            <Input
                                id="amount-received"
                                type="number"
                                step="0.01"
                                min="0"
                                value={amountReceived}
                                onChange={(e) =>
                                    setAmountReceived(e.target.value)
                                }
                                onKeyDown={handleKeyDown}
                                placeholder="0.00"
                                className="text-lg"
                                autoFocus
                            />

                            {/* Change Due */}
                            {isValidAmount && changeDue > 0 && (
                                <div className="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-emerald-700 dark:text-emerald-400">
                                            {__('Change Due')}
                                        </span>
                                        <span className="text-lg font-bold text-emerald-700 dark:text-emerald-400">
                                            {formatPrice(changeDue)}
                                        </span>
                                    </div>
                                </div>
                            )}

                            {/* Insufficient Amount Warning */}
                            {!isValidAmount && amountReceived !== '' && (
                                <p className="text-sm text-destructive">
                                    {__(
                                        'Amount received must be equal to or greater than total',
                                    )}
                                </p>
                            )}
                        </div>
                    )}
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        disabled={isProcessing}
                        className="flex-1"
                    >
                        {__('Cancel')}
                    </Button>
                    <Button
                        onClick={handlePayment}
                        disabled={!isValidAmount || isProcessing}
                        className="flex-1 gap-2"
                    >
                        {isProcessing ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {__('Processing...')}
                            </>
                        ) : (
                            <>
                                <Check className="h-4 w-4" />
                                {__('Complete Payment')}
                            </>
                        )}
                    </Button>
                </div>

                {/* Quick Amount Buttons for Cash */}
                {selectedMethod === 'cash' && (
                    <div className="border-t pt-4">
                        <Label className="mb-2 block text-xs text-muted-foreground">
                            {__('Quick amounts')}
                        </Label>
                        <div className="grid grid-cols-4 gap-2">
                            {[20, 50, 100, 200].map((amount) => (
                                <Button
                                    key={amount}
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        setAmountReceived(amount.toFixed(2))
                                    }
                                    disabled={isProcessing}
                                    className="text-xs"
                                >
                                    ${amount}
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
