import { useLanguage } from '@/hooks/use-language';
import type {
    PaymentData,
    PaymentMethod,
    PaymentResult,
    ReceiptData,
} from '@/types/pos';
import axios from 'axios';
import { useCallback, useState } from 'react';
import { toast } from 'sonner';

export type { ReceiptData };

interface UsePaymentProcessingReturn {
    isProcessing: boolean;
    processPayment: (data: PaymentData) => Promise<PaymentResult | null>;
    processQuickPayment: (
        storeId: number,
        method: PaymentMethod,
        amount: number,
    ) => Promise<PaymentResult | null>;
    cancelPayment: () => void;
}

/**
 * Custom hook for payment processing
 *
 * Features:
 * - Process payments with different methods (cash, card, transfer)
 * - Quick payment for exact amounts
 * - Loading states
 * - Error handling with toast notifications
 * - Payment cancellation
 *
 * @example
 * const { processPayment, isProcessing } = usePaymentProcessing({
 *   onSuccess: (result) => {
 *     showReceipt(result.receipt);
 *   },
 *   onError: (error) => {
 *     console.error(error);
 *   },
 * });
 */
export function usePaymentProcessing({
    onSuccess,
    onError,
}: {
    onSuccess?: (result: PaymentResult) => void;
    onError?: (error: unknown) => void;
} = {}): UsePaymentProcessingReturn {
    const { __ } = useLanguage();
    const [isProcessing, setIsProcessing] = useState(false);

    const processPayment = useCallback(
        async (data: PaymentData): Promise<PaymentResult | null> => {
            setIsProcessing(true);

            try {
                const response = await axios.post<{ data: PaymentResult }>(
                    '/pos/payments',
                    data,
                );

                const result = response.data.data;

                // Show success notification
                toast.success(__('Payment successful!'), {
                    description: `${__('Sale')}: ${result.sale_reference}`,
                    duration: 3000,
                });

                // Call success callback
                onSuccess?.(result);

                return result;
            } catch (error: unknown) {
                console.error('Payment failed:', error);

                // Extract error message
                const errorMessage =
                    (
                        error as {
                            response?: { data?: { message?: string } };
                            message?: string;
                        }
                    ).response?.data?.message ||
                    (error as { message?: string }).message ||
                    __('Payment failed');

                // Show error notification
                toast.error(__('Payment failed'), {
                    description: errorMessage,
                });

                // Call error callback
                onError?.(error);

                return null;
            } finally {
                setIsProcessing(false);
            }
        },
        [__, onSuccess, onError],
    );

    const processQuickPayment = useCallback(
        async (
            storeId: number,
            method: PaymentMethod,
            amount: number,
        ): Promise<PaymentResult | null> => {
            return processPayment({
                store_id: storeId,
                method,
                amount,
            });
        },
        [processPayment],
    );

    const cancelPayment = useCallback(() => {
        setIsProcessing(false);
        toast.info(__('Payment cancelled'), {
            duration: 2000,
        });
    }, [__]);

    return {
        isProcessing,
        processPayment,
        processQuickPayment,
        cancelPayment,
    };
}

/**
 * Custom hook for payment validation
 *
 * Features:
 * - Validate payment amounts
 * - Calculate change due
 * - Check minimum/maximum amounts
 * - Validate payment methods
 *
 * @example
 * const { isValid, errors, changeDue } = usePaymentValidation({
 *   totalAmount: 10000, // in cents
 *   amountReceived: 15000,
 *   method: 'cash',
 * });
 */
interface PaymentValidationOptions {
    totalAmount: number;
    amountReceived: number;
    method: PaymentMethod;
}

interface PaymentValidationResult {
    isValid: boolean;
    errors: string[];
    changeDue: number;
    isExactAmount: boolean;
}

export function usePaymentValidation({
    totalAmount,
    amountReceived,
    method,
}: PaymentValidationOptions): PaymentValidationResult {
    const { __ } = useLanguage();

    const errors: string[] = [];

    // Validate amount received
    if (amountReceived <= 0) {
        errors.push(__('Amount must be greater than zero'));
    }

    // For cash, amount must be >= total
    if (method === 'cash' && amountReceived < totalAmount) {
        errors.push(
            __('Amount received must be equal to or greater than total'),
        );
    }

    // For card/transfer, amount must equal total (no change)
    if (
        (method === 'card' || method === 'transfer') &&
        amountReceived !== totalAmount
    ) {
        errors.push(__('Amount must equal total for card/transfer payments'));
    }

    // Calculate change due
    const changeDue = Math.max(0, amountReceived - totalAmount);

    return {
        isValid: errors.length === 0,
        errors,
        changeDue,
        isExactAmount: amountReceived === totalAmount,
    };
}

/**
 * Custom hook for split payments
 *
 * Allows splitting payment across multiple methods
 *
 * @example
 * const { addPayment, payments, totalPaid, remainingAmount } = useSplitPayments({
 *   totalAmount: 10000,
 * });
 *
 * addPayment({ method: 'cash', amount: 5000 });
 * addPayment({ method: 'card', amount: 5000 });
 */
interface SplitPayment {
    method: PaymentMethod;
    amount: number;
}

interface UseSplitPaymentsOptions {
    totalAmount: number;
}

interface UseSplitPaymentsReturn {
    payments: SplitPayment[];
    addPayment: (payment: SplitPayment) => void;
    removePayment: (index: number) => void;
    clearPayments: () => void;
    totalPaid: number;
    remainingAmount: number;
    isFullyPaid: boolean;
    canComplete: boolean;
}

export function useSplitPayments({
    totalAmount,
}: UseSplitPaymentsOptions): UseSplitPaymentsReturn {
    const [payments, setPayments] = useState<SplitPayment[]>([]);

    const addPayment = useCallback((payment: SplitPayment) => {
        setPayments((prev) => [...prev, payment]);
    }, []);

    const removePayment = useCallback((index: number) => {
        setPayments((prev) => prev.filter((_, i) => i !== index));
    }, []);

    const clearPayments = useCallback(() => {
        setPayments([]);
    }, []);

    const totalPaid = payments.reduce(
        (sum, payment) => sum + payment.amount,
        0,
    );
    const remainingAmount = Math.max(0, totalAmount - totalPaid);
    const isFullyPaid = totalPaid >= totalAmount;
    const canComplete = isFullyPaid;

    return {
        payments,
        addPayment,
        removePayment,
        clearPayments,
        totalPaid,
        remainingAmount,
        isFullyPaid,
        canComplete,
    };
}
