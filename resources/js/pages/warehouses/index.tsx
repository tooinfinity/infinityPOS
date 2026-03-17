import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ActionMenu from '@/components/action-menu';
import ConfirmDialog from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import FilterBar from '@/components/filter-bar';
import { ActiveBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';

type WarehouseRow = App.Models.Warehouse & {
    batches_count?: number;
    purchases_count?: number;
    sales_count?: number;
};

interface Props extends Inertia.SharedData {
    warehouses: Paginated<WarehouseRow>;
    filters: Record<string, unknown>;
}

export default function WarehousesIndex({ warehouses, filters = {} }: Props) {
    const [deleteWarehouse, setDeleteWarehouse] =
        useState<App.Models.Warehouse | null>(null);
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            WarehouseController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<WarehouseRow, unknown>[] = [
        {
            accessorKey: 'name',
            size: 200,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Name" />
            ),
            cell: ({ row }) => (
                <button
                    className="text-left text-sm font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            WarehouseController.show.url({
                                warehouse: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.name}
                </button>
            ),
        },
        {
            accessorKey: 'code',
            size: 90,
            header: 'Code',
            cell: ({ row }) => (
                <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs font-medium">
                    {row.original.code}
                </span>
            ),
        },
        {
            accessorKey: 'city',
            size: 130,
            header: 'City',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.city ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'batches_count',
            size: 90,
            header: 'Batches',
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.batches_count ?? 0}
                </span>
            ),
        },
        {
            accessorKey: 'purchases_count',
            size: 100,
            header: 'Purchases',
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.purchases_count ?? 0}
                </span>
            ),
        },
        {
            accessorKey: 'sales_count',
            size: 80,
            header: 'Sales',
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.sales_count ?? 0}
                </span>
            ),
        },
        {
            accessorKey: 'is_active',
            size: 90,
            header: 'Status',
            cell: ({ row }) => <ActiveBadge active={row.original.is_active} />,
        },
        {
            accessorKey: 'created_at',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Created" />
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
                                    WarehouseController.show.url({
                                        warehouse: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () =>
                                router.visit(
                                    WarehouseController.edit.url({
                                        warehouse: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteWarehouse(row.original),
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
            <Head title="Warehouses" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Warehouses
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage storage locations
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(WarehouseController.create.url())
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New warehouse
                        </Button>
                    </div>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by name or code…"
                    />
                    <DataTable
                        columns={columns}
                        data={warehouses}
                        baseUrl={WarehouseController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteWarehouse && (
                    <ConfirmDialog
                        open={!!deleteWarehouse}
                        onOpenChange={(v) => !v && setDeleteWarehouse(null)}
                        deleteRoute={WarehouseController.destroy.url({
                            warehouse: deleteWarehouse.id,
                        })}
                        title={`Delete "${deleteWarehouse.name}"?`}
                        description="This warehouse can only be deleted if it has no associated batches, movements, purchases or sales."
                    />
                )}
            </div>
        </AppLayout>
    );
}
