import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import {
    CreditCard,
    Minus,
    Plus,
    Search,
    ShoppingCart,
    Trash2,
} from 'lucide-react';

interface Product {
    id: number;
    name: string;
    sku: string;
    barcode: string;
    selling_price: number;
    cost_price: number;
    track_inventory: boolean;
    category: string | null;
    stock: number;
    batches: {
        id: number;
        batch_number: string;
        quantity: number;
        expiry_date: string | null;
    }[];
}

interface CartItem {
    product_id: number;
    product_name: string;
    product_sku: string;
    batch_id: number | null;
    batch_number: string | null;
    quantity: number;
    unit_price: number;
    unit_cost: number;
    subtotal: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'POS Terminal',
        href: '/pos',
    },
];

export default function PosCreate({ products }: { products: Product[] }) {
    const [cart, setCart] = useState<CartItem[]>([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCustomer, setSelectedCustomer] = useState<number | null>(
        null,
    );
    const [customerName, setCustomerName] = useState('');

    const { data, setData, post, processing } = useForm({
        customer_id: null as number | null,
        warehouse_id: 1,
        sale_date: new Date().toISOString().split('T')[0],
        note: '',
        paid_amount: 0,
        payment_method_id: 1,
        items: [] as Array<{
            product_id: number;
            batch_id: number | null;
            quantity: number;
            unit_price: number;
            unit_cost: number;
        }>,
    });

    const filteredProducts = products.filter(
        (product) =>
            product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            product.sku.toLowerCase().includes(searchQuery.toLowerCase()) ||
            product.barcode.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    const cartTotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const cartCount = cart.reduce((sum, item) => sum + item.quantity, 0);

    const addToCart = (product: Product) => {
        const existingItem = cart.find(
            (item) => item.product_id === product.id,
        );

        if (existingItem) {
            if (
                product.track_inventory &&
                existingItem.quantity >= product.stock
            ) {
                return;
            }
            updateQuantity(existingItem.product_id, existingItem.quantity + 1);
        } else {
            const batchId = product.batches[0]?.id ?? null;
            const batchNumber = product.batches[0]?.batch_number ?? null;
            const newItem: CartItem = {
                product_id: product.id,
                product_name: product.name,
                product_sku: product.sku,
                batch_id: batchId,
                batch_number: batchNumber,
                quantity: 1,
                unit_price: product.selling_price,
                unit_cost: product.cost_price,
                subtotal: product.selling_price,
            };
            setCart([...cart, newItem]);
        }
    };

    const updateQuantity = (productId: number, quantity: number) => {
        if (quantity <= 0) {
            removeFromCart(productId);
            return;
        }

        setCart(
            cart.map((item) =>
                item.product_id === productId
                    ? {
                          ...item,
                          quantity,
                          subtotal: item.unit_price * quantity,
                      }
                    : item,
            ),
        );
    };

    const removeFromCart = (productId: number) => {
        setCart(cart.filter((item) => item.product_id !== productId));
    };

    const clearCart = () => {
        setCart([]);
    };

    const handleCustomerSelect = (customerId: string) => {
        const id = customerId === 'walk-in' ? null : parseInt(customerId);
        setSelectedCustomer(id);
        setData('customer_id', id);
    };

    const handleCheckout = (complete: boolean = true) => {
        const items = cart.map((item) => ({
            product_id: item.product_id,
            batch_id: item.batch_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            unit_cost: item.unit_cost,
        }));

        setData('items', items);
        setData('paid_amount', complete ? cartTotal : 0);

        post('/pos/quick-sale', {
            onSuccess: () => {
                clearCart();
                setSelectedCustomer(null);
                setCustomerName('');
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="POS Terminal" />
            <div className="flex h-[calc(100vh-8rem)] gap-4">
                <div className="flex flex-1 flex-col gap-4">
                    <Card className="flex-1">
                        <CardHeader className="pb-3">
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle>Products</CardTitle>
                                    <CardDescription>
                                        Click to add products to cart
                                    </CardDescription>
                                </div>
                                <div className="relative w-64">
                                    <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Search products..."
                                        className="pl-8"
                                        value={searchQuery}
                                        onChange={(e) =>
                                            setSearchQuery(e.target.value)
                                        }
                                    />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="grid max-h-[calc(100vh-20rem)] grid-cols-2 gap-3 overflow-y-auto md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                {filteredProducts.map((product) => (
                                    <Card
                                        key={product.id}
                                        className="cursor-pointer transition-colors hover:border-primary"
                                        onClick={() => addToCart(product)}
                                    >
                                        <CardContent className="p-3">
                                            <div className="truncate text-sm font-medium">
                                                {product.name}
                                            </div>
                                            <div className="truncate text-xs text-muted-foreground">
                                                {product.sku}
                                            </div>
                                            <div className="mt-2 flex items-center justify-between">
                                                <span className="font-bold">
                                                    $
                                                    {(
                                                        product.selling_price /
                                                        100
                                                    ).toFixed(2)}
                                                </span>
                                                {product.track_inventory && (
                                                    <Badge
                                                        variant={
                                                            product.stock > 10
                                                                ? 'default'
                                                                : product.stock >
                                                                    0
                                                                  ? 'secondary'
                                                                  : 'destructive'
                                                        }
                                                        className="text-xs"
                                                    >
                                                        {product.stock}
                                                    </Badge>
                                                )}
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="flex w-96 flex-col">
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                <ShoppingCart className="h-5 w-5" />
                                Cart
                            </CardTitle>
                            <Badge variant="outline">{cartCount} items</Badge>
                        </div>
                        <div className="mt-2">
                            <Select
                                value={
                                    selectedCustomer?.toString() ?? 'walk-in'
                                }
                                onValueChange={handleCustomerSelect}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select customer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="walk-in">
                                        Walk-in Customer
                                    </SelectItem>
                                    <SelectItem value="1">
                                        Customer 1
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent className="flex-1 overflow-y-auto">
                        {cart.length === 0 ? (
                            <div className="flex h-full flex-col items-center justify-center text-muted-foreground">
                                <ShoppingCart className="mb-2 h-12 w-12" />
                                <p>Cart is empty</p>
                                <p className="text-sm">Click products to add</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {cart.map((item) => (
                                    <div
                                        key={item.product_id}
                                        className="flex items-center justify-between gap-2 rounded-lg border p-2"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-sm font-medium">
                                                {item.product_name}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                $
                                                {(
                                                    item.unit_price / 100
                                                ).toFixed(2)}{' '}
                                                each
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                className="h-7 w-7"
                                                onClick={() =>
                                                    updateQuantity(
                                                        item.product_id,
                                                        item.quantity - 1,
                                                    )
                                                }
                                            >
                                                <Minus className="h-3 w-3" />
                                            </Button>
                                            <span className="w-8 text-center text-sm">
                                                {item.quantity}
                                            </span>
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                className="h-7 w-7"
                                                onClick={() =>
                                                    updateQuantity(
                                                        item.product_id,
                                                        item.quantity + 1,
                                                    )
                                                }
                                            >
                                                <Plus className="h-3 w-3" />
                                            </Button>
                                        </div>
                                        <div className="w-16 text-right">
                                            <div className="text-sm font-medium">
                                                $
                                                {(item.subtotal / 100).toFixed(
                                                    2,
                                                )}
                                            </div>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="h-6 w-6 text-muted-foreground hover:text-destructive"
                                                onClick={() =>
                                                    removeFromCart(
                                                        item.product_id,
                                                    )
                                                }
                                            >
                                                <Trash2 className="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                    {cart.length > 0 && (
                        <div className="space-y-3 border-t p-4">
                            <div className="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span>${(cartTotal / 100).toFixed(2)}</span>
                            </div>
                            <Separator />
                            <div className="space-y-2">
                                <Label>Payment Method</Label>
                                <Select
                                    value={data.payment_method_id.toString()}
                                    onValueChange={(value) =>
                                        setData(
                                            'payment_method_id',
                                            parseInt(value),
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">Cash</SelectItem>
                                        <SelectItem value="2">Card</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    className="flex-1"
                                    onClick={() => handleCheckout(true)}
                                    disabled={processing}
                                >
                                    <CreditCard className="mr-2 h-4 w-4" />
                                    Pay Now
                                </Button>
                                <Button
                                    variant="outline"
                                    onClick={() => handleCheckout(false)}
                                    disabled={processing}
                                >
                                    Hold
                                </Button>
                            </div>
                            <Button
                                variant="ghost"
                                className="w-full text-muted-foreground"
                                onClick={clearCart}
                            >
                                Clear Cart
                            </Button>
                        </div>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
