import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Package, Plus, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
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
import PurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseController';
import type { App, Inertia } from '@/wayfinder/types';

interface ItemRow {
    _key: number;
    product_id: number | '';
    quantity: number;
    unit_cost: number;
    expires_at: string;
    _product?: App.Models.Product;
}

interface FormData {
    supplier_id: string;
    warehouse_id: string;
    status: App.Enums.PurchaseStatusEnum;
    purchase_date: string;
    total_amount: number;
    note: string;
    items: Array<{
        product_id: number;
        quantity: number;
        unit_cost: number;
        expires_at: string | null;
    }>;
}

interface Props extends Inertia.SharedData {
    purchase: App.Models.Purchase;
    suppliers: App.Models.Supplier[];
    warehouses: App.Models.Warehouse[];
    products: App.Models.Product[];
}

let rowKey = 0;
const makeRow = (): ItemRow => ({
    _key: ++rowKey,
    product_id: '',
    quantity: 1,
    unit_cost: 0,
    expires_at: '',
});

export default function PurchaseEdit({
    purchase,
    suppliers,
    warehouses,
    products,
}: Props) {
    const [rows, setRows] = useState<ItemRow[]>(() =>
        purchase.items?.length
            ? purchase.items.map((item) => ({
                  _key: ++rowKey,
                  product_id: item.product?.id ?? 0,
                  quantity: item.quantity,
                  unit_cost: item.unit_cost,
                  expires_at: item.expires_at
                      ? String(item.expires_at).slice(0, 10)
                      : '',
                  _product: item.product ?? undefined,
              }))
            : [makeRow()],
    );

    const { data, setData, put, processing, errors, clearErrors } =
        useForm<FormData>({
            supplier_id: purchase.supplier?.id?.toString() ?? '',
            warehouse_id: purchase.warehouse?.id?.toString() ?? '',
            status: purchase.status,
            purchase_date: purchase.purchase_date
                ? String(purchase.purchase_date).slice(0, 10)
                : new Date().toISOString().slice(0, 10),
            total_amount: purchase.total_amount ?? 0,
            note: purchase.note ?? '',
            items: [],
        });

    const syncItems = useCallback(
        (currentRows: ItemRow[]) => {
            const validRows = currentRows.filter((r) => r.product_id !== '');

            setData(
                'items',
                validRows.map((r) => ({
                    product_id: r.product_id as number,
                    quantity: r.quantity,
                    unit_cost: r.unit_cost,
                    expires_at: r.expires_at || null,
                })),
            );

            setData(
                'total_amount',
                currentRows.reduce((s, r) => s + r.quantity * r.unit_cost, 0),
            );
        },
        [setData],
    );

    useEffect(() => {
        syncItems(rows);
    }, [rows, syncItems]);

    function handleProductChange(key: number, productId: string) {
        const product = products.find((p) => p.id === Number(productId));
        setRows((prev) =>
            prev.map((r) =>
                r._key !== key
                    ? r
                    : {
                          ...r,
                          product_id: productId === '' ? '' : Number(productId),
                          unit_cost: product?.cost_price ?? 0,
                          _product: product,
                      },
            ),
        );
    }

    function updateRow(key: number, patch: Partial<ItemRow>) {
        setRows((prev) =>
            prev.map((r) => (r._key === key ? { ...r, ...patch } : r)),
        );
    }

    const subtotal = rows.reduce((s, r) => s + r.quantity * r.unit_cost, 0);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(PurchaseController.update.url({ purchase: purchase.id }), {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout>
            <Head title={`Edit Purchase — ${purchase.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(
                                    PurchaseController.show.url({
                                        purchase: purchase.id,
                                    }),
                                )
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Edit Purchase
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {purchase.reference_no}
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>
                                    Supplier{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.supplier_id}
                                    onValueChange={(v) =>
                                        setData('supplier_id', v)
                                    }
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select supplier" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {suppliers.map((s) => (
                                            <SelectItem
                                                key={s.id}
                                                value={String(s.id)}
                                            >
                                                {s.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.supplier_id && (
                                    <p className="text-xs text-destructive">
                                        {errors.supplier_id}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label>
                                    Warehouse{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.warehouse_id}
                                    onValueChange={(v) =>
                                        setData('warehouse_id', v)
                                    }
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
                                            v as App.Enums.PurchaseStatusEnum,
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
                                        <SelectItem value="ordered">
                                            Ordered
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1.5">
                                <Label>
                                    Purchase date{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    type="date"
                                    value={data.purchase_date}
                                    onChange={(e) =>
                                        setData('purchase_date', e.target.value)
                                    }
                                    required
                                />
                                {errors.purchase_date && (
                                    <p className="text-xs text-destructive">
                                        {errors.purchase_date}
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

                            <div className="grid grid-cols-[1fr_80px_110px_130px_36px] gap-2 px-1">
                                {[
                                    'Product',
                                    'Qty',
                                    'Unit cost',
                                    'Expiry date',
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
                                        className="grid grid-cols-[1fr_80px_110px_130px_36px] items-center gap-2"
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
                                            value={row.unit_cost}
                                            onChange={(e) =>
                                                updateRow(row._key, {
                                                    unit_cost: Number(
                                                        e.target.value,
                                                    ),
                                                })
                                            }
                                        />

                                        <Input
                                            type="date"
                                            className="h-9"
                                            value={row.expires_at}
                                            onChange={(e) =>
                                                updateRow(row._key, {
                                                    expires_at: e.target.value,
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

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => clearErrors()}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Save changes
                            </Button>
                        </DialogFooter>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
