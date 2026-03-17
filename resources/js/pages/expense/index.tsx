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
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatMoney } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import ExpenseController from '@/wayfinder/App/Http/Controllers/Expenses/ExpenseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    expenses: Paginated<App.Models.Expense>;
    filters: Record<string, unknown>;
}

export default function ExpensesIndex({ expenses, filters = {} }: Props) {
    const [deleteExpense, setDeleteExpense] =
        useState<App.Models.Expense | null>(null);
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            ExpenseController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.Expense>[] = [
        {
            accessorKey: 'reference_no',
            size: 150,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Reference" />
            ),
            cell: ({ row }) => (
                <button
                    className="font-mono text-xs font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            ExpenseController.show.url({
                                expense: row.original.id,
                            }),
                        )
                    }
                >
                    {row.original.reference_no}
                </button>
            ),
        },
        {
            id: 'category',
            size: 180,
            header: 'Category',
            cell: ({ row }) => (
                <span className="text-sm">
                    {row.original.expense_category?.name ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'description',
            size: 240,
            header: 'Description',
            cell: ({ row }) => (
                <span className="line-clamp-1 text-sm text-muted-foreground">
                    {row.original.description ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'amount',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Amount" />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-sm font-medium">
                    {formatMoney(row.original.amount)}
                </span>
            ),
        },
        {
            accessorKey: 'expense_date',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Date" />
            ),
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {formatDate(row.original.expense_date)}
                </span>
            ),
        },
        {
            id: 'user',
            size: 140,
            header: 'Recorded by',
            cell: ({ row }) => (
                <span className="text-sm text-muted-foreground">
                    {row.original.user?.name ?? '—'}
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
                                    ExpenseController.show.url({
                                        expense: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () =>
                                router.visit(
                                    ExpenseController.edit.url({
                                        expense: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteExpense(row.original),
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
            <Head title="Expenses" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Expenses
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Track all business expenses
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(ExpenseController.create.url())
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New expense
                        </Button>
                    </div>
                    <FilterBar
                        search={search}
                        onSearchChange={setSearch}
                        onSearch={() => applyFilters({ search })}
                        placeholder="Search by reference or description…"
                    />
                    <DataTable
                        columns={columns}
                        data={expenses}
                        baseUrl={ExpenseController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteExpense && (
                    <ConfirmDialog
                        open={!!deleteExpense}
                        onOpenChange={(v) => !v && setDeleteExpense(null)}
                        deleteRoute={ExpenseController.destroy.url({
                            expense: deleteExpense.id,
                        })}
                        title={`Delete expense ${deleteExpense.reference_no}?`}
                        description="This expense will be permanently deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
