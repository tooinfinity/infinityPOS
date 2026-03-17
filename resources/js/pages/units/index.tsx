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
import UnitController from '@/wayfinder/App/Http/Controllers/Products/UnitController';
import type { App, Inertia } from '@/wayfinder/types';

type UnitRow = App.Models.Unit & { products_count?: number };

interface Props extends Inertia.SharedData {
    units: Paginated<UnitRow>;
    filters: Record<string, unknown>;
}

export default function UnitsIndex({ units, filters = {} }: Props) {
    const [deleteUnit, setDeleteUnit] = useState<App.Models.Unit | null>(null);
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            UnitController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<UnitRow, unknown>[] = [
        {
            accessorKey: 'name',
            size: 180,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Name" />
            ),
            cell: ({ row }) => (
                <button
                    className="text-left text-sm font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            UnitController.show.url({ unit: row.original.id }),
                        )
                    }
                >
                    {row.original.name}
                </button>
            ),
        },
        {
            accessorKey: 'short_name',
            size: 100,
            header: 'Abbreviation',
            cell: ({ row }) => (
                <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs font-medium">
                    {row.original.short_name}
                </span>
            ),
        },
        {
            accessorKey: 'products_count',
            size: 100,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Products" />
            ),
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.products_count ?? 0}
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
                                    UnitController.show.url({
                                        unit: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () =>
                                router.visit(
                                    UnitController.edit.url({
                                        unit: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteUnit(row.original),
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
            <Head title="Units" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Units of Measure
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Define measurement units for products
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(UnitController.create.url())
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New unit
                        </Button>
                    </div>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by name or abbreviation…"
                    />
                    <DataTable
                        columns={columns}
                        data={units}
                        baseUrl={UnitController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteUnit && (
                    <ConfirmDialog
                        open={!!deleteUnit}
                        onOpenChange={(v) => !v && setDeleteUnit(null)}
                        deleteRoute={UnitController.destroy.url({
                            unit: deleteUnit.id,
                        })}
                        title={`Delete "${deleteUnit.name}"?`}
                        description="Products using this unit will be reassigned to the default unit (Piece) if available."
                    />
                )}
            </div>
        </AppLayout>
    );
}
