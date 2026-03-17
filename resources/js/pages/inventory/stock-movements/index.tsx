import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';

import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import FilterBar from '@/components/filter-bar';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import StockMovementController from '@/wayfinder/App/Http/Controllers/Inventory/StockMovementController';
import type { App, Inertia } from '@/wayfinder/types';
import { useState } from 'react';

interface Props extends Inertia.SharedData {
    movements: Paginated<App.Models.StockMovement>;
    filters: Record<string, unknown>;
}

const TYPE_COLORS: Record<string, string> = {
    in: 'text-emerald-600',
    out: 'text-red-600',
    transfer: 'text-blue-600',
    adjustment: 'text-amber-600',
};

export default function StockMovementsIndex({
    movements,
    filters = {},
}: Props) {
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            StockMovementController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.StockMovement, unknown>[] = [
        {
            id: 'product',
            size: 180,
            header: 'Product',
            cell: ({ row }) => (
                <div>
                    <p className="text-sm font-medium">
                        {row.original.product?.name ?? '—'}
                    </p>
                    <p className="font-mono text-xs text-muted-foreground">
                        {row.original.product?.sku}
                    </p>
                </div>
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
            id: 'batch',
            size: 140,
            header: 'Batch',
            cell: ({ row }) => (
                <span className="font-mono text-xs text-muted-foreground">
                    {row.original.batch?.batch_number ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'type',
            size: 100,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Type" />
            ),
            cell: ({ row }) => (
                <span
                    className={`text-xs font-semibold capitalize ${TYPE_COLORS[row.original.type] ?? ''}`}
                >
                    {row.original.type}
                </span>
            ),
        },
        {
            accessorKey: 'previous_quantity',
            size: 90,
            header: 'Before',
            cell: ({ row }) => (
                <span className="font-mono text-sm text-muted-foreground">
                    {row.original.previous_quantity}
                </span>
            ),
        },
        {
            accessorKey: 'quantity',
            size: 90,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Change" />
            ),
            cell: ({ row }) => {
                const q = row.original.quantity;
                return (
                    <span
                        className={`font-mono text-sm font-medium ${q >= 0 ? 'text-emerald-600' : 'text-red-600'}`}
                    >
                        {q >= 0 ? '+' : ''}
                        {q}
                    </span>
                );
            },
        },
        {
            accessorKey: 'current_quantity',
            size: 90,
            header: 'After',
            cell: ({ row }) => (
                <span className="font-mono text-sm font-medium">
                    {row.original.current_quantity}
                </span>
            ),
        },
        {
            accessorKey: 'note',
            size: 200,
            header: 'Note',
            cell: ({ row }) => (
                <span className="line-clamp-1 text-sm text-muted-foreground">
                    {row.original.note ?? '—'}
                </span>
            ),
        },
        {
            id: 'user',
            size: 120,
            header: 'By',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.user?.name ?? '—'}
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
    ];

    return (
        <AppLayout>
            <Head title="Stock Movements" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div>
                        <h1 className="text-xl font-semibold tracking-tight">
                            Stock Movements
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Read-only audit trail of all stock changes
                        </p>
                    </div>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by note or reference…"
                    >
                        <Select
                            value={(filters.type as string) ?? ''}
                            onValueChange={(v) =>
                                applyFilters({
                                    type: v === 'all' ? undefined : v,
                                })
                            }
                        >
                            <SelectTrigger className="h-9 w-36">
                                <SelectValue placeholder="Type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All types</SelectItem>
                                <SelectItem value="in">In</SelectItem>
                                <SelectItem value="out">Out</SelectItem>
                                <SelectItem value="transfer">
                                    Transfer
                                </SelectItem>
                                <SelectItem value="adjustment">
                                    Adjustment
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FilterBar>
                    <DataTable
                        columns={columns}
                        data={movements}
                        baseUrl={StockMovementController.index.url()}
                        filters={filters}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
