import { useState } from 'react';

import { useForm } from '@inertiajs/react';

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
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Check, Package, Search } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Returns',
        href: '/returns',
    },
    {
        title: 'New Return',
        href: '/returns/create',
    },
];

export default function ReturnsCreate({
    sales,
    selectedSale,
}: {
    sales: any[];
    selectedSale?: any;
}) {
    const [selectedSaleId, setSelectedSaleId] = useState<number | null>(
        selectedSale?.id ?? null,
    );
    const [selectedItems, setSelectedItems] = useState<Record<number, number>>(
        {},
    );

    const { data, setData, post, processing } = useForm({
        sale_id: selectedSale?.id ?? null,
        warehouse_id: 1,
        return_date: new Date().toISOString().split('T')[0],
        note: '',
        items: [] as Array<{
            product_id: number;
            batch_id: number | null;
            quantity: number;
            unit_price: number;
        }>,
    });

    const sale = sales.find((s) => s.id === selectedSaleId);

    const toggleItem = (productId: number, maxQuantity: number) => {
        setSelectedItems((prev) => {
            const current = prev[productId] || 0;
            if (current >= maxQuantity) {
                const { [productId]: _, ...rest } = prev;
                return rest;
            }
            return { ...prev, [productId]: (prev[productId] || 0) + 1 };
        });
    };

    const handleSubmit = () => {
        const items = Object.entries(selectedItems).map(
            ([productId, quantity]) => {
                const saleItem = sale?.items.find(
                    (i: any) => i.product.id === parseInt(productId),
                );
                return {
                    product_id: parseInt(productId),
                    batch_id: saleItem?.batch?.id ?? null,
                    quantity,
                    unit_price: saleItem?.unit_price ?? 0,
                };
            },
        );

        setData('items', items);

        post('/returns', {
            onSuccess: () => {
                setSelectedItems({});
                setSelectedSaleId(null);
            },
        });
    };

    const returnTotal = Object.entries(selectedItems).reduce(
        (sum, [productId, quantity]) => {
            const saleItem = sale?.items.find(
                (i: any) => i.product.id === parseInt(productId),
            );
            return sum + (saleItem?.unit_price ?? 0) * quantity;
        },
        0,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Return" />
            <div className="mb-6">
                <h1 className="text-2xl font-bold">New Return</h1>
                <p className="text-muted-foreground">
                    Process a product return from a completed sale
                </p>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Select Sale</CardTitle>
                        <CardDescription>
                            Choose a completed sale to return items from
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="relative">
                            <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                            <Input
                                placeholder="Search by reference number..."
                                className="pl-8"
                            />
                        </div>
                        <div className="max-h-96 space-y-2 overflow-y-auto">
                            {sales.map((s) => (
                                <div
                                    key={s.id}
                                    className={`cursor-pointer rounded-lg border p-3 transition-colors ${
                                        selectedSaleId === s.id
                                            ? 'border-primary bg-primary/5'
                                            : 'hover:border-gray-300'
                                    }`}
                                    onClick={() => setSelectedSaleId(s.id)}
                                >
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <div className="font-medium">
                                                {s.reference_no}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {s.customer?.name ?? 'Walk-in'}{' '}
                                                •{' '}
                                                {new Date(
                                                    s.sale_date,
                                                ).toLocaleDateString()}
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <div className="font-medium">
                                                $
                                                {(s.total_amount / 100).toFixed(
                                                    2,
                                                )}
                                            </div>
                                            <Badge variant="outline">
                                                {s.items?.length ?? 0} items
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Select Items</CardTitle>
                        <CardDescription>
                            Choose items and quantities to return
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {!sale ? (
                            <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
                                <Package className="mb-2 h-12 w-12" />
                                <p>Select a sale to see returnable items</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className="rounded-lg bg-muted p-3">
                                    <div className="text-sm text-muted-foreground">
                                        Returning from
                                    </div>
                                    <div className="font-medium">
                                        {sale.reference_no}
                                    </div>
                                </div>
                                <Separator />
                                <div className="max-h-64 space-y-2 overflow-y-auto">
                                    {sale.items?.map((item: any) => (
                                        <div
                                            key={item.id}
                                            className="flex items-center justify-between rounded-lg border p-2"
                                        >
                                            <div className="flex-1">
                                                <div className="text-sm font-medium">
                                                    {item.product.name}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    $
                                                    {(
                                                        item.unit_price / 100
                                                    ).toFixed(2)}{' '}
                                                    each
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm text-muted-foreground">
                                                    {item.quantity} available
                                                </span>
                                                <Button
                                                    variant={
                                                        selectedItems[
                                                            item.product.id
                                                        ]
                                                            ? 'default'
                                                            : 'outline'
                                                    }
                                                    size="sm"
                                                    onClick={() =>
                                                        toggleItem(
                                                            item.product.id,
                                                            item.quantity,
                                                        )
                                                    }
                                                >
                                                    {selectedItems[
                                                        item.product.id
                                                    ] ? (
                                                        <>
                                                            <Check className="mr-1 h-3 w-3" />
                                                            {
                                                                selectedItems[
                                                                    item.product
                                                                        .id
                                                                ]
                                                            }
                                                        </>
                                                    ) : (
                                                        'Select'
                                                    )}
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <Separator />
                                <div className="flex justify-between text-lg font-bold">
                                    <span>Return Total</span>
                                    <span>
                                        ${(returnTotal / 100).toFixed(2)}
                                    </span>
                                </div>
                                <Button
                                    className="w-full"
                                    disabled={
                                        Object.keys(selectedItems).length ===
                                            0 || processing
                                    }
                                    onClick={handleSubmit}
                                >
                                    Process Return
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
