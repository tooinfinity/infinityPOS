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
import ExpenseCategoryController from '@/wayfinder/App/Http/Controllers/Expenses/ExpenseCategoryController';
import type { App, Inertia } from '@/wayfinder/types';

type CategoryRow = App.Models.ExpenseCategory & { expenses_count?: number };

interface Props extends Inertia.SharedData {
    categories: Paginated<CategoryRow>;
    filters: Record<string, unknown>;
}

export default function ExpenseCategoriesIndex({
    categories,
    filters = {},
}: Props) {
    const [deleteCategory, setDeleteCategory] =
        useState<App.Models.ExpenseCategory | null>(null);
    const [search, setSearch] = useState((filters.search as string) ?? '');

    function applyFilters(patch: Record<string, unknown>) {
        router.get(
            ExpenseCategoryController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<CategoryRow, unknown>[] = [
        {
            accessorKey: 'name',
            size: 200,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Name" />
            ),
            cell: ({ row }) => (
                <span className="text-sm font-medium">{row.original.name}</span>
            ),
        },
        {
            accessorKey: 'description',
            size: 260,
            header: 'Description',
            cell: ({ row }) => (
                <span className="line-clamp-1 text-sm text-muted-foreground">
                    {row.original.description ?? '—'}
                </span>
            ),
        },
        {
            accessorKey: 'expenses_count',
            size: 100,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Expenses" />
            ),
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.expenses_count ?? 0}
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
                                    ExpenseCategoryController.edit.url({
                                        expenseCategory: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteCategory(row.original),
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
            <Head title="Expense Categories" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Expense Categories
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Classify your business expenses
                            </p>
                        </div>
                        <Button
                            onClick={() =>
                                router.visit(
                                    ExpenseCategoryController.create.url(),
                                )
                            }
                        >
                            <Plus className="mr-2 h-4 w-4" /> New category
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
                        data={categories}
                        baseUrl={ExpenseCategoryController.index.url()}
                        filters={filters}
                    />
                </div>

                {deleteCategory && (
                    <ConfirmDialog
                        open={!!deleteCategory}
                        onOpenChange={(v) => !v && setDeleteCategory(null)}
                        deleteRoute={ExpenseCategoryController.destroy.url({
                            expenseCategory: deleteCategory.id,
                        })}
                        title={`Delete "${deleteCategory.name}"?`}
                        description="This category cannot be deleted if it has associated expenses."
                    />
                )}
            </div>
        </AppLayout>
    );
}
