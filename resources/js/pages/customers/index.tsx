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
import CustomerController from '@/wayfinder/App/Http/Controllers/Sales/CustomerController';
import type { App, Inertia } from '@/wayfinder/types';
import CustomerFormModal from './partials/customer-form-modal';

interface Filters {
    search?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    [key: string]: unknown;
}

type CustomerRow = App.Models.Customer & {
    sales_count?: number;
    tax_number?: string;
};

interface Props extends Inertia.SharedData {
    customers: Paginated<CustomerRow>;
    filters: Filters;
}

export default function CustomersIndex({ customers, filters = {} }: Props) {
    const [createOpen, setCreateOpen] = useState(false);
    const [editCustomer, setEditCustomer] =
        useState<App.Models.Customer | null>(null);
    const [deleteCustomer, setDeleteCustomer] =
        useState<App.Models.Customer | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            CustomerController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<CustomerRow, unknown>[] = [
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
                            CustomerController.show.url({
                                customer: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.name}
                </button>
            ),
        },
        {
            accessorKey: 'phone',
            size: 140,
            header: 'Phone',
            cell: ({ row }) => (
                <span className="font-mono text-sm text-muted-foreground">
                    {row.original.phone ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'email',
            size: 200,
            header: 'Email',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.email ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'city',
            size: 120,
            header: 'City',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.city ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'sales_count',
            size: 90,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Sales" />
            ),
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
                                    CustomerController.show.url({
                                        customer: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () => setEditCustomer(row.original),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteCustomer(row.original),
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
            <Head title="Customers" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Customers
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage your customer base
                            </p>
                        </div>
                        <Button onClick={() => setCreateOpen(true)}>
                            <Plus className="mr-2 h-4 w-4" /> New customer
                        </Button>
                    </div>

                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by name, email, phone…"
                    />

                    <DataTable
                        columns={columns}
                        data={customers}
                        baseUrl={CustomerController.index.url()}
                        filters={filters}
                    />
                </div>

                <CustomerFormModal
                    open={createOpen}
                    onOpenChange={setCreateOpen}
                />
                {editCustomer && (
                    <CustomerFormModal
                        open={!!editCustomer}
                        onOpenChange={(v) => !v && setEditCustomer(null)}
                        customer={editCustomer}
                    />
                )}
                {deleteCustomer && (
                    <ConfirmDialog
                        open={!!deleteCustomer}
                        onOpenChange={(v) => !v && setDeleteCustomer(null)}
                        deleteRoute={CustomerController.destroy.url({
                            customer: deleteCustomer.id,
                        })}
                        title={`Delete ${deleteCustomer.name}?`}
                        description="This customer will be permanently deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
