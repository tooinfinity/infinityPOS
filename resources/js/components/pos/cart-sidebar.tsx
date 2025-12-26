import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useLanguage } from '@/hooks/use-language';
import { cn } from '@/lib/utils';
import {
    DollarSign,
    Minus,
    Percent,
    Plus,
    Receipt,
    ShoppingCart,
    Trash2,
    X,
} from 'lucide-react';
import { useCallback, useState } from 'react';

interface CartItem {
    line_id: string;
    product_id: number;
    name: string;
    unit_price: number;
    quantity: number;
    line_subtotal: number;
}

interface CartTotals {
    subtotal: number;
    discount_total: number;
    tax_total: number;
    total: number;
}

interface CartSidebarProps {
    items: CartItem[];
    totals: CartTotals;
    itemCount: number;
    onUpdateQuantity: (lineId: string, quantity: number) => void;
    onRemoveItem: (lineId: string) => void;
    onClearCart: () => void;
    onPayNow: () => void;
    onQuickPayExact?: () => void;
    enableDiscounts: boolean;
    maxDiscountPercentage: number;
    onApplyDiscount: (discountAmount: number) => void;
    onApplyTax: (taxAmount: number) => void;
}

export function CartSidebar({
    items,
    totals,
    itemCount,
    onUpdateQuantity,
    onRemoveItem,
    onClearCart,
    onPayNow,
    onQuickPayExact,
    enableDiscounts,
    maxDiscountPercentage,
    onApplyDiscount,
    onApplyTax,
}: CartSidebarProps) {
    const { __ } = useLanguage();
    const [discountInput, setDiscountInput] = useState<string>('');
    const [discountType, setDiscountType] = useState<'percentage' | 'fixed'>(
        'fixed',
    );
    const [taxInput, setTaxInput] = useState<string>('');
    const [taxType, setTaxType] = useState<'percentage' | 'fixed'>(
        'percentage',
    );

    const formatPrice = useCallback((amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    }, []);

    const handleDiscountChange = useCallback(
        (value: string) => {
            setDiscountInput(value);
            const numValue = parseFloat(value);

            if (!value || isNaN(numValue) || numValue < 0) {
                onApplyDiscount(0);
                return;
            }

            let discountCents: number;

            if (discountType === 'percentage') {
                // Calculate discount from percentage
                const percentage = Math.min(numValue, maxDiscountPercentage); // Cap at max %
                discountCents = Math.floor(
                    (totals.subtotal * percentage) / 100,
                );
            } else {
                // Fixed amount - convert dollars to cents
                discountCents = Math.round(numValue * 100);
            }

            // Calculate max discount based on percentage
            const maxDiscount = Math.floor(
                (totals.subtotal * maxDiscountPercentage) / 100,
            );

            // Apply the discount, capped at max allowed and subtotal
            const finalDiscount = Math.min(
                discountCents,
                maxDiscount,
                totals.subtotal,
            );
            onApplyDiscount(finalDiscount);
        },
        [onApplyDiscount, maxDiscountPercentage, totals.subtotal, discountType],
    );

    const toggleDiscountType = useCallback(() => {
        setDiscountType((prev) => (prev === 'fixed' ? 'percentage' : 'fixed'));
        setDiscountInput('');
        onApplyDiscount(0);
    }, [onApplyDiscount]);

    const handleTaxChange = useCallback(
        (value: string) => {
            setTaxInput(value);
            const numValue = parseFloat(value);

            if (!value || isNaN(numValue) || numValue < 0) {
                onApplyTax(0);
                return;
            }

            let taxCents: number;

            if (taxType === 'percentage') {
                // Calculate tax from percentage of taxable amount (subtotal - discount)
                const taxableAmount = totals.subtotal - totals.discount_total;
                taxCents = Math.floor((taxableAmount * numValue) / 100);
            } else {
                // Fixed amount - convert dollars to cents
                taxCents = Math.round(numValue * 100);
            }

            onApplyTax(taxCents);
        },
        [onApplyTax, taxType, totals.subtotal, totals.discount_total],
    );

    const toggleTaxType = useCallback(() => {
        setTaxType((prev) => (prev === 'fixed' ? 'percentage' : 'fixed'));
        setTaxInput('');
    }, []);

    const isEmpty = items.length === 0;

    // Clear discount and tax inputs when cart is empty
    const handleClearCart = useCallback(() => {
        setDiscountInput('');
        setTaxInput('');
        onClearCart();
    }, [onClearCart]);

    return (
        <div className="flex h-full flex-col border-l border-border/50 bg-background">
            {/* Header */}
            <div className="sticky top-0 z-10 border-b border-border/50 bg-gradient-to-r from-primary/5 via-primary/3 to-transparent backdrop-blur-sm">
                <div className="p-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10">
                                <ShoppingCart className="h-4.5 w-4.5 text-primary" />
                            </div>
                            <div>
                                <h2 className="text-sm leading-tight font-bold">
                                    {__('Current Sale')}
                                </h2>
                                <p className="text-xs text-muted-foreground">
                                    {itemCount}{' '}
                                    {itemCount === 1 ? 'item' : 'items'}
                                </p>
                            </div>
                        </div>
                        {!isEmpty && (
                            <Button
                                onClick={handleClearCart}
                                variant="ghost"
                                size="sm"
                                className="h-8 gap-1 px-2 text-xs text-muted-foreground hover:text-destructive"
                            >
                                <Trash2 className="h-3.5 w-3.5" />
                                {__('Clear')}
                            </Button>
                        )}
                    </div>
                </div>
            </div>

            {/* Cart Items */}
            <div className="flex-1 overflow-y-auto">
                {isEmpty ? (
                    <div className="flex h-full flex-col items-center justify-center p-6 text-center">
                        <div className="relative">
                            <div className="absolute inset-0 animate-pulse rounded-full bg-primary/5 blur-xl" />
                            <div className="relative rounded-xl bg-muted/50 p-6">
                                <ShoppingCart className="h-12 w-12 text-muted-foreground/40" />
                            </div>
                        </div>
                        <p className="mt-4 text-sm font-semibold text-foreground/80">
                            {__('Cart is empty')}
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {__('Add products to start a sale')}
                        </p>
                    </div>
                ) : (
                    <div className="space-y-2 p-2.5">
                        {items.map((item, index) => (
                            <div
                                key={item.line_id}
                                className="group relative rounded-lg border border-border/50 bg-card/50 p-2.5 transition-all hover:border-primary/40 hover:bg-card hover:shadow-sm"
                                style={{
                                    animationDelay: `${index * 50}ms`,
                                }}
                            >
                                <button
                                    onClick={() => onRemoveItem(item.line_id)}
                                    className="absolute -top-1 -right-1 z-10 flex h-5.5 w-5.5 items-center justify-center rounded-full border border-border bg-background opacity-0 shadow-sm transition-all group-hover:opacity-100 hover:border-destructive hover:bg-destructive hover:text-destructive-foreground"
                                    title={__('Remove item')}
                                >
                                    <X className="h-3 w-3" />
                                </button>

                                <div className="pr-6">
                                    <h4 className="line-clamp-2 text-sm leading-tight font-semibold">
                                        {item.name}
                                    </h4>
                                    <div className="mt-0.5 flex items-baseline gap-1">
                                        <span className="text-xs font-medium text-muted-foreground">
                                            {formatPrice(item.unit_price)}
                                        </span>
                                        <span className="text-[10px] text-muted-foreground/60">
                                            each
                                        </span>
                                    </div>
                                </div>

                                <div className="mt-2.5 flex items-center justify-between gap-2">
                                    <div className="flex items-center rounded border border-border/60 bg-background/50">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            className="h-7 w-7 rounded-r-none hover:bg-primary/10 hover:text-primary"
                                            onClick={() =>
                                                onUpdateQuantity(
                                                    item.line_id,
                                                    Math.max(
                                                        1,
                                                        item.quantity - 1,
                                                    ),
                                                )
                                            }
                                        >
                                            <Minus className="h-3.5 w-3.5" />
                                        </Button>

                                        <div className="flex h-7 w-10 items-center justify-center border-x border-border/60 bg-muted/30">
                                            <span className="text-sm font-bold">
                                                {item.quantity}
                                            </span>
                                        </div>

                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            className="h-7 w-7 rounded-l-none hover:bg-primary/10 hover:text-primary"
                                            onClick={() =>
                                                onUpdateQuantity(
                                                    item.line_id,
                                                    item.quantity + 1,
                                                )
                                            }
                                        >
                                            <Plus className="h-3.5 w-3.5" />
                                        </Button>
                                    </div>

                                    <div className="flex-1 text-right">
                                        <div className="inline-flex items-center justify-center rounded bg-primary/10 px-2 py-1">
                                            <span className="text-sm font-bold text-primary">
                                                {formatPrice(
                                                    item.line_subtotal,
                                                )}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Totals Section */}
            {!isEmpty && (
                <div className="border-t border-border/50 bg-muted/30 p-3">
                    <div className="space-y-2.5">
                        {/* Subtotal */}
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium text-muted-foreground">
                                {__('Subtotal')}
                            </span>
                            <span className="font-semibold">
                                {formatPrice(totals.subtotal)}
                            </span>
                        </div>

                        {/* Tax Input */}
                        <div className="space-y-1.5 rounded-lg border border-border/60 bg-background p-2">
                            <div className="flex items-center justify-between">
                                <Label
                                    htmlFor="tax-input"
                                    className="text-xs font-medium text-muted-foreground"
                                >
                                    {__('Tax')}
                                </Label>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={toggleTaxType}
                                    disabled={isEmpty}
                                    className="h-6 gap-1 px-2 text-[10px] font-medium"
                                    title={__(
                                        taxType === 'fixed'
                                            ? 'Switch to percentage'
                                            : 'Switch to fixed amount',
                                    )}
                                >
                                    {taxType === 'fixed' ? (
                                        <>
                                            <DollarSign className="h-2.5 w-2.5" />
                                            $
                                        </>
                                    ) : (
                                        <>
                                            <Percent className="h-2.5 w-2.5" />%
                                        </>
                                    )}
                                </Button>
                            </div>
                            <div className="relative">
                                <span className="absolute top-1/2 left-2.5 -translate-y-1/2 text-sm font-semibold text-muted-foreground">
                                    {taxType === 'fixed' ? '$' : '%'}
                                </span>
                                <Input
                                    id="tax-input"
                                    type="number"
                                    min="0"
                                    step={taxType === 'fixed' ? '0.01' : '0.1'}
                                    placeholder={
                                        taxType === 'fixed'
                                            ? formatPrice(
                                                  totals.tax_total,
                                              ).replace('$', '')
                                            : '0.00'
                                    }
                                    value={taxInput}
                                    onChange={(e) =>
                                        handleTaxChange(e.target.value)
                                    }
                                    disabled={isEmpty}
                                    className="h-8 pr-2.5 pl-7 text-sm font-medium"
                                />
                            </div>
                            {totals.tax_total > 0 && !taxInput && (
                                <div className="flex items-center justify-between rounded bg-blue-50 px-1.5 py-0.5 dark:bg-blue-950/30">
                                    <span className="text-[10px] font-medium text-blue-700 dark:text-blue-400">
                                        {__('Auto')}
                                    </span>
                                    <span className="text-xs font-bold text-blue-700 dark:text-blue-400">
                                        {formatPrice(totals.tax_total)}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Discount Input */}
                        {enableDiscounts && (
                            <div className="space-y-1.5 rounded-lg border border-border/60 bg-background p-2">
                                <div className="flex items-center justify-between">
                                    <Label
                                        htmlFor="discount-input"
                                        className="text-xs font-medium text-muted-foreground"
                                    >
                                        {__('Discount')}{' '}
                                        {maxDiscountPercentage > 0 && (
                                            <span className="text-[10px] text-muted-foreground/70">
                                                (max {maxDiscountPercentage}%)
                                            </span>
                                        )}
                                    </Label>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={toggleDiscountType}
                                        disabled={isEmpty}
                                        className="h-6 gap-1 px-2 text-[10px] font-medium"
                                        title={__(
                                            discountType === 'fixed'
                                                ? 'Switch to percentage'
                                                : 'Switch to fixed amount',
                                        )}
                                    >
                                        {discountType === 'fixed' ? (
                                            <>
                                                <DollarSign className="h-2.5 w-2.5" />
                                                $
                                            </>
                                        ) : (
                                            <>
                                                <Percent className="h-2.5 w-2.5" />
                                                %
                                            </>
                                        )}
                                    </Button>
                                </div>
                                <div className="relative">
                                    <span className="absolute top-1/2 left-2.5 -translate-y-1/2 text-sm font-semibold text-muted-foreground">
                                        {discountType === 'fixed' ? '$' : '%'}
                                    </span>
                                    <Input
                                        id="discount-input"
                                        type="number"
                                        min="0"
                                        step={
                                            discountType === 'fixed'
                                                ? '0.01'
                                                : '0.1'
                                        }
                                        max={
                                            discountType === 'percentage'
                                                ? maxDiscountPercentage
                                                : undefined
                                        }
                                        placeholder={
                                            discountType === 'fixed'
                                                ? '0.00'
                                                : '0.00'
                                        }
                                        value={discountInput}
                                        onChange={(e) =>
                                            handleDiscountChange(e.target.value)
                                        }
                                        disabled={isEmpty}
                                        className="h-8 pr-2.5 pl-7 text-sm font-medium"
                                    />
                                </div>
                                {totals.discount_total > 0 && (
                                    <div className="flex items-center justify-between rounded bg-red-50 px-1.5 py-0.5 dark:bg-red-950/30">
                                        <span className="text-[10px] font-medium text-red-700 dark:text-red-400">
                                            {__('Applied')}
                                        </span>
                                        <span className="text-xs font-bold text-red-700 dark:text-red-400">
                                            -
                                            {formatPrice(totals.discount_total)}
                                        </span>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Total */}
                        <div className="mt-2 rounded-lg border border-primary/30 bg-gradient-to-r from-primary/10 to-primary/5 p-2.5 shadow-sm">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-1.5">
                                    <Receipt className="h-4 w-4 text-primary" />
                                    <span className="text-sm font-bold text-primary">
                                        {__('Total')}
                                    </span>
                                </div>
                                <span className="text-2xl font-bold text-primary">
                                    {formatPrice(totals.total)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Action Buttons */}
            <div className="border-t border-border/50 bg-card/50 p-2.5">
                {/* Quick Pay Exact - shown when onQuickPayExact is provided */}
                {onQuickPayExact && (
                    <Button
                        onClick={onQuickPayExact}
                        disabled={isEmpty}
                        className={cn(
                            'h-11 w-full text-base font-bold shadow-md transition-all hover:scale-[1.02] hover:shadow-lg',
                            'bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600',
                            'disabled:opacity-50 disabled:hover:scale-100',
                        )}
                        size="lg"
                    >
                        <Receipt className="mr-2 h-4 w-4" />
                        {__('Quick Pay')} {formatPrice(totals.total)}
                    </Button>
                )}

                {/* Regular Pay Now button */}
                {!onQuickPayExact && (
                    <Button
                        onClick={onPayNow}
                        disabled={isEmpty}
                        className={cn(
                            'h-11 w-full text-base font-bold shadow-md transition-all hover:scale-[1.02] hover:shadow-lg',
                            'disabled:opacity-50 disabled:hover:scale-100',
                        )}
                        size="lg"
                    >
                        <Receipt className="mr-2 h-4 w-4" />
                        {__('Pay Now')} {formatPrice(totals.total)}
                    </Button>
                )}
            </div>
        </div>
    );
}
