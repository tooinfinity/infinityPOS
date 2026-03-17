import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ActionMenu from '@/components/action-menu';
import ConfirmDialog from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import FilterBar from '@/components/filter-bar';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import StockTransferController from '@/wayfinder/App/Http/Controllers/Inventory/StockTransferController';
import type { App, Inertia } from '@/wayfinder/types';

const STATUS_STYLES: Record<string, string> = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    completed: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    cancelled: 'bg-zinc-100 text-zinc-500 ring-zinc-500/20',
};

interface Props extends Inertia.SharedData {
    transfers: Paginated<App.Models.StockTransfer>;
    filters: Record<string, unknown>;
}

export default function StockTransfersIndex({
    transfers,
    filters = {},
}: Props) {
    const [deleteTransfer, setDeleteTransfer] =
        useState<App.Models.StockTransfer | null>(null);
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            StockTransferController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.StockTransfer>[] = [
        {
            accessorKey: 'reference_no',
            size: 160,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Reference" />
            ),
            cell: ({ row }) => (
                <button
                    className="font-mono text-xs font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            StockTransferController.show.url({
                                stockTransfer: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.reference_no}
                </button>
            ),
        },
        {
            id: 'from_warehouse',
            size: 150,
            header: 'From',
            cell: ({ row }) => (
                <span className="text-sm">
                    {row.original.from_warehouse?.name ?? '—'}
                </span>
            ),
        },
        {
            id: 'to_warehouse',
            size: 150,
            header: 'To',
            cell: ({ row }) => (
                <span className="text-sm">
                    {row.original.to_warehouse?.name ?? '—'}
                </span>
            ),
        },
        {
            id: 'items_count',
            size: 80,
            header: 'Items',
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.items?.length ?? 0}
                </span>
            ),
        },
        {
            accessorKey: 'status',
            size: 110,
            header: 'Status',
            cell: ({ row }) => (
                <span
                    className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ${STATUS_STYLES[row.original.status] ?? ''}`}
                >
                    {row.original.status.charAt(0).toUpperCase() +
                        row.original.status.slice(1)}
                </span>
            ),
        },
        {
            accessorKey: 'transfer_date',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Date" />
            ),
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {formatDate(row.original.transfer_date)}
                </span>
            ),
        },
        {
            id: 'user',
            size: 130,
            header: 'Created by',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.user?.name ?? '—'}
                </span>
            ),
        },
        {
            id: 'actions',
            size: 50,
            cell: ({ row }) => {
                const isPending = row.original.status === 'pending';
                return (
                    <ActionMenu
                        items={[
                            {
                                label: 'View',
                                onClick: () =>
                                    router.visit(
                                        StockTransferController.show.url({
                                            stockTransfer: row.original.id,
                                        }),
                                    ),
                            },
                            ...(isPending
                                ? [
                                      {
                                          label: 'Edit',
                                          onClick: () =>
                                              router.visit(
                                                  StockTransferController.edit.url(
                                                      {
                                                          stockTransfer:
                                                              row.original.id,
                                                      },
                                                  ),
                                              ),
                                      },
                                  ]
                                : []),
                            {
                                label: 'Delete',
                                onClick: () => setDeleteTransfer(row.original),
                                icon: <Trash2 className="h-3.5 w-3.5" />,
                                variant: 'destructive' as const,
                            },
                        ]}
                    />
                );
            },
        },
    ];

    return (
        <AppLayout>
            <Head title="Stock Transfers" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Stock Transfers
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Move stock between warehouses
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(
                                    StockTransferController.create.url(),
                                )
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New transfer
                        </Button>
                    </div>

                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by reference…"
                    >
                        <Select
                            value={(filters.status as string) ?? ''}
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
                                <SelectItem value="cancelled">
                                    Cancelled
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FilterBar>

                    <DataTable
                        columns={columns}
                        data={transfers}
                        baseUrl={StockTransferController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteTransfer && (
                    <ConfirmDialog
                        open={!!deleteTransfer}
                        onOpenChange={(v) => !v && setDeleteTransfer(null)}
                        deleteRoute={StockTransferController.destroy.url({
                            stockTransfer: deleteTransfer.id,
                        })}
                        title={`Delete transfer ${deleteTransfer.reference_no}?`}
                        description="Only pending transfers that have not been completed can be deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
