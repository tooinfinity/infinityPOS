import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    CheckCircle2,
    Clock,
    Loader2,
    Pencil,
    Trash2,
    XCircle,
} from 'lucide-react';
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
import { formatDate, formatDateTime } from '@/lib/formatters';
import CancelStockTransferController from '@/wayfinder/App/Http/Controllers/Inventory/CancelStockTransferController';
import CompleteStockTransferController from '@/wayfinder/App/Http/Controllers/Inventory/CompleteStockTransferController';
import StockTransferController from '@/wayfinder/App/Http/Controllers/Inventory/StockTransferController';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    transfer: App.Models.StockTransfer;
}

const STATUS_CONFIG: Record<
    string,
    { label: string; icon: React.ReactNode; cls: string }
> = {
    pending: {
        label: 'Pending',
        icon: <Clock className="h-3.5 w-3.5" />,
        cls: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    },
    completed: {
        label: 'Completed',
        icon: <CheckCircle2 className="h-3.5 w-3.5" />,
        cls: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    },
    cancelled: {
        label: 'Cancelled',
        icon: <XCircle className="h-3.5 w-3.5" />,
        cls: 'bg-zinc-100 text-zinc-500 ring-zinc-500/20',
    },
};

export default function StockTransferShow({ transfer }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [completing, setCompleting] = useState(false);
    const [cancelling, setCancelling] = useState(false);

    const status = STATUS_CONFIG[transfer.status] ?? STATUS_CONFIG.pending;
    const items = transfer.items ?? [];
    const isPending = transfer.status === 'pending';

    function handleComplete() {
        setCompleting(true);
        router.post(
            CompleteStockTransferController.url({
                stockTransfer: transfer.id,
            }),
            {},
            { onFinish: () => setCompleting(false) },
        );
    }

    function handleCancel() {
        setCancelling(true);
        router.post(
            CancelStockTransferController.url({ stockTransfer: transfer.id }),
            {},
            { onFinish: () => setCancelling(false) },
        );
    }

    return (
        <AppLayout>
            <Head title={`Transfer ${transfer.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={StockTransferController.index.url()}
                        title={transfer.reference_no}
                        badges={
                            <span
                                className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ${status.cls}`}
                            >
                                {status.icon}
                                {status.label}
                            </span>
                        }
                        subtitle={`Created ${formatDateTime(transfer.created_at)}`}
                        actions={
                            <>
                                {isPending && (
                                    <>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                router.visit(
                                                    StockTransferController.edit.url(
                                                        {
                                                            stockTransfer:
                                                                transfer.id,
                                                        },
                                                    ),
                                                )
                                            }
                                        >
                                            <Pencil className="mr-1.5 h-3.5 w-3.5" />{' '}
                                            Edit
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="border-amber-300 text-amber-700 hover:bg-amber-50"
                                            onClick={handleCancel}
                                            disabled={cancelling}
                                        >
                                            {cancelling && (
                                                <Loader2 className="mr-1.5 h-3.5 w-3.5 animate-spin" />
                                            )}
                                            Cancel transfer
                                        </Button>
                                        <Button
                                            size="sm"
                                            className="bg-emerald-600 hover:bg-emerald-700"
                                            onClick={handleComplete}
                                            disabled={completing}
                                        >
                                            {completing && (
                                                <Loader2 className="mr-1.5 h-3.5 w-3.5 animate-spin" />
                                            )}
                                            Complete
                                        </Button>
                                    </>
                                )}
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
                        {/* Items table */}
                        <div className="col-span-2">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base">
                                        Items
                                        <span className="ml-2 text-sm font-normal text-muted-foreground">
                                            {items.length} line
                                            {items.length !== 1 ? 's' : ''}
                                        </span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    {items.length === 0 ? (
                                        <div className="flex h-20 items-center justify-center text-sm text-muted-foreground">
                                            No items
                                        </div>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Product
                                                    </TableHead>
                                                    <TableHead>Batch</TableHead>
                                                    <TableHead className="text-right">
                                                        Qty
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {items.map((item) => (
                                                    <TableRow key={item.id}>
                                                        <TableCell>
                                                            <span className="text-sm font-medium">
                                                                {item.product
                                                                    ?.name ??
                                                                    '—'}
                                                            </span>
                                                            <span className="ml-2 font-mono text-xs text-muted-foreground">
                                                                {
                                                                    item.product
                                                                        ?.sku
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell>
                                                            <span className="font-mono text-xs text-muted-foreground">
                                                                {item.batch
                                                                    ?.batch_number ??
                                                                    '—'}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono font-medium">
                                                            {item.quantity}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-4">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base">
                                        Route
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3 text-sm">
                                    <div className="flex flex-col gap-1">
                                        <span className="text-xs text-muted-foreground">
                                            From
                                        </span>
                                        {transfer.from_warehouse ? (
                                            <Link
                                                href={WarehouseController.show.url(
                                                    {
                                                        warehouse:
                                                            transfer
                                                                .from_warehouse
                                                                .id,
                                                    },
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {transfer.from_warehouse.name}
                                            </Link>
                                        ) : (
                                            <span className="text-muted-foreground italic">
                                                —
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex items-center justify-center">
                                        <ArrowRight className="h-4 w-4 text-muted-foreground" />
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <span className="text-xs text-muted-foreground">
                                            To
                                        </span>
                                        {transfer.to_warehouse ? (
                                            <Link
                                                href={WarehouseController.show.url(
                                                    {
                                                        warehouse:
                                                            transfer
                                                                .to_warehouse
                                                                .id,
                                                    },
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {transfer.to_warehouse.name}
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
                                            Date
                                        </span>
                                        <span>
                                            {formatDate(transfer.transfer_date)}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Status
                                        </span>
                                        <span
                                            className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ${status.cls}`}
                                        >
                                            {status.icon} {status.label}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Created by
                                        </span>
                                        <span>
                                            {transfer.user?.name ?? '—'}
                                        </span>
                                    </div>
                                    {transfer.note && (
                                        <>
                                            <Separator />
                                            <div>
                                                <p className="mb-1 text-muted-foreground">
                                                    Note
                                                </p>
                                                <p className="whitespace-pre-wrap">
                                                    {transfer.note}
                                                </p>
                                            </div>
                                        </>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={StockTransferController.destroy.url({
                        stockTransfer: transfer.id,
                    })}
                    title={`Delete transfer ${transfer.reference_no}?`}
                    description="Only pending transfers that have not been completed can be deleted."
                    onSuccess={() =>
                        router.visit(StockTransferController.index.url())
                    }
                />
            </div>
        </AppLayout>
    );
}
