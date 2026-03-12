import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { CheckCircle2, MoreHorizontal, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog, { ActionDialog } from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import {
    PaymentStatusBadge,
    ReturnStatusBadge,
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
import CompletePurchaseReturnController from '@/wayfinder/App/Http/Controllers/Purchases/CompletePurchaseReturnController';
import PurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseController';
import PurchaseReturnController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseReturnController';
import type { App, Inertia } from '@/wayfinder/types';

interface Filters {
    search?: string;
    status?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    [key: string]: unknown;
}

type PurchaseReturnRow = App.Models.PurchaseReturn & {
    supplier?: App.Models.Supplier | null;
};

interface Props extends Inertia.SharedData {
    purchaseReturns: Paginated<PurchaseReturnRow>;
    filters: Filters;
}

export default function PurchaseReturnsIndex({
    purchaseReturns,
    filters = {},
}: Props) {
    const [completeReturn, setCompleteReturn] =
        useState<App.Models.PurchaseReturn | null>(null);
    const [deleteReturn, setDeleteReturn] =
        useState<App.Models.PurchaseReturn | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            PurchaseReturnController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<PurchaseReturnRow, unknown>[] = [
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
                            PurchaseReturnController.show.url({
                                purchaseReturn: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.reference_no}
                </button>
            ),
        },
        {
            id: 'purchase_ref',
            size: 140,
            header: 'Original purchase',
            cell: ({ row }) => (
                <button
                    className="font-mono text-xs text-muted-foreground hover:text-foreground hover:underline"
                    onClick={() =>
                        row.original.purchase &&
                        router.visit(
                            PurchaseController.show.url({
                                purchase: row.original.purchase.id,
                            }),
                        )
                    }
                >
                    {row.original.purchase?.reference_no ?? '—'}
                </button>
            ),
        },
        {
            id: 'supplier',
            size: 180,
            header: 'Supplier',
            cell: ({ row }) => (
                <span className="text-sm">
                    {row.original.supplier?.name ?? '—'}
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
                <ReturnStatusBadge status={row.original.status} />
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
            cell: ({ row }) => {
                const r = row.original;
                return (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="h-8 w-8"
                            >
                                <MoreHorizontal className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-44">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                onClick={() =>
                                    router.visit(
                                        PurchaseReturnController.show.url({
                                            purchaseReturn: r.id,
                                        }),
                                    )
                                }
                            >
                                View details
                            </DropdownMenuItem>
                            {r.status === 'pending' && (
                                <DropdownMenuItem
                                    onClick={() => setCompleteReturn(r)}
                                >
                                    <CheckCircle2 className="mr-2 h-3.5 w-3.5 text-emerald-500" />{' '}
                                    Complete
                                </DropdownMenuItem>
                            )}
                            <DropdownMenuSeparator />
                            {r.status === 'pending' && (
                                <DropdownMenuItem
                                    className="text-destructive focus:text-destructive"
                                    onClick={() => setDeleteReturn(r)}
                                >
                                    <Trash2 className="mr-2 h-3.5 w-3.5" />{' '}
                                    Delete
                                </DropdownMenuItem>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    return (
        <AppLayout>
            <Head title="Purchase Returns" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Purchase Returns
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage returns to suppliers
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search reference…"
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
                                <SelectItem value="completed">
                                    Completed
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <DataTable
                        columns={columns}
                        data={purchaseReturns}
                        baseUrl={PurchaseReturnController.index.url()}
                        filters={filters}
                    />
                </div>

                {completeReturn && (
                    <ActionDialog
                        open={!!completeReturn}
                        onOpenChange={(v) => !v && setCompleteReturn(null)}
                        actionRoute={CompletePurchaseReturnController.url({
                            purchaseReturn: completeReturn.id,
                        })}
                        title="Complete this return?"
                        description={`Return ${completeReturn.reference_no} will be completed and stock deducted.`}
                        confirmLabel="Complete"
                    />
                )}
                {deleteReturn && (
                    <ConfirmDialog
                        open={!!deleteReturn}
                        onOpenChange={(v) => !v && setDeleteReturn(null)}
                        deleteRoute={PurchaseReturnController.destroy.url({
                            purchaseReturn: deleteReturn.id,
                        })}
                        title={`Delete return ${deleteReturn.reference_no}?`}
                        description="This return will be permanently deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
