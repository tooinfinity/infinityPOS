import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { AlertTriangle, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ActionMenu from '@/components/action-menu';
import ConfirmDialog from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import FilterBar from '@/components/filter-bar';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatMoney } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import BatchController from '@/wayfinder/App/Http/Controllers/Products/BatchController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    batches: Paginated<App.Models.Batch>;
    filters: Record<string, unknown>;
}

export default function BatchesIndex({ batches, filters = {} }: Props) {
    const [deleteBatch, setDeleteBatch] = useState<App.Models.Batch | null>(
        null,
    );
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            BatchController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.Batch, unknown>[] = [
        {
            accessorKey: 'batch_number',
            size: 180,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Batch #" />
            ),
            cell: ({ row }) => (
                <button
                    className="font-mono text-xs font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            BatchController.show.url({
                                batch: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.batch_number}
                </button>
            ),
        },
        {
            id: 'product',
            size: 200,
            header: 'Product',
            cell: ({ row }) => (
                <span className="text-sm font-medium">
                    {row.original.product?.name ?? '—'}
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
            accessorKey: 'quantity',
            size: 100,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Qty" />
            ),
            cell: ({ row }) => (
                <span
                    className={`font-mono text-sm font-medium ${row.original.quantity === 0 ? 'text-red-500' : ''}`}
                >
                    {row.original.quantity}
                </span>
            ),
        },
        {
            accessorKey: 'cost_amount',
            size: 120,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Unit cost" />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-sm">
                    {formatMoney(row.original.cost_amount)}
                </span>
            ),
        },
        {
            accessorKey: 'expires_at',
            size: 130,
            header: 'Expiry',
            cell: ({ row }) => {
                const expiresAt = row.original.expires_at;
                if (!expiresAt)
                    return (
                        <span className="text-sm text-muted-foreground">—</span>
                    );
                const isExpired = new Date(expiresAt) < new Date();
                const isExpiringSoon =
                    !isExpired &&
                    new Date(expiresAt) <
                        new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
                return (
                    <span
                        className={
                            isExpired
                                ? 'flex items-center gap-1 text-xs font-medium text-red-600'
                                : isExpiringSoon
                                  ? 'flex items-center gap-1 text-xs font-medium text-amber-600'
                                  : 'text-sm text-muted-foreground'
                        }
                    >
                        {(isExpired || isExpiringSoon) && (
                            <AlertTriangle className="h-3 w-3 shrink-0" />
                        )}
                        {formatDate(expiresAt)}
                    </span>
                );
            },
        },
        {
            accessorKey: 'created_at',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Received" />
            ),
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {formatDate(row.original.created_at)}
                </span>
            ),
        },
        {
            id: 'actions',
            size: 50,
            cell: ({ row }) => (
                <ActionMenu
                    items={[
                        {
                            label: 'View',
                            onClick: () =>
                                router.visit(
                                    BatchController.show.url({
                                        batch: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () =>
                                router.visit(
                                    BatchController.edit.url({
                                        batch: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteBatch(row.original),
                            icon: <Trash2 className="h-3.5 w-3.5" />,
                            variant: 'destructive',
                        },
                    ]}
                />
            ),
        },
    ];

    return (
        <AppLayout>
            <Head title="Batches" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Batches
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage stock batches across warehouses
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(BatchController.create.url())
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New batch
                        </Button>
                    </div>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by batch number or product…"
                    />
                    <DataTable
                        columns={columns}
                        data={batches}
                        baseUrl={BatchController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteBatch && (
                    <ConfirmDialog
                        open={!!deleteBatch}
                        onOpenChange={(v) => !v && setDeleteBatch(null)}
                        deleteRoute={BatchController.destroy.url({
                            batch: deleteBatch.id,
                        })}
                        title={`Delete batch ${deleteBatch.batch_number}?`}
                        description="This batch can only be deleted if it has no associated movements, purchases or sales."
                    />
                )}
            </div>
        </AppLayout>
    );
}
