import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Package, Plus, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatAmount } from '@/lib/formatters';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import type { App, Inertia } from '@/wayfinder/types';

interface ItemRow {
    _key: number;
    product_id: number | '';
    batch_id: number | '';
    quantity: number;
    unit_price: number;
    unit_cost: number;
    _product?: App.Models.Product;
    _batches?: App.Models.Batch[];
}

interface FormData {
    customer_id: string;
    warehouse_id: string;
    status: App.Enums.SaleStatusEnum;
    sale_date: string;
    total_amount: number;
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
    customers: App.Models.Customer[];
    warehouses: App.Models.Warehouse[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
}

let rowKey = 0;
const makeRow = (): ItemRow => ({
    _key: ++rowKey,
    product_id: '',
    batch_id: '',
    quantity: 1,
    unit_price: 0,
    unit_cost: 0,
});

const today = new Date().toISOString().slice(0, 10);

export default function SaleCreate({ customers, warehouses, products }: Props) {
    const [rows, setRows] = useState<ItemRow[]>([makeRow()]);

    const { data, setData, post, processing, errors, reset } =
        useForm<FormData>({
            customer_id: '',
            warehouse_id: '',
            status: 'pending',
            sale_date: today,
            total_amount: 0,
            note: '',
            items: [],
        });

    const syncItems = useCallback(
        (currentRows: ItemRow[]) => {
            const validRows = currentRows.filter(
                (r) => r.product_id !== '' && r.batch_id !== '',
            );
            setData(
                'items',
                validRows.map((r) => ({
                    product_id: r.product_id as number,
                    batch_id: r.batch_id as number,
                    quantity: r.quantity,
                    unit_price: r.unit_price,
                    unit_cost: r.unit_cost,
                })),
            );
            setData(
                'total_amount',
                currentRows.reduce((s, r) => s + r.quantity * r.unit_price, 0),
            );
        },
        [setData],
    );

    useEffect(() => {
        syncItems(rows);
    }, [rows, syncItems]);

    function handleProductChange(key: number, productId: string) {
        const product = products.find((p) => p.id === Number(productId));
        const warehouseId = Number(data.warehouse_id);
        setRows((prev) =>
            prev.map((r) =>
                r._key !== key
                    ? r
                    : {
                          ...r,
                          product_id: productId === '' ? '' : Number(productId),
                          batch_id: '',
                          unit_price: product?.selling_price ?? 0,
                          unit_cost: product?.cost_price ?? 0,
                          _product: product,
                          _batches: (product?.batches ?? []).filter(
                              (b) =>
                                  !warehouseId ||
                                  b.warehouse_id === warehouseId,
                          ),
                      },
            ),
        );
    }

    function handleWarehouseChange(warehouseId: string) {
        setData('warehouse_id', warehouseId);
        const wid = Number(warehouseId);
        setRows((prev) =>
            prev.map((r) => ({
                ...r,
                batch_id: '',
                _batches: (r._product?.batches ?? []).filter(
                    (b) => !wid || b.warehouse_id === wid,
                ),
            })),
        );
    }

    function updateRow(key: number, patch: Partial<ItemRow>) {
        setRows((prev) =>
            prev.map((r) => (r._key === key ? { ...r, ...patch } : r)),
        );
    }

    const subtotal = rows.reduce((s, r) => s + r.quantity * r.unit_price, 0);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(SaleController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setRows([makeRow()]);
            },
        });
    }

    return (
        <AppLayout>
            <Head title="New Sale" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(SaleController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                New Sale
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Create a new sales order
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>Customer</Label>
                                <Select
                                    value={data.customer_id || 'walk-in'}
                                    onValueChange={(v) =>
                                        setData(
                                            'customer_id',
                                            v === 'walk-in' ? '' : v,
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Walk-in customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="walk-in">
                                            Walk-in
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
                                <Label>
                                    Warehouse{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.warehouse_id}
                                    onValueChange={handleWarehouseChange}
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select warehouse" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map((w) => (
                                            <SelectItem
                                                key={w.id}
                                                value={String(w.id)}
                                            >
                                                {w.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.warehouse_id && (
                                    <p className="text-xs text-destructive">
                                        {errors.warehouse_id}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label>Status</Label>
                                <Select
                                    value={data.status}
                                    onValueChange={(v) =>
                                        setData(
                                            'status',
                                            v as App.Enums.SaleStatusEnum,
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">
                                            Pending
                                        </SelectItem>
                                        <SelectItem value="completed">
                                            Completed
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1.5">
                                <Label>
                                    Sale date{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    type="date"
                                    value={data.sale_date}
                                    onChange={(e) =>
                                        setData('sale_date', e.target.value)
                                    }
                                    required
                                />
                                {errors.sale_date && (
                                    <p className="text-xs text-destructive">
                                        {errors.sale_date}
                                    </p>
                                )}
                            </div>

                            <div className="col-span-2 space-y-1.5">
                                <Label>Note</Label>
                                <Textarea
                                    rows={1}
                                    className="resize-none"
                                    value={data.note}
                                    onChange={(e) =>
                                        setData('note', e.target.value)
                                    }
                                    placeholder="Internal note…"
                                />
                            </div>
                        </div>

                        <Separator />

                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <h3 className="text-sm font-semibold">Items</h3>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        setRows((p) => [...p, makeRow()])
                                    }
                                >
                                    <Plus className="mr-1.5 h-3.5 w-3.5" /> Add
                                    item
                                </Button>
                            </div>

                            {errors.items && (
                                <p className="text-xs text-destructive">
                                    {errors.items as string}
                                </p>
                            )}

                            <div className="grid grid-cols-[1fr_1fr_80px_110px_36px] gap-2 px-1">
                                {[
                                    'Product',
                                    'Batch',
                                    'Qty',
                                    'Unit price',
                                    '',
                                ].map((h) => (
                                    <span
                                        key={h}
                                        className="text-xs font-medium text-muted-foreground"
                                    >
                                        {h}
                                    </span>
                                ))}
                            </div>

                            <div className="space-y-2">
                                {rows.map((row) => (
                                    <div
                                        key={row._key}
                                        className="grid grid-cols-[1fr_1fr_80px_110px_36px] items-center gap-2"
                                    >
                                        <Select
                                            value={
                                                row.product_id === ''
                                                    ? ''
                                                    : String(row.product_id)
                                            }
                                            onValueChange={(v) =>
                                                handleProductChange(row._key, v)
                                            }
                                        >
                                            <SelectTrigger className="h-9">
                                                <SelectValue placeholder="Select product">
                                                    {row._product && (
                                                        <span className="flex items-center gap-1.5">
                                                            <Package className="h-3 w-3 shrink-0 text-muted-foreground" />
                                                            <span className="truncate">
                                                                {
                                                                    row._product
                                                                        .name
                                                                }
                                                            </span>
                                                        </span>
                                                    )}
                                                </SelectValue>
                                            </SelectTrigger>
                                            <SelectContent>
                                                {products.map((p) => (
                                                    <SelectItem
                                                        key={p.id}
                                                        value={String(p.id)}
                                                    >
                                                        {p.name}{' '}
                                                        <span className="ml-2 text-xs text-muted-foreground">
                                                            {p.sku}
                                                        </span>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        <Select
                                            value={
                                                row.batch_id === ''
                                                    ? ''
                                                    : String(row.batch_id)
                                            }
                                            onValueChange={(v) =>
                                                updateRow(row._key, {
                                                    batch_id:
                                                        v === ''
                                                            ? ''
                                                            : Number(v),
                                                })
                                            }
                                            disabled={!row._batches?.length}
                                        >
                                            <SelectTrigger className="h-9">
                                                <SelectValue placeholder="Batch" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {(row._batches ?? []).map(
                                                    (b) => (
                                                        <SelectItem
                                                            key={b.id}
                                                            value={String(b.id)}
                                                        >
                                                            {b.batch_number}{' '}
                                                            <span className="text-xs text-muted-foreground">
                                                                ({b.quantity})
                                                            </span>
                                                        </SelectItem>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>

                                        <Input
                                            type="number"
                                            min={1}
                                            className="h-9"
                                            value={row.quantity}
                                            onChange={(e) =>
                                                updateRow(row._key, {
                                                    quantity: Math.max(
                                                        1,
                                                        Number(e.target.value),
                                                    ),
                                                })
                                            }
                                        />

                                        <Input
                                            type="number"
                                            min={0}
                                            className="h-9 font-mono"
                                            value={row.unit_price}
                                            onChange={(e) =>
                                                updateRow(row._key, {
                                                    unit_price: Number(
                                                        e.target.value,
                                                    ),
                                                })
                                            }
                                        />

                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            className="h-9 w-9 text-muted-foreground hover:text-destructive"
                                            onClick={() =>
                                                setRows((p) =>
                                                    p.filter(
                                                        (r) =>
                                                            r._key !== row._key,
                                                    ),
                                                )
                                            }
                                            disabled={rows.length === 1}
                                        >
                                            <X className="h-4 w-4" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <Separator />

                        <div className="flex justify-end">
                            <div className="w-64 space-y-2 text-sm">
                                <h3 className="font-semibold">Summary</h3>
                                <div className="space-y-1.5 rounded-lg bg-muted/40 p-3">
                                    <div className="flex justify-between text-base font-semibold">
                                        <span>Total</span>
                                        <span className="font-mono">
                                            {formatAmount(subtotal)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(SaleController.index.url())
                                }
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Create sale
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
