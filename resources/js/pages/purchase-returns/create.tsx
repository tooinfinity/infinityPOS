import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatAmount } from '@/lib/formatters';
import PurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseController';
import PurchaseReturnController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseReturnController';
import type { App, Inertia } from '@/wayfinder/types';

interface ReturnableItem {
    purchase_item_id: number;
    product_id: number;
    batch_id: number;
    product_name: string;
    product_sku: string;
    batch_number: string;
    unit_cost: number;
    unit_short_name: string;
    max_quantity: number;
}

interface FormData {
    purchase_id: string;
    note: string;
    items: Array<{
        purchase_item_id: number;
        product_id: number;
        batch_id: number;
        quantity: number;
        unit_cost: number;
    }>;
}

interface Props extends Inertia.SharedData {
    purchase: App.Models.Purchase;
    returnableItems: ReturnableItem[];
}

export default function PurchaseReturnCreate({
    purchase,
    returnableItems,
}: Props) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        purchase_id: String(purchase.id),
        note: '',
        items: returnableItems.map((item) => ({
            purchase_item_id: item.purchase_item_id,
            product_id: item.product_id,
            batch_id: item.batch_id,
            quantity: 0,
            unit_cost: item.unit_cost,
        })),
    });

    function updateItemQty(index: number, qty: number) {
        const max = returnableItems[index].max_quantity;
        const updated = [...data.items];
        updated[index] = {
            ...updated[index],
            quantity: Math.max(0, Math.min(qty, max)),
        };
        setData('items', updated);
    }

    const activeItems = data.items.filter((i) => i.quantity > 0);
    const total = activeItems.reduce(
        (sum, i) => sum + i.quantity * i.unit_cost,
        0,
    );

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(PurchaseReturnController.store.url(), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title={`Return — ${purchase.reference_no}`} />
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
                                New Return to Supplier
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Against purchase{' '}
                                <span className="font-mono font-medium text-foreground">
                                    {purchase.reference_no}
                                </span>
                                {purchase.supplier && (
                                    <> · {purchase.supplier.name}</>
                                )}
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit}>
                        <div className="grid grid-cols-3 gap-6">
                            <div className="col-span-2">
                                <Card>
                                    <CardHeader className="pb-3">
                                        <CardTitle className="text-base">
                                            Select items to return
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="p-0">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Product
                                                    </TableHead>
                                                    <TableHead>Batch</TableHead>
                                                    <TableHead className="text-right">
                                                        Max
                                                    </TableHead>
                                                    <TableHead className="w-28 text-right">
                                                        Qty to return
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Subtotal
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {returnableItems.map(
                                                    (item, idx) => {
                                                        const formItem =
                                                            data.items[idx];
                                                        return (
                                                            <TableRow
                                                                key={
                                                                    item.purchase_item_id
                                                                }
                                                            >
                                                                <TableCell>
                                                                    <p className="text-sm font-medium">
                                                                        {
                                                                            item.product_name
                                                                        }
                                                                    </p>
                                                                    <p className="font-mono text-xs text-muted-foreground">
                                                                        {
                                                                            item.product_sku
                                                                        }
                                                                    </p>
                                                                </TableCell>
                                                                <TableCell>
                                                                    <span className="font-mono text-sm text-muted-foreground">
                                                                        {
                                                                            item.batch_number
                                                                        }
                                                                    </span>
                                                                </TableCell>
                                                                <TableCell className="text-right text-sm text-muted-foreground tabular-nums">
                                                                    {
                                                                        item.max_quantity
                                                                    }{' '}
                                                                    <span className="text-xs">
                                                                        {
                                                                            item.unit_short_name
                                                                        }
                                                                    </span>
                                                                </TableCell>
                                                                <TableCell className="text-right">
                                                                    <Input
                                                                        type="number"
                                                                        min={0}
                                                                        max={
                                                                            item.max_quantity
                                                                        }
                                                                        className="ml-auto h-8 w-20 text-right font-mono"
                                                                        value={
                                                                            formItem.quantity ||
                                                                            ''
                                                                        }
                                                                        placeholder="0"
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateItemQty(
                                                                                idx,
                                                                                Number(
                                                                                    e
                                                                                        .target
                                                                                        .value,
                                                                                ),
                                                                            )
                                                                        }
                                                                    />
                                                                </TableCell>
                                                                <TableCell className="text-right font-mono text-sm">
                                                                    {formItem.quantity >
                                                                    0
                                                                        ? formatAmount(
                                                                              formItem.quantity *
                                                                                  item.unit_cost,
                                                                          )
                                                                        : '—'}
                                                                </TableCell>
                                                            </TableRow>
                                                        );
                                                    },
                                                )}
                                            </TableBody>
                                        </Table>
                                    </CardContent>
                                </Card>
                            </div>

                            <div className="space-y-4">
                                <Card>
                                    <CardHeader className="pb-3">
                                        <CardTitle className="text-base">
                                            Details
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="space-y-1.5">
                                            <Label>Note</Label>
                                            <Textarea
                                                rows={3}
                                                className="resize-none"
                                                placeholder="Reason for return to supplier…"
                                                value={data.note}
                                                onChange={(e) =>
                                                    setData(
                                                        'note',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>

                                        <Separator />

                                        <div className="flex justify-between font-semibold">
                                            <span>Return total</span>
                                            <span className="font-mono">
                                                {formatAmount(total)}
                                            </span>
                                        </div>

                                        {errors.items && (
                                            <p className="text-xs text-destructive">
                                                {errors.items as string}
                                            </p>
                                        )}

                                        <Button
                                            type="submit"
                                            className="w-full"
                                            disabled={
                                                processing ||
                                                activeItems.length === 0
                                            }
                                        >
                                            {processing && (
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            )}
                                            Create return
                                        </Button>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
