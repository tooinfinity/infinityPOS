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
import PaymentMethodController from '@/wayfinder/App/Http/Controllers/Payments/PaymentMethodController';
import type { App, Inertia } from '@/wayfinder/types';

type MethodRow = App.Models.PaymentMethod & { payments_count?: number };

interface Props extends Inertia.SharedData {
    methods: Paginated<MethodRow>;
    filters: Record<string, unknown>;
}

export default function PaymentMethodsIndex({ methods, filters = {} }: Props) {
    const [deleteMethod, setDeleteMethod] =
        useState<App.Models.PaymentMethod | null>(null);
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            PaymentMethodController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<MethodRow, unknown>[] = [
        {
            accessorKey: 'name',
            size: 180,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Name" />
            ),
            cell: ({ row }) => (
                <span className="text-sm font-medium">{row.original.name}</span>
            ),
        },
        {
            accessorKey: 'code',
            size: 100,
            header: 'Code',
            cell: ({ row }) => (
                <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs font-medium">
                    {row.original.code}
                </span>
            ),
        },
        {
            accessorKey: 'payments_count',
            size: 100,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Payments" />
            ),
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.payments_count ?? 0}
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
                            label: 'Edit',
                            onClick: () =>
                                router.visit(
                                    PaymentMethodController.edit.url({
                                        paymentMethod: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteMethod(row.original),
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
            <Head title="Payment Methods" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Payment Methods
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage accepted payment methods
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(
                                    PaymentMethodController.create.url(),
                                )
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New method
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
                        data={methods}
                        baseUrl={PaymentMethodController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteMethod && (
                    <ConfirmDialog
                        open={!!deleteMethod}
                        onOpenChange={(v) => !v && setDeleteMethod(null)}
                        deleteRoute={PaymentMethodController.destroy.url({
                            paymentMethod: deleteMethod.id,
                        })}
                        title={`Delete "${deleteMethod.name}"?`}
                        description="This method cannot be deleted if it has associated payments."
                    />
                )}
            </div>
        </AppLayout>
    );
}
