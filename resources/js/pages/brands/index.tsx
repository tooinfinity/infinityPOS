import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Image, Plus, Trash2 } from 'lucide-react';
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
import BrandController from '@/wayfinder/App/Http/Controllers/Products/BrandController';
import type { App, Inertia } from '@/wayfinder/types';

interface Filters {
    search?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    [key: string]: unknown;
}

type BrandRow = App.Models.Brand & {
    products_count?: number;
    logo?: { id: number; url: string; thumb: string; size: string } | null;
};

interface Props extends Inertia.SharedData {
    brands: Paginated<BrandRow>;
    filters: Filters;
}

export default function BrandsIndex({ brands, filters = {} }: Props) {
    const [deleteBrand, setDeleteBrand] = useState<App.Models.Brand | null>(
        null,
    );
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            BrandController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<BrandRow, unknown>[] = [
        {
            id: 'logo',
            size: 60,
            header: '',
            cell: ({ row }) => {
                const logo = row.original.logo;
                return logo ? (
                    <img
                        src={logo.thumb}
                        alt={row.original.name}
                        className="h-8 w-8 rounded-md object-cover"
                    />
                ) : (
                    <div className="flex h-8 w-8 items-center justify-center rounded-md bg-muted">
                        <Image className="h-4 w-4 text-muted-foreground" />
                    </div>
                );
            },
        },
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
                            BrandController.show.url({
                                brand: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.name}
                </button>
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
                                    BrandController.show.url({
                                        brand: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () =>
                                router.visit(
                                    BrandController.edit.url({
                                        brand: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteBrand(row.original),
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
            <Head title="Brands" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Brands
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage product brands
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(BrandController.create.url())
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New brand
                        </Button>
                    </div>

                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by name…"
                    />

                    <DataTable
                        columns={columns}
                        data={brands}
                        baseUrl={BrandController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteBrand && (
                    <ConfirmDialog
                        open={!!deleteBrand}
                        onOpenChange={(v) => !v && setDeleteBrand(null)}
                        deleteRoute={BrandController.destroy.url({
                            brand: deleteBrand.id,
                        })}
                        title={`Delete "${deleteBrand.name}"?`}
                        description="Products using this brand will have their brand removed."
                    />
                )}
            </div>
        </AppLayout>
    );
}
