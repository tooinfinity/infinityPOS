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
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useLanguage } from '@/hooks/use-language';
import { cn } from '@/lib/utils';
import axios from 'axios';
import { AlertCircle, DollarSign, Loader2, Percent } from 'lucide-react';
import { useState } from 'react';

interface DiscountModalProps {
    isOpen: boolean;
    onClose: () => void;
    currentSubtotal: number;
    currentDiscount: number;
    maxDiscountPercentage: number;
    onDiscountApplied: () => void;
}

type DiscountType = 'percentage' | 'fixed';

export function DiscountModal({
    isOpen,
    onClose,
    currentSubtotal,
    currentDiscount,
    maxDiscountPercentage,
    onDiscountApplied,
}: DiscountModalProps) {
    const { __ } = useLanguage();
    const [discountType, setDiscountType] =
        useState<DiscountType>('percentage');
    const [discountValue, setDiscountValue] = useState<string>('');
    const [isProcessing, setIsProcessing] = useState(false);
    const [error, setError] = useState<string>('');

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    // Calculate discount amount
    const calculateDiscountAmount = (): number => {
        const value = parseFloat(discountValue || '0');
        if (isNaN(value) || value <= 0) return 0;

        if (discountType === 'percentage') {
            return Math.round((currentSubtotal * value) / 100);
        } else {
            // Fixed amount (convert to cents)
            return Math.round(value * 100);
        }
    };

    const discountAmount = calculateDiscountAmount();
    const newTotal = Math.max(0, currentSubtotal - discountAmount);

    // Validation
    const validate = (): boolean => {
        const value = parseFloat(discountValue || '0');

        if (isNaN(value) || value <= 0) {
            setError(__('Please enter a valid discount amount'));
            return false;
        }

        if (discountType === 'percentage') {
            if (value > maxDiscountPercentage) {
                setError(
                    `${__('Discount cannot exceed')} ${maxDiscountPercentage}%`,
                );
                return false;
            }
            if (value > 100) {
                setError(__('Percentage cannot exceed 100%'));
                return false;
            }
        } else {
            // Fixed amount
            const maxFixedDiscount = currentSubtotal / 100; // Convert to dollars
            if (value > maxFixedDiscount) {
                setError(
                    `${__('Discount cannot exceed subtotal')} (${formatPrice(currentSubtotal)})`,
                );
                return false;
            }
        }

        setError('');
        return true;
    };

    const handleApply = async () => {
        if (!validate()) return;

        setIsProcessing(true);

        try {
            await axios.put('/pos/cart/discount', {
                type: discountType,
                amount: discountAmount,
            });

            onDiscountApplied();
            onClose();

            // Reset form
            setDiscountValue('');
            setDiscountType('percentage');
            setError('');
        } catch (error: unknown) {
            console.error('Failed to apply discount:', error);
            const apiError = error as {
                response?: { data?: { message?: string } };
            };
            setError(
                apiError.response?.data?.message ||
                    __('Failed to apply discount. Please try again.'),
            );
        } finally {
            setIsProcessing(false);
        }
    };

    const handleRemoveDiscount = async () => {
        setIsProcessing(true);

        try {
            await axios.put('/pos/cart/discount', {
                type: 'fixed',
                amount: 0,
            });

            onDiscountApplied();
            onClose();

            setDiscountValue('');
            setError('');
        } catch (error: unknown) {
            console.error('Failed to remove discount:', error);
            const apiError = error as {
                response?: { data?: { message?: string } };
            };
            setError(
                apiError.response?.data?.message ||
                    __('Failed to remove discount. Please try again.'),
            );
        } finally {
            setIsProcessing(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-xl font-semibold">
                        {__('Apply Discount')}
                    </DialogTitle>
                    <DialogDescription>
                        {__('Add a discount to the current sale')}
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-6 py-4">
                    {/* Current Subtotal */}
                    <div className="rounded-lg bg-muted/50 p-4">
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-muted-foreground">
                                {__('Current Subtotal')}
                            </span>
                            <span className="text-lg font-semibold">
                                {formatPrice(currentSubtotal)}
                            </span>
                        </div>
                        {currentDiscount > 0 && (
                            <div className="mt-2 flex items-center justify-between text-destructive">
                                <span className="text-sm">
                                    {__('Current Discount')}
                                </span>
                                <span className="text-sm font-medium">
                                    -{formatPrice(currentDiscount)}
                                </span>
                            </div>
                        )}
                    </div>

                    {/* Discount Type Selection */}
                    <div className="space-y-3">
                        <Label>{__('Discount Type')}</Label>
                        <RadioGroup
                            value={discountType}
                            onValueChange={(value) => {
                                setDiscountType(value as DiscountType);
                                setError('');
                            }}
                            className="grid grid-cols-2 gap-3"
                        >
                            <label
                                htmlFor="percentage"
                                className={cn(
                                    'flex cursor-pointer items-center gap-3 rounded-lg border-2 p-4 transition-all',
                                    'hover:border-primary/50 hover:bg-primary/5',
                                    discountType === 'percentage'
                                        ? 'border-primary bg-primary/10'
                                        : 'border-border',
                                )}
                            >
                                <RadioGroupItem
                                    value="percentage"
                                    id="percentage"
                                />
                                <div className="flex items-center gap-2">
                                    <Percent className="h-4 w-4" />
                                    <span className="text-sm font-medium">
                                        {__('Percentage')}
                                    </span>
                                </div>
                            </label>

                            <label
                                htmlFor="fixed"
                                className={cn(
                                    'flex cursor-pointer items-center gap-3 rounded-lg border-2 p-4 transition-all',
                                    'hover:border-primary/50 hover:bg-primary/5',
                                    discountType === 'fixed'
                                        ? 'border-primary bg-primary/10'
                                        : 'border-border',
                                )}
                            >
                                <RadioGroupItem value="fixed" id="fixed" />
                                <div className="flex items-center gap-2">
                                    <DollarSign className="h-4 w-4" />
                                    <span className="text-sm font-medium">
                                        {__('Fixed Amount')}
                                    </span>
                                </div>
                            </label>
                        </RadioGroup>
                    </div>

                    {/* Discount Value Input */}
                    <div className="space-y-2">
                        <Label htmlFor="discount-value">
                            {discountType === 'percentage'
                                ? __('Discount Percentage')
                                : __('Discount Amount')}
                        </Label>
                        <div className="relative">
                            <Input
                                id="discount-value"
                                type="number"
                                step={
                                    discountType === 'percentage' ? '1' : '0.01'
                                }
                                min="0"
                                max={
                                    discountType === 'percentage'
                                        ? maxDiscountPercentage
                                        : currentSubtotal / 100
                                }
                                value={discountValue}
                                onChange={(e) => {
                                    setDiscountValue(e.target.value);
                                    setError('');
                                }}
                                placeholder={
                                    discountType === 'percentage'
                                        ? '10'
                                        : '0.00'
                                }
                                className="pr-10 text-lg"
                                autoFocus
                            />
                            <div className="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground">
                                {discountType === 'percentage' ? '%' : '$'}
                            </div>
                        </div>

                        {/* Max discount hint */}
                        <p className="text-xs text-muted-foreground">
                            {discountType === 'percentage'
                                ? `${__('Maximum')}: ${maxDiscountPercentage}%`
                                : `${__('Maximum')}: ${formatPrice(currentSubtotal)}`}
                        </p>

                        {/* Error message */}
                        {error && (
                            <div className="flex items-center gap-2 text-sm text-destructive">
                                <AlertCircle className="h-4 w-4" />
                                <span>{error}</span>
                            </div>
                        )}
                    </div>

                    {/* Preview */}
                    {discountAmount > 0 && !error && (
                        <div className="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-4">
                            <div className="space-y-2 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-emerald-700 dark:text-emerald-400">
                                        {__('Discount Amount')}
                                    </span>
                                    <span className="font-semibold text-emerald-700 dark:text-emerald-400">
                                        -{formatPrice(discountAmount)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between border-t border-emerald-500/20 pt-2">
                                    <span className="font-medium text-emerald-700 dark:text-emerald-400">
                                        {__('New Total')}
                                    </span>
                                    <span className="text-lg font-bold text-emerald-700 dark:text-emerald-400">
                                        {formatPrice(newTotal)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                    {currentDiscount > 0 && (
                        <Button
                            variant="outline"
                            onClick={handleRemoveDiscount}
                            disabled={isProcessing}
                            className="flex-1"
                        >
                            {__('Remove Discount')}
                        </Button>
                    )}
                    <Button
                        variant="outline"
                        onClick={onClose}
                        disabled={isProcessing}
                        className="flex-1"
                    >
                        {__('Cancel')}
                    </Button>
                    <Button
                        onClick={handleApply}
                        disabled={isProcessing || !!error || !discountValue}
                        className="flex-1 gap-2"
                    >
                        {isProcessing ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {__('Applying...')}
                            </>
                        ) : (
                            __('Apply Discount')
                        )}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
