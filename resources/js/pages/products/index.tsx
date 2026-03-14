import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ActionMenu from '@/components/action-menu';
import ConfirmDialog from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import FilterBar from '@/components/filter-bar';
import { ActiveBadge, StockStatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatAmount } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import ProductController from '@/wayfinder/App/Http/Controllers/Products/ProductController';
import type { App, Inertia } from '@/wayfinder/types';
import ProductFormModal from './partials/product-form-modal';

interface Filters {
    search?: string;
    category_id?: string;
    brand_id?: string;
    track_inventory?: string;
    low_stock?: string;
    out_of_stock?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    [key: string]: unknown;
}

interface Props extends Inertia.SharedData {
    products: Paginated<App.Models.Product>;
    categories: App.Models.Category[];
    brands: App.Models.Brand[];
    units: App.Models.Unit[];
    filters: Filters;
    perPage: number;
}

export default function ProductsIndex({
    products,
    categories,
    brands,
    units,
    filters = {},
}: Props) {
    const [createOpen, setCreateOpen] = useState(false);
    const [editProduct, setEditProduct] = useState<App.Models.Product | null>(
        null,
    );
    const [deleteProduct, setDeleteProduct] =
        useState<App.Models.Product | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            ProductController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.Product>[] = [
        {
            accessorKey: 'name',
            size: 220,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Product" />
            ),
            cell: ({ row }) => {
                const p = row.original;
                return (
                    <button
                        className="text-left"
                        onClick={() =>
                            router.visit(
                                ProductController.show.url({ product: p.id }),
                            )
                        }
                    >
                        <p className="text-sm font-medium text-primary hover:underline">
                            {p.name}
                        </p>
                        <p className="font-mono text-xs text-muted-foreground">
                            {p.sku}
                        </p>
                    </button>
                );
            },
        },
        {
            id: 'category',
            size: 130,
            header: 'Category',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.category?.name ?? '—'}
                </span>
            ),
        },
        {
            id: 'brand',
            size: 120,
            header: 'Brand',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.brand?.name ?? '—'}
                </span>
            ),
        },
        {
            id: 'unit',
            size: 80,
            header: 'Unit',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.unit?.short_name ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'selling_price',
            size: 110,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Price" />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-sm">
                    {formatAmount(row.original.selling_price)}
                </span>
            ),
        },
        {
            accessorKey: 'stock_quantity',
            size: 110,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Stock" />
            ),
            cell: ({ row }) => {
                const p = row.original as App.Models.Product & {
                    stock_quantity?: number;
                };
                if (!p.track_inventory) {
                    return (
                        <span className="text-xs text-muted-foreground italic">
                            Untracked
                        </span>
                    );
                }
                const qty = p.stock_quantity ?? 0;
                const alertQty = p.alert_quantity ?? 0;
                const status: 'in_stock' | 'low_stock' | 'out_of_stock' =
                    qty === 0
                        ? 'out_of_stock'
                        : alertQty > 0 && qty <= alertQty
                          ? 'low_stock'
                          : 'in_stock';
                return (
                    <div className="flex items-center gap-2">
                        <StockStatusBadge status={status} />
                        <span className="font-mono text-sm text-muted-foreground">
                            {qty}
                        </span>
                    </div>
                );
            },
        },
        {
            accessorKey: 'track_inventory',
            size: 90,
            header: 'Tracking',
            cell: ({ row }) => (
                <ActiveBadge active={row.original.track_inventory} />
            ),
        },
        {
            id: 'actions',
            size: 50,
            cell: ({ row }) => (
                <ActionMenu
                    items={[
                        {
                            label: 'View details',
                            onClick: () =>
                                router.visit(
                                    ProductController.show.url({
                                        product: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () => setEditProduct(row.original),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteProduct(row.original),
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
            <Head title="Products" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Products
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage your product catalogue
                            </p>
                        </div>
                        <Button onClick={() => setCreateOpen(true)}>
                            <Plus className="mr-2 h-4 w-4" /> New product
                        </Button>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <FilterBar
                            search={search}
                            onSearchChange={setSearch}
                            onSearch={() => applyFilters({ search })}
                            placeholder="Search name, SKU…"
                        />

                        <Select
                            value={filters.category_id?.toString() ?? ''}
                            onValueChange={(v) =>
                                applyFilters({ category_id: v || undefined })
                            }
                        >
                            <SelectTrigger className="h-9 w-40">
                                <SelectValue placeholder="Category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All categories
                                </SelectItem>
                                {categories.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.brand_id?.toString() ?? ''}
                            onValueChange={(v) =>
                                applyFilters({ brand_id: v || undefined })
                            }
                        >
                            <SelectTrigger className="h-9 w-40">
                                <SelectValue placeholder="Brand" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All brands</SelectItem>
                                {brands.map((b) => (
                                    <SelectItem key={b.id} value={String(b.id)}>
                                        {b.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.track_inventory?.toString() ?? ''}
                            onValueChange={(v) =>
                                applyFilters({
                                    track_inventory: v || undefined,
                                })
                            }
                        >
                            <SelectTrigger className="h-9 w-36">
                                <SelectValue placeholder="Tracking" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All products
                                </SelectItem>
                                <SelectItem value="1">Tracked</SelectItem>
                                <SelectItem value="0">Untracked</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <DataTable
                        columns={columns}
                        data={products}
                        baseUrl={ProductController.index.url()}
                        filters={filters}
                    />
                </div>

                <ProductFormModal
                    open={createOpen}
                    onOpenChange={setCreateOpen}
                    categories={categories}
                    brands={brands}
                    units={units}
                />

                {editProduct && (
                    <ProductFormModal
                        open={!!editProduct}
                        onOpenChange={(v) => !v && setEditProduct(null)}
                        categories={categories}
                        brands={brands}
                        units={units}
                        product={editProduct}
                    />
                )}

                {deleteProduct && (
                    <ConfirmDialog
                        open={!!deleteProduct}
                        onOpenChange={(v) => !v && setDeleteProduct(null)}
                        deleteRoute={ProductController.destroy.url({
                            product: deleteProduct.id,
                        })}
                        title={`Delete "${deleteProduct.name}"?`}
                        description="This product and all associated batches will be permanently deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
