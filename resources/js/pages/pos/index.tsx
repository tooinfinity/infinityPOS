import { Head, useForm } from '@inertiajs/react';
import {
    ChevronRight,
    CreditCard,
    Loader2,
    Minus,
    Package,
    Plus,
    Search,
    ShoppingCart,
    Trash2,
    X,
} from 'lucide-react';
import { useCallback, useMemo, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatMoney } from '@/lib/formatters';
import PosController from '@/wayfinder/App/Http/Controllers/Pos/PosController';
import type { App, Inertia } from '@/wayfinder/types';

interface CartItem {
    _key: number;
    product: App.Models.Product;
    batch: App.Models.Batch;
    quantity: number;
    unit_price: number;
}

interface FormData {
    customer_id: string;
    warehouse_id: string;
    payment_method_id: string;
    note: string;
    items: Array<{
        product_id: number;
        batch_id: number;
        quantity: number;
        unit_price: number;
        unit_cost: number;
    }>;
}

interface Props extends Inertia.SharedData {
    warehouses: App.Models.Warehouse[];
    customers: App.Models.Customer[];
    paymentMethods: App.Models.PaymentMethod[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
}

let cartKey = 0;

export default function PosIndex({
    warehouses,
    customers,
    paymentMethods,
    products,
}: Props) {
    const [cart, setCart] = useState<CartItem[]>([]);
    const [search, setSearch] = useState('');
    const [selectedWarehouse, setSelectedWarehouse] = useState<string>('');
    const searchRef = useRef<HTMLInputElement>(null);

    const { data, setData, transform, post, processing, reset } =
        useForm<FormData>({
            customer_id: '',
            warehouse_id: '',
            payment_method_id: paymentMethods[0]
                ? String(paymentMethods[0].id)
                : '',
            note: '',
            items: [],
        });

    const filteredProducts = useMemo(() => {
        if (!search.trim()) return products.slice(0, 40);
        const q = search.toLowerCase();
        return products
            .filter(
                (p) =>
                    p.name.toLowerCase().includes(q) ||
                    p.sku?.toLowerCase().includes(q) ||
                    p.barcode?.toLowerCase().includes(q),
            )
            .slice(0, 40);
    }, [products, search]);

    const availableBatches = useCallback(
        (product: App.Models.Product & { batches?: App.Models.Batch[] }) => {
            const wid = Number(selectedWarehouse);
            return (product.batches ?? []).filter(
                (b) => b.quantity > 0 && (!wid || b.warehouse_id === wid),
            );
        },
        [selectedWarehouse],
    );

    function addToCart(
        product: App.Models.Product & { batches?: App.Models.Batch[] },
    ) {
        const batches = availableBatches(product);
        if (!batches.length) return;
        const batch = batches[0];

        const existingIdx = cart.findIndex(
            (c) => c.product.id === product.id && c.batch.id === batch.id,
        );

        if (existingIdx >= 0) {
            setCart((prev) =>
                prev.map((c, i) =>
                    i === existingIdx ? { ...c, quantity: c.quantity + 1 } : c,
                ),
            );
        } else {
            setCart((prev) => [
                ...prev,
                {
                    _key: ++cartKey,
                    product,
                    batch,
                    quantity: 1,
                    unit_price: product.selling_price ?? 0,
                },
            ]);
        }
    }

    function updateQty(key: number, delta: number) {
        setCart((prev) =>
            prev
                .map((c) =>
                    c._key === key ? { ...c, quantity: c.quantity + delta } : c,
                )
                .filter((c) => c.quantity > 0),
        );
    }

    function removeFromCart(key: number) {
        setCart((prev) => prev.filter((c) => c._key !== key));
    }

    function clearCart() {
        setCart([]);
    }

    const subtotal = cart.reduce((s, c) => s + c.quantity * c.unit_price, 0);

    function handleWarehouseChange(warehouseId: string) {
        setSelectedWarehouse(warehouseId);
        setData('warehouse_id', warehouseId);
        setCart([]);
    }

    function handleCheckout() {
        if (!cart.length) return;

        transform((d) => ({
            ...d,
            items: cart.map((c) => ({
                product_id: c.product.id,
                batch_id: c.batch.id,
                quantity: c.quantity,
                unit_price: c.unit_price,
                unit_cost: c.batch.cost_amount ?? 0,
            })),
        }));

        post(PosController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                clearCart();
                reset();
                setData(
                    'payment_method_id',
                    paymentMethods[0] ? String(paymentMethods[0].id) : '',
                );
                searchRef.current?.focus();
            },
        });
    }

    return (
        <AppLayout>
            <Head title="Point of Sale" />
            <div className="flex h-[calc(100vh-4rem)] gap-0 overflow-hidden">
                {/* Left: product catalog */}
                <div className="flex flex-1 flex-col overflow-hidden border-r">
                    {/* Toolbar */}
                    <div className="flex items-center gap-3 border-b p-3">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                ref={searchRef}
                                className="h-9 pr-8 pl-9"
                                placeholder="Search product, SKU or barcode…"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                autoFocus
                            />
                            {search && (
                                <button
                                    onClick={() => setSearch('')}
                                    className="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <X className="h-3.5 w-3.5" />
                                </button>
                            )}
                        </div>
                        <Select
                            value={selectedWarehouse}
                            onValueChange={handleWarehouseChange}
                        >
                            <SelectTrigger className="h-9 w-44">
                                <SelectValue placeholder="All warehouses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All warehouses
                                </SelectItem>
                                {warehouses.map((w) => (
                                    <SelectItem key={w.id} value={String(w.id)}>
                                        {w.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Product grid */}
                    <div className="flex-1 overflow-y-auto p-3">
                        {filteredProducts.length === 0 ? (
                            <div className="flex h-40 flex-col items-center justify-center gap-2 text-muted-foreground">
                                <Package className="h-8 w-8 opacity-30" />
                                <p className="text-sm">No products found</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-3 gap-2 xl:grid-cols-4 2xl:grid-cols-5">
                                {filteredProducts.map((product) => {
                                    const batches = availableBatches(product);
                                    const inStock = batches.length > 0;
                                    const totalQty = batches.reduce(
                                        (s, b) => s + b.quantity,
                                        0,
                                    );

                                    return (
                                        <button
                                            key={product.id}
                                            onClick={() => addToCart(product)}
                                            disabled={!inStock}
                                            className="group relative flex flex-col items-start gap-1.5 rounded-lg border bg-card p-3 text-left transition-all hover:border-primary/40 hover:shadow-sm disabled:cursor-not-allowed disabled:opacity-40"
                                        >
                                            <div className="flex w-full items-start justify-between gap-1">
                                                <span className="line-clamp-2 text-sm leading-tight font-medium">
                                                    {product.name}
                                                </span>
                                                <ChevronRight className="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                                            </div>
                                            <span className="font-mono text-xs text-muted-foreground">
                                                {product.sku}
                                            </span>
                                            <div className="mt-auto flex w-full items-end justify-between">
                                                <span className="font-mono text-sm font-semibold">
                                                    {formatMoney(
                                                        product.selling_price,
                                                    )}
                                                </span>
                                                <span
                                                    className={`text-xs tabular-nums ${
                                                        totalQty === 0
                                                            ? 'text-red-500'
                                                            : totalQty < 5
                                                              ? 'text-amber-600'
                                                              : 'text-muted-foreground'
                                                    }`}
                                                >
                                                    {totalQty} left
                                                </span>
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </div>

                {/* Right: cart + checkout */}
                <div className="flex w-80 flex-col overflow-hidden xl:w-96">
                    {/* Cart header */}
                    <div className="flex items-center justify-between border-b px-4 py-3">
                        <div className="flex items-center gap-2">
                            <ShoppingCart className="h-4 w-4" />
                            <span className="font-semibold">Cart</span>
                            {cart.length > 0 && (
                                <span className="rounded-full bg-primary px-1.5 py-0.5 text-xs font-semibold text-primary-foreground">
                                    {cart.length}
                                </span>
                            )}
                        </div>
                        {cart.length > 0 && (
                            <button
                                onClick={clearCart}
                                className="flex items-center gap-1 text-xs text-muted-foreground hover:text-destructive"
                            >
                                <Trash2 className="h-3.5 w-3.5" /> Clear
                            </button>
                        )}
                    </div>

                    {/* Cart items */}
                    <div className="flex-1 overflow-y-auto">
                        {cart.length === 0 ? (
                            <div className="flex h-40 flex-col items-center justify-center gap-2 text-muted-foreground">
                                <ShoppingCart className="h-8 w-8 opacity-30" />
                                <p className="text-sm">Cart is empty</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {cart.map((item) => (
                                    <div
                                        key={item._key}
                                        className="flex items-start gap-3 px-4 py-3"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-medium">
                                                {item.product.name}
                                            </p>
                                            <p className="font-mono text-xs text-muted-foreground">
                                                {item.batch.batch_number}
                                            </p>
                                        </div>
                                        <div className="flex flex-col items-end gap-1.5">
                                            <span className="font-mono text-sm font-semibold">
                                                {formatMoney(
                                                    item.quantity *
                                                        item.unit_price,
                                                )}
                                            </span>
                                            <div className="flex items-center gap-1">
                                                <button
                                                    onClick={() =>
                                                        updateQty(item._key, -1)
                                                    }
                                                    className="flex h-5 w-5 items-center justify-center rounded border text-muted-foreground hover:bg-muted"
                                                >
                                                    <Minus className="h-3 w-3" />
                                                </button>
                                                <span className="w-6 text-center font-mono text-sm">
                                                    {item.quantity}
                                                </span>
                                                <button
                                                    onClick={() =>
                                                        updateQty(item._key, +1)
                                                    }
                                                    className="flex h-5 w-5 items-center justify-center rounded border text-muted-foreground hover:bg-muted"
                                                >
                                                    <Plus className="h-3 w-3" />
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        removeFromCart(
                                                            item._key,
                                                        )
                                                    }
                                                    className="ml-1 flex h-5 w-5 items-center justify-center rounded text-muted-foreground hover:text-destructive"
                                                >
                                                    <X className="h-3 w-3" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Checkout panel */}
                    <div className="space-y-3 border-t p-4">
                        <div className="space-y-2">
                            <div className="space-y-1.5">
                                <Label className="text-xs">Customer</Label>
                                <Select
                                    value={data.customer_id || 'walk-in'}
                                    onValueChange={(v) =>
                                        setData(
                                            'customer_id',
                                            v === 'walk-in' ? '' : v,
                                        )
                                    }
                                >
                                    <SelectTrigger className="h-8 text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="walk-in">
                                            Walk-in customer
                                        </SelectItem>
                                        {customers.map((c) => (
                                            <SelectItem
                                                key={c.id}
                                                value={String(c.id)}
                                            >
                                                {c.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1.5">
                                <Label className="text-xs">
                                    Payment method
                                </Label>
                                <Select
                                    value={data.payment_method_id}
                                    onValueChange={(v) =>
                                        setData('payment_method_id', v)
                                    }
                                >
                                    <SelectTrigger className="h-8 text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {paymentMethods.map((m) => (
                                            <SelectItem
                                                key={m.id}
                                                value={String(m.id)}
                                            >
                                                {m.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <Separator />

                        <div className="flex justify-between text-base font-semibold">
                            <span>Total</span>
                            <span className="font-mono">
                                {formatMoney(subtotal)}
                            </span>
                        </div>

                        <Button
                            className="w-full"
                            size="lg"
                            disabled={
                                cart.length === 0 ||
                                processing ||
                                !data.warehouse_id
                            }
                            onClick={handleCheckout}
                        >
                            {processing ? (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            ) : (
                                <CreditCard className="mr-2 h-4 w-4" />
                            )}
                            Charge {formatMoney(subtotal)}
                        </Button>

                        {!data.warehouse_id && (
                            <p className="text-center text-xs text-muted-foreground">
                                Select a warehouse to enable checkout
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
