import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, BarChart3, Layers, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatDateTime, formatMoney } from '@/lib/formatters';
import BatchController from '@/wayfinder/App/Http/Controllers/Products/BatchController';
import ProductController from '@/wayfinder/App/Http/Controllers/Products/ProductController';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    batch: App.Models.Batch;
}

export default function BatchShow({ batch }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const movements = batch.stock_movements ?? [];

    const isExpired =
        batch.expires_at && new Date(batch.expires_at) < new Date();
    const isExpiringSoon =
        batch.expires_at &&
        !isExpired &&
        new Date(batch.expires_at) <
            new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);

    return (
        <AppLayout>
            <Head title={`Batch ${batch.batch_number}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={BatchController.index.url()}
                        title={batch.batch_number ?? '—'}
                        badges={
                            <>
                                {isExpired && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-600/20">
                                        <AlertTriangle className="h-3 w-3" />{' '}
                                        Expired
                                    </span>
                                )}
                                {isExpiringSoon && !isExpired && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">
                                        <AlertTriangle className="h-3 w-3" />{' '}
                                        Expiring soon
                                    </span>
                                )}
                                {batch.quantity === 0 && (
                                    <span className="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-500 ring-1 ring-zinc-500/20">
                                        Out of stock
                                    </span>
                                )}
                            </>
                        }
                        subtitle={`Received ${formatDate(batch.created_at)}`}
                        actions={
                            <>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.visit(
                                            BatchController.edit.url({
                                                batch: batch.id,
                                            }),
                                        )
                                    }
                                >
                                    <Pencil className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Edit
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    onClick={() => setDeleteOpen(true)}
                                >
                                    <Trash2 className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Delete
                                </Button>
                            </>
                        }
                    />

                    <div className="grid grid-cols-3 gap-6">
                        <div className="col-span-2">
                            {movements.length > 0 && (
                                <Card>
                                    <CardHeader className="pb-3">
                                        <CardTitle className="flex items-center gap-2 text-base">
                                            <BarChart3 className="h-4 w-4" />{' '}
                                            Recent movements
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="p-0">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Type</TableHead>
                                                    <TableHead className="text-right">
                                                        Before
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Change
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        After
                                                    </TableHead>
                                                    <TableHead>Note</TableHead>
                                                    <TableHead>Date</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {movements.map((m) => (
                                                    <TableRow key={m.id}>
                                                        <TableCell>
                                                            <span className="text-xs font-medium text-muted-foreground capitalize">
                                                                {m.type}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono text-sm text-muted-foreground">
                                                            {
                                                                m.previous_quantity
                                                            }
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono text-sm">
                                                            <span
                                                                className={
                                                                    m.quantity >=
                                                                    0
                                                                        ? 'text-emerald-600'
                                                                        : 'text-red-600'
                                                                }
                                                            >
                                                                {m.quantity >= 0
                                                                    ? '+'
                                                                    : ''}
                                                                {m.quantity}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono text-sm font-medium">
                                                            {m.current_quantity}
                                                        </TableCell>
                                                        <TableCell className="max-w-[160px] truncate text-sm text-muted-foreground">
                                                            {m.note ?? '—'}
                                                        </TableCell>
                                                        <TableCell className="text-sm text-muted-foreground">
                                                            {formatDateTime(
                                                                m.created_at,
                                                            )}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </CardContent>
                                </Card>
                            )}
                        </div>

                        <div className="space-y-4">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Layers className="h-4 w-4" /> Batch
                                        details
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Product
                                        </span>
                                        {batch.product ? (
                                            <Link
                                                href={ProductController.show.url(
                                                    {
                                                        product:
                                                            batch.product.id,
                                                    },
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {batch.product.name}
                                            </Link>
                                        ) : (
                                            <span className="text-muted-foreground italic">
                                                —
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Warehouse
                                        </span>
                                        {batch.warehouse ? (
                                            <Link
                                                href={WarehouseController.show.url(
                                                    {
                                                        warehouse:
                                                            batch.warehouse.id,
                                                    },
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {batch.warehouse.name}
                                            </Link>
                                        ) : (
                                            <span className="text-muted-foreground italic">
                                                —
                                            </span>
                                        )}
                                    </div>
                                    <Separator />
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Current qty
                                        </span>
                                        <span
                                            className={`font-mono font-semibold ${batch.quantity === 0 ? 'text-red-600' : ''}`}
                                        >
                                            {batch.quantity}
                                            <span className="ml-1 text-xs font-normal text-muted-foreground">
                                                {
                                                    batch.product?.unit
                                                        ?.short_name
                                                }
                                            </span>
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Unit cost
                                        </span>
                                        <span className="font-mono">
                                            {formatMoney(batch.cost_amount)}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Expiry date
                                        </span>
                                        <span
                                            className={
                                                isExpired
                                                    ? 'font-medium text-red-600'
                                                    : isExpiringSoon
                                                      ? 'font-medium text-amber-600'
                                                      : ''
                                            }
                                        >
                                            {batch.expires_at
                                                ? formatDate(batch.expires_at)
                                                : 'No expiry'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Received
                                        </span>
                                        <span>
                                            {formatDate(batch.created_at)}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={BatchController.destroy.url({
                        batch: batch.id,
                    })}
                    title={`Delete batch ${batch.batch_number}?`}
                    description="This batch can only be deleted if it has no associated stock movements."
                    onSuccess={() => router.visit(BatchController.index.url())}
                />
            </div>
        </AppLayout>
    );
}
