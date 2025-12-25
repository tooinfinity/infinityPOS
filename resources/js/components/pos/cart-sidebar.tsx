import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import { Minus, Plus, ShoppingCart, Trash2, X } from 'lucide-react';
import { useCallback } from 'react';

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
    onHoldSale: () => void;
    onPayNow: () => void;
    enableDiscounts: boolean;
}

export function CartSidebar({
    items,
    totals,
    itemCount,
    onUpdateQuantity,
    onRemoveItem,
    onClearCart,
    onHoldSale,
    onPayNow,
    enableDiscounts,
}: CartSidebarProps) {
    const { __ } = useLanguage();

    const formatPrice = useCallback((amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    }, []);

    const isEmpty = items.length === 0;

    return (
        <div className="flex h-full flex-col border-l border-border bg-card">
            {/* Header */}
            <div className="border-b border-border p-4">
                <div className="flex items-center justify-between">
                    <h2 className="flex items-center gap-2 text-lg font-semibold">
                        <ShoppingCart className="h-5 w-5" />
                        {__('Current Sale')}
                    </h2>
                    <span className="text-sm text-muted-foreground">
                        {itemCount} {itemCount === 1 ? 'item' : 'items'}
                    </span>
                </div>
            </div>

            {/* Cart Items */}
            <div className="flex-1 overflow-y-auto p-4">
                {isEmpty ? (
                    <div className="flex h-full flex-col items-center justify-center text-center">
                        <ShoppingCart className="mb-4 h-16 w-16 text-muted-foreground/30" />
                        <p className="text-sm text-muted-foreground">
                            {__('Cart is empty')}
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {__('Add products to start a sale')}
                        </p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {items.map((item) => (
                            <div
                                key={item.line_id}
                                className="group relative rounded-lg border border-border bg-card p-3 transition-all hover:shadow-sm"
                            >
                                <button
                                    onClick={() => onRemoveItem(item.line_id)}
                                    className="absolute top-2 right-2 rounded-full p-1 opacity-0 transition-all group-hover:opacity-100 hover:bg-destructive/10 hover:text-destructive"
                                    title={__('Remove item')}
                                >
                                    <X className="h-4 w-4" />
                                </button>

                                <div className="pr-8">
                                    <h4 className="line-clamp-2 text-sm font-medium">
                                        {item.name}
                                    </h4>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {formatPrice(item.unit_price)} each
                                    </p>
                                </div>

                                <div className="mt-3 flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Button
                                            size="icon"
                                            variant="outline"
                                            className="h-7 w-7"
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
                                            <Minus className="h-3 w-3" />
                                        </Button>

                                        <span className="w-8 text-center text-sm font-medium">
                                            {item.quantity}
                                        </span>

                                        <Button
                                            size="icon"
                                            variant="outline"
                                            className="h-7 w-7"
                                            onClick={() =>
                                                onUpdateQuantity(
                                                    item.line_id,
                                                    item.quantity + 1,
                                                )
                                            }
                                        >
                                            <Plus className="h-3 w-3" />
                                        </Button>
                                    </div>

                                    <span className="text-sm font-semibold">
                                        {formatPrice(item.line_subtotal)}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Totals Section */}
            {!isEmpty && (
                <div className="border-t border-border bg-muted/30 p-4">
                    <div className="space-y-2 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                {__('Subtotal')}
                            </span>
                            <span className="font-medium">
                                {formatPrice(totals.subtotal)}
                            </span>
                        </div>

                        {totals.tax_total > 0 && (
                            <>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {__('Tax Amount')}
                                    </span>
                                    <span className="font-medium">
                                        {formatPrice(totals.tax_total)}
                                    </span>
                                </div>
                            </>
                        )}

                        {enableDiscounts && totals.discount_total > 0 && (
                            <div className="flex justify-between text-destructive">
                                <span>{__('Discount ($)')}</span>
                                <span className="font-medium">
                                    -{formatPrice(totals.discount_total)}
                                </span>
                            </div>
                        )}

                        <div className="mt-2 border-t border-border pt-2">
                            <div className="flex justify-between text-lg font-bold">
                                <span>{__('Total')}</span>
                                <span>{formatPrice(totals.total)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Action Buttons */}
            <div className="space-y-2 border-t border-border p-4">
                <Button
                    onClick={onPayNow}
                    disabled={isEmpty}
                    className="h-12 w-full text-base font-semibold"
                    size="lg"
                >
                    {__('Pay Now')}
                </Button>

                <div className="grid grid-cols-2 gap-2">
                    <Button
                        onClick={onClearCart}
                        disabled={isEmpty}
                        variant="outline"
                        size="sm"
                        className="gap-2"
                    >
                        <Trash2 className="h-4 w-4" />
                        {__('Reset')}
                    </Button>

                    <Button
                        onClick={onHoldSale}
                        disabled={isEmpty}
                        variant="outline"
                        size="sm"
                    >
                        {__('Hold Sale')}
                    </Button>
                </div>
            </div>
        </div>
    );
}
