import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import {
    CheckCircle2,
    MoreHorizontal,
    Plus,
    RotateCcw,
    Trash2,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog, { ActionDialog } from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import {
    PaymentStatusBadge,
    PurchaseStatusBadge,
} from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime, formatMoney } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import CancelPurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/CancelPurchaseController';
import PurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseController';
import PurchaseReturnController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseReturnController';
import ReceivePurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/ReceivePurchaseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Filters {
    search?: string;
    status?: string;
    payment_status?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    [key: string]: unknown;
}

interface Props extends Inertia.SharedData {
    purchases: Paginated<App.Models.Purchase>;
    filters: Filters;
}

function RowActions({
    purchase,
    onDelete,
    onReceive,
    onCancel,
}: {
    purchase: App.Models.Purchase;
    onDelete: (p: App.Models.Purchase) => void;
    onReceive: (p: App.Models.Purchase) => void;
    onCancel: (p: App.Models.Purchase) => void;
}) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="h-8 w-8">
                    <MoreHorizontal className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                    onClick={() =>
                        router.visit(
                            PurchaseController.show.url({
                                purchase: purchase.id,
                            }),
                        )
                    }
                >
                    View details
                </DropdownMenuItem>
                {(purchase.status === 'pending' ||
                    purchase.status === 'ordered') && (
                    <DropdownMenuItem onClick={() => onReceive(purchase)}>
                        <CheckCircle2 className="mr-2 h-3.5 w-3.5 text-emerald-500" />
                        Receive
                    </DropdownMenuItem>
                )}
                {purchase.status === 'received' && (
                    <DropdownMenuItem
                        onClick={() =>
                            router.visit(
                                PurchaseReturnController.create[
                                    '/purchase-returns/create/{purchase}'
                                ].url({
                                    purchase: purchase.id,
                                }),
                            )
                        }
                    >
                        <RotateCcw className="mr-2 h-3.5 w-3.5" /> Create return
                    </DropdownMenuItem>
                )}
                {(purchase.status === 'pending' ||
                    purchase.status === 'ordered' ||
                    purchase.status === 'received') && (
                    <DropdownMenuItem
                        className="text-amber-600"
                        onClick={() => onCancel(purchase)}
                    >
                        <XCircle className="mr-2 h-3.5 w-3.5" /> Cancel
                    </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                {purchase.status !== 'received' &&
                    purchase.status !== 'cancelled' && (
                        <DropdownMenuItem
                            className="text-destructive focus:text-destructive"
                            onClick={() => onDelete(purchase)}
                        >
                            <Trash2 className="mr-2 h-3.5 w-3.5" /> Delete
                        </DropdownMenuItem>
                    )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

export default function PurchasesIndex({ purchases, filters = {} }: Props) {
    const [deletePurchase, setDeletePurchase] =
        useState<App.Models.Purchase | null>(null);
    const [receivePurchase, setReceivePurchase] =
        useState<App.Models.Purchase | null>(null);
    const [cancelPurchase, setCancelPurchase] =
        useState<App.Models.Purchase | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            PurchaseController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.Purchase, unknown>[] = [
        {
            accessorKey: 'reference_no',
            size: 140,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Reference" />
            ),
            cell: ({ row }) => (
                <button
                    className="font-mono text-xs font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            PurchaseController.show.url({
                                purchase: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.reference_no}
                </button>
            ),
        },
        {
            id: 'supplier',
            size: 180,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Supplier" />
            ),
            cell: ({ row }) => (
                <span className="text-sm">
                    {row.original.supplier?.name ?? (
                        <span className="text-muted-foreground italic">—</span>
                    )}
                </span>
            ),
        },
        {
            id: 'warehouse',
            size: 140,
            header: 'Warehouse',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.warehouse?.name ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'status',
            size: 110,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Status" />
            ),
            cell: ({ row }) => (
                <PurchaseStatusBadge status={row.original.status} />
            ),
        },
        {
            accessorKey: 'payment_status',
            size: 110,
            header: 'Payment',
            cell: ({ row }) => (
                <PaymentStatusBadge status={row.original.payment_status} />
            ),
        },
        {
            accessorKey: 'total_amount',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Total" />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-sm font-medium">
                    {formatMoney(row.original.total_amount)}
                </span>
            ),
        },
        {
            id: 'due_amount',
            size: 120,
            header: 'Due',
            cell: ({ row }) => {
                const due =
                    typeof row.original.due_amount === 'number'
                        ? row.original.due_amount
                        : 0;
                return (
                    <span
                        className={
                            due > 0
                                ? 'font-mono text-sm font-medium text-red-600'
                                : 'font-mono text-sm text-muted-foreground'
                        }
                    >
                        {formatMoney(due)}
                    </span>
                );
            },
        },
        {
            accessorKey: 'created_at',
            size: 160,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Date" />
            ),
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {formatDateTime(row.original.created_at)}
                </span>
            ),
        },
        {
            id: 'actions',
            size: 50,
            cell: ({ row }) => (
                <RowActions
                    purchase={row.original}
                    onDelete={setDeletePurchase}
                    onReceive={setReceivePurchase}
                    onCancel={setCancelPurchase}
                />
            ),
        },
    ];

    return (
        <AppLayout>
            <Head title="Purchases" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Purchases
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage all purchase orders
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(PurchaseController.create.url())
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New purchase
                        </Button>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search reference, supplier…"
                            className="h-9 w-64"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) =>
                                e.key === 'Enter' && applyFilters({ search })
                            }
                            onBlur={() => applyFilters({ search })}
                        />
                        <Select
                            value={filters.status ?? ''}
                            onValueChange={(v) =>
                                applyFilters({
                                    status: v === 'all' ? undefined : v,
                                })
                            }
                        >
                            <SelectTrigger className="h-9 w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All statuses
                                </SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="ordered">Ordered</SelectItem>
                                <SelectItem value="received">
                                    Received
                                </SelectItem>
                                <SelectItem value="cancelled">
                                    Cancelled
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            value={filters.payment_status ?? ''}
                            onValueChange={(v) =>
                                applyFilters({
                                    payment_status: v === 'all' ? undefined : v,
                                })
                            }
                        >
                            <SelectTrigger className="h-9 w-40">
                                <SelectValue placeholder="Payment" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All payments
                                </SelectItem>
                                <SelectItem value="unpaid">Unpaid</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <DataTable
                        columns={columns}
                        data={purchases}
                        baseUrl={PurchaseController.index.url()}
                        filters={filters}
                    />
                </div>

                {receivePurchase && (
                    <ActionDialog
                        open={!!receivePurchase}
                        onOpenChange={(v) => !v && setReceivePurchase(null)}
                        actionRoute={ReceivePurchaseController.url({
                            purchase: receivePurchase.id,
                        })}
                        title="Receive this purchase?"
                        description={`Purchase ${receivePurchase.reference_no} will be marked as received and stock will be added.`}
                        confirmLabel="Receive"
                    />
                )}

                {cancelPurchase && (
                    <ActionDialog
                        open={!!cancelPurchase}
                        onOpenChange={(v) => !v && setCancelPurchase(null)}
                        actionRoute={CancelPurchaseController.url({
                            purchase: cancelPurchase.id,
                        })}
                        title="Cancel this purchase?"
                        description={
                            cancelPurchase.status === 'received'
                                ? `Cancelling will deduct stock.`
                                : `Purchase ${cancelPurchase.reference_no} will be cancelled.`
                        }
                        confirmLabel="Cancel purchase"
                        confirmVariant="destructive"
                    />
                )}

                {deletePurchase && (
                    <ConfirmDialog
                        open={!!deletePurchase}
                        onOpenChange={(v) => !v && setDeletePurchase(null)}
                        deleteRoute={PurchaseController.destroy.url({
                            purchase: deletePurchase.id,
                        })}
                        title={`Delete purchase ${deletePurchase.reference_no}?`}
                        description="This purchase and all its items will be permanently deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
