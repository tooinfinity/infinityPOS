import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2 } from 'lucide-react';

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
import AppLayout from '@/layouts/app-layout';
import BatchController from '@/wayfinder/App/Http/Controllers/Products/BatchController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    batch: App.Models.Batch;
    products: App.Models.Product[];
    warehouses: App.Models.Warehouse[];
}

export default function BatchEdit({ batch, products, warehouses }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        product_id: String(batch.product_id),
        warehouse_id: String(batch.warehouse_id),
        batch_number: batch.batch_number ?? '',
        cost_amount: batch.cost_amount,
        quantity: batch.quantity,
        expires_at: batch.expires_at
            ? String(batch.expires_at).slice(0, 10)
            : '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(BatchController.update.url({ batch: batch.id }), {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout>
            <Head title={`Edit Batch — ${batch.batch_number}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(
                                    BatchController.show.url({
                                        batch: batch.id,
                                    }),
                                )
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Edit Batch
                            </h1>
                            <p className="font-mono text-sm text-muted-foreground">
                                {batch.batch_number}
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="max-w-lg space-y-4"
                    >
                        <div className="space-y-1.5">
                            <Label>
                                Product{' '}
                                <span className="text-destructive">*</span>
                            </Label>
                            <Select
                                value={data.product_id}
                                onValueChange={(v) => setData('product_id', v)}
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {products.map((p) => (
                                        <SelectItem
                                            key={p.id}
                                            value={String(p.id)}
                                        >
                                            {p.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.product_id && (
                                <p className="text-xs text-destructive">
                                    {errors.product_id}
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
                            {errors.warehouse_id && (
                                <p className="text-xs text-destructive">
                                    {errors.warehouse_id}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Batch number</Label>
                            <Input
                                value={data.batch_number}
                                onChange={(e) =>
                                    setData('batch_number', e.target.value)
                                }
                                className="font-mono"
                            />
                            {errors.batch_number && (
                                <p className="text-xs text-destructive">
                                    {errors.batch_number}
                                </p>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>Unit cost</Label>
                                <Input
                                    type="number"
                                    min={0}
                                    className="font-mono"
                                    value={data.cost_amount}
                                    onChange={(e) =>
                                        setData(
                                            'cost_amount',
                                            Number(e.target.value),
                                        )
                                    }
                                />
                                {errors.cost_amount && (
                                    <p className="text-xs text-destructive">
                                        {errors.cost_amount}
                                    </p>
                                )}
                            </div>
                            <div className="space-y-1.5">
                                <Label>Quantity</Label>
                                <Input
                                    type="number"
                                    min={0}
                                    className="font-mono"
                                    value={data.quantity}
                                    onChange={(e) =>
                                        setData(
                                            'quantity',
                                            Number(e.target.value),
                                        )
                                    }
                                />
                                {errors.quantity && (
                                    <p className="text-xs text-destructive">
                                        {errors.quantity}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label>Expiry date</Label>
                            <Input
                                type="date"
                                value={data.expires_at}
                                onChange={(e) =>
                                    setData('expires_at', e.target.value)
                                }
                            />
                        </div>

                        <div className="flex gap-3 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(
                                        BatchController.show.url({
                                            batch: batch.id,
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
