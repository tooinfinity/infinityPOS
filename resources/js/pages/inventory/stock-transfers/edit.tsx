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
import StockTransferController from '@/wayfinder/App/Http/Controllers/Inventory/StockTransferController';
import type { App, Inertia } from '@/wayfinder/types';

interface ItemRow {
    _key: number;
    product_id: number | '';
    batch_id: number | '';
    quantity: number;
    _product?: App.Models.Product;
    _batches?: App.Models.Batch[];
}

interface FormData {
    from_warehouse_id: string;
    to_warehouse_id: string;
    transfer_date: string;
    note: string;
    items: Array<{ product_id: number; batch_id: number; quantity: number }>;
}

interface Props extends Inertia.SharedData {
    transfer: App.Models.StockTransfer;
    warehouses: App.Models.Warehouse[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
}

let rowKey = 0;

export default function StockTransferEdit({
    transfer,
    warehouses,
    products,
}: Props) {
    const [rows, setRows] = useState<ItemRow[]>(() =>
        transfer.items?.length
            ? transfer.items.map((item) => ({
                  _key: ++rowKey,
                  product_id: item.product?.id ?? 0,
                  batch_id: item.batch?.id ?? 0,
                  quantity: item.quantity,
                  _product: item.product ?? undefined,
                  _batches:
                      products.find((p) => p.id === item.product?.id)
                          ?.batches ?? [],
              }))
            : [{ _key: ++rowKey, product_id: '', batch_id: '', quantity: 1 }],
    );

    const { data, setData, put, processing, errors } = useForm<FormData>({
        from_warehouse_id: String(transfer.from_warehouse_id),
        to_warehouse_id: String(transfer.to_warehouse_id),
        transfer_date: transfer.transfer_date
            ? String(transfer.transfer_date).slice(0, 10)
            : new Date().toISOString().slice(0, 10),
        note: transfer.note ?? '',
        items: [],
    });

    const syncItems = useCallback(
        (currentRows: ItemRow[]) => {
            const valid = currentRows.filter(
                (r) => r.product_id !== '' && r.batch_id !== '',
            );
            setData(
                'items',
                valid.map((r) => ({
                    product_id: r.product_id as number,
                    batch_id: r.batch_id as number,
                    quantity: r.quantity,
                })),
            );
        },
        [setData],
    );

    useEffect(() => {
        syncItems(rows);
    }, [rows, syncItems]);

    function handleFromWarehouseChange(warehouseId: string) {
        setData('from_warehouse_id', warehouseId);
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

    function handleProductChange(key: number, productId: string) {
        const product = products.find((p) => p.id === Number(productId));
        const wid = Number(data.from_warehouse_id);
        setRows((prev) =>
            prev.map((r) =>
                r._key !== key
                    ? r
                    : {
                          ...r,
                          product_id: productId === '' ? '' : Number(productId),
                          batch_id: '',
                          _product: product,
                          _batches: (product?.batches ?? []).filter(
                              (b) => !wid || b.warehouse_id === wid,
                          ),
                      },
            ),
        );
    }

    function updateRow(key: number, patch: Partial<ItemRow>) {
        setRows((prev) =>
            prev.map((r) => (r._key === key ? { ...r, ...patch } : r)),
        );
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(
            StockTransferController.update.url({ stockTransfer: transfer.id }),
            {
                preserveScroll: true,
            },
        );
    }

    return (
        <AppLayout>
            <Head title={`Edit Transfer — ${transfer.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(
                                    StockTransferController.show.url({
                                        stockTransfer: transfer.id,
                                    }),
                                )
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Edit Transfer
                            </h1>
                            <p className="font-mono text-sm text-muted-foreground">
                                {transfer.reference_no}
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>
                                    From warehouse{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.from_warehouse_id}
                                    onValueChange={handleFromWarehouseChange}
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue />
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
                                {errors.from_warehouse_id && (
                                    <p className="text-xs text-destructive">
                                        {errors.from_warehouse_id}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label>
                                    To warehouse{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.to_warehouse_id}
                                    onValueChange={(v) =>
                                        setData('to_warehouse_id', v)
                                    }
                                    required
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses
                                            .filter(
                                                (w) =>
                                                    String(w.id) !==
                                                    data.from_warehouse_id,
                                            )
                                            .map((w) => (
                                                <SelectItem
                                                    key={w.id}
                                                    value={String(w.id)}
                                                >
                                                    {w.name}
                                                </SelectItem>
                                            ))}
                                    </SelectContent>
                                </Select>
                                {errors.to_warehouse_id && (
                                    <p className="text-xs text-destructive">
                                        {errors.to_warehouse_id}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label>Transfer date</Label>
                                <Input
                                    type="date"
                                    value={data.transfer_date}
                                    onChange={(e) =>
                                        setData('transfer_date', e.target.value)
                                    }
                                    required
                                />
                            </div>

                            <div className="space-y-1.5">
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
                                <h3 className="text-sm font-semibold">
                                    Items to transfer
                                </h3>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        setRows((p) => [
                                            ...p,
                                            {
                                                _key: ++rowKey,
                                                product_id: '',
                                                batch_id: '',
                                                quantity: 1,
                                            },
                                        ])
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

                            <div className="grid grid-cols-[1fr_1fr_90px_36px] gap-2 px-1">
                                {['Product', 'Batch (source)', 'Qty', ''].map(
                                    (h) => (
                                        <span
                                            key={h}
                                            className="text-xs font-medium text-muted-foreground"
                                        >
                                            {h}
                                        </span>
                                    ),
                                )}
                            </div>

                            <div className="space-y-2">
                                {rows.map((row) => (
                                    <div
                                        key={row._key}
                                        className="grid grid-cols-[1fr_1fr_90px_36px] items-center gap-2"
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
                                                <SelectValue placeholder="Select batch" />
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
                                            className="h-9 font-mono"
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

                        <div className="flex justify-end gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(
                                        StockTransferController.show.url({
                                            stockTransfer: transfer.id,
                                        }),
                                    )
                                }
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Save changes
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
