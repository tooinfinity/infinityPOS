import { cn } from '@/lib/utils';
import { Package } from 'lucide-react';

interface ProductCardProps {
    id: number;
    name: string;
    sku: string | null;
    price: number;
    image: string | null;
    available_stock: number | null;
    category?: {
        name: string;
    } | null;
    onAddToCart: (productId: number) => void;
}

export function ProductCard({
    id,
    name,
    sku,
    price,
    image,
    available_stock,
    onAddToCart,
}: ProductCardProps) {
    const isLowStock = available_stock !== null && available_stock < 5;
    const isOutOfStock = available_stock !== null && available_stock === 0;

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    return (
        <button
            onClick={() => !isOutOfStock && onAddToCart(id)}
            disabled={isOutOfStock}
            className={cn(
                'group relative flex flex-col overflow-hidden rounded-lg border border-border/50 bg-card/50 transition-all duration-200',
                'hover:border-primary/40 hover:bg-card hover:shadow-sm active:scale-[0.98]',
                isOutOfStock
                    ? 'cursor-not-allowed opacity-50'
                    : 'cursor-pointer',
            )}
        >
            {/* Image Section */}
            <div className="relative aspect-square w-full overflow-hidden bg-muted/30">
                {image ? (
                    <img
                        src={image}
                        alt={name}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        loading="lazy"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center">
                        <Package className="h-10 w-10 text-muted-foreground/40" />
                    </div>
                )}

                {/* Stock Badge */}
                {available_stock !== null && (
                    <div
                        className={cn(
                            'absolute top-2 right-2 rounded px-2 py-0.5 text-xs font-medium shadow-sm',
                            isOutOfStock
                                ? 'bg-destructive/90 text-destructive-foreground'
                                : isLowStock
                                  ? 'bg-amber-500/90 text-white'
                                  : 'bg-emerald-500/90 text-white',
                        )}
                    >
                        {isOutOfStock ? 'Out' : isLowStock ? 'Low' : 'In Stock'}
                    </div>
                )}
            </div>

            {/* Content Section */}
            <div className="flex flex-col gap-0.5 p-2.5">
                <h3 className="line-clamp-2 text-sm leading-tight font-semibold text-card-foreground">
                    {name}
                </h3>

                {sku && (
                    <p className="text-xs text-muted-foreground">SKU: {sku}</p>
                )}

                <div className="mt-0.5 flex items-center justify-between gap-2">
                    <span className="text-sm font-bold text-primary">
                        {formatPrice(price)}
                    </span>

                    {available_stock !== null && !isOutOfStock && (
                        <span className="text-xs text-muted-foreground">
                            {available_stock} stock
                        </span>
                    )}
                </div>
            </div>
        </button>
    );
}
