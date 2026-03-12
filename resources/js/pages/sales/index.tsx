import { Head, router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import {
    CheckCircle2,
    MoreHorizontal,
    Plus,
    RotateCcw,
    Trash2,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog, { ActionDialog } from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import { PaymentStatusBadge, SaleStatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime, formatMoney } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import CancelSaleController from '@/wayfinder/App/Http/Controllers/Sales/CancelSaleController';
import CompleteSaleController from '@/wayfinder/App/Http/Controllers/Sales/CompleteSaleController';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import SaleReturnController from '@/wayfinder/App/Http/Controllers/Sales/SaleReturnController';
import type { App, Inertia } from '@/wayfinder/types';
import SaleFormModal from './partials/sale-form-modal';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Filters {
    search?: string;
    status?: string;
    payment_status?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    [key: string]: unknown;
}

interface Props extends Inertia.SharedData {
    sales: Paginated<App.Models.Sale>;
    customers: App.Models.Customer[];
    warehouses: App.Models.Warehouse[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
    filters: Filters;
}

// ─── Row actions ──────────────────────────────────────────────────────────────

function RowActions({
    sale,
    onEdit,
    onDelete,
    onComplete,
    onCancel,
}: {
    sale: App.Models.Sale;
    onEdit: (s: App.Models.Sale) => void;
    onDelete: (s: App.Models.Sale) => void;
    onComplete: (s: App.Models.Sale) => void;
    onCancel: (s: App.Models.Sale) => void;
}) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="h-8 w-8">
                    <MoreHorizontal className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                    onClick={() =>
                        router.visit(SaleController.show.url({ sale: sale.id }))
                    }
                >
                    View details
                </DropdownMenuItem>
                {sale.status === 'pending' && (
                    <DropdownMenuItem onClick={() => onEdit(sale)}>
                        Edit
                    </DropdownMenuItem>
                )}
                {sale.status === 'pending' && (
                    <DropdownMenuItem onClick={() => onComplete(sale)}>
                        <CheckCircle2 className="mr-2 h-3.5 w-3.5 text-emerald-500" />{' '}
                        Complete
                    </DropdownMenuItem>
                )}
                {sale.status === 'completed' && (
                    <DropdownMenuItem
                        onClick={() => {
                            const url = SaleReturnController.create[
                                '/sale-returns/create/{sale}'
                            ].url({
                                sale: sale.id,
                            });
                            router.visit(url);
                        }}
                    >
                        <RotateCcw className="mr-2 h-3.5 w-3.5" /> Create return
                    </DropdownMenuItem>
                )}
                {(sale.status === 'pending' || sale.status === 'completed') && (
                    <DropdownMenuItem
                        className="text-amber-600"
                        onClick={() => onCancel(sale)}
                    >
                        <XCircle className="mr-2 h-3.5 w-3.5" /> Cancel
                    </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                {sale.status !== 'completed' && (
                    <DropdownMenuItem
                        className="text-destructive focus:text-destructive"
                        onClick={() => onDelete(sale)}
                    >
                        <Trash2 className="mr-2 h-3.5 w-3.5" /> Delete
                    </DropdownMenuItem>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function SalesIndex({
    sales,
    customers,
    warehouses,
    products,
    filters = {},
}: Props) {
    const [createOpen, setCreateOpen] = useState(false);
    const [editSale, setEditSale] = useState<App.Models.Sale | null>(null);
    const [deleteSale, setDeleteSale] = useState<App.Models.Sale | null>(null);
    const [completeSale, setCompleteSale] = useState<App.Models.Sale | null>(
        null,
    );
    const [cancelSale, setCancelSale] = useState<App.Models.Sale | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            SaleController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<App.Models.Sale, unknown>[] = [
        {
            accessorKey: 'reference_no',
            size: 140,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Reference" />
            ),
            cell: ({ row }) => (
                <Button
                    className="font-mono text-xs font-medium text-primary hover:underline"
                    onClick={() =>
                        router.visit(
                            SaleController.show.url({ sale: row.original.id }),
                        )
                    }
                >
                    {row.original.reference_no}
                </Button>
            ),
        },
        {
            id: 'customer',
            size: 180,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Customer" />
            ),
            cell: ({ row }) => (
                <span className="text-sm">
                    {row.original.customer?.name ?? (
                        <span className="text-muted-foreground italic">
                            Walk-in
                        </span>
                    )}
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
            accessorKey: 'status',
            size: 110,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Status" />
            ),
            cell: ({ row }) => <SaleStatusBadge status={row.original.status} />,
        },
        {
            accessorKey: 'payment_status',
            size: 110,
            header: 'Payment',
            cell: ({ row }) => (
                <PaymentStatusBadge status={row.original.payment_status} />
            ),
        },
        {
            accessorKey: 'total_amount',
            size: 130,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Total" />
            ),
            cell: ({ row }) => (
                <span className="font-mono text-sm font-medium">
                    {formatMoney(row.original.total_amount)}
                </span>
            ),
        },
        {
            id: 'due_amount',
            size: 120,
            header: 'Due',
            cell: ({ row }) => {
                const due = row.original.due_amount;
                return (
                    <span
                        className={
                            due > 0
                                ? 'font-mono text-sm font-medium text-red-600'
                                : 'font-mono text-sm text-muted-foreground'
                        }
                    >
                        {formatMoney(due)}
                    </span>
                );
            },
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
        {
            id: 'actions',
            size: 50,
            cell: ({ row }) => (
                <RowActions
                    sale={row.original}
                    onEdit={setEditSale}
                    onDelete={setDeleteSale}
                    onComplete={setCompleteSale}
                    onCancel={setCancelSale}
                />
            ),
        },
    ];

    return (
        <AppLayout>
            <Head title="Sales" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Sales
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage all sales orders
                            </p>
                        </div>
                        <Button onClick={() => setCreateOpen(true)}>
                            <Plus className="mr-2 h-4 w-4" /> New sale
                        </Button>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Input
                            placeholder="Search reference, customer…"
                            className="h-9 w-64"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) =>
                                e.key === 'Enter' && applyFilters({ search })
                            }
                            onBlur={() => applyFilters({ search })}
                        />
                        <Select
                            value={filters.status ?? ''}
                            onValueChange={(v) =>
                                applyFilters({
                                    status: v === 'all' ? undefined : v,
                                })
                            }
                        >
                            <SelectTrigger className="h-9 w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All statuses
                                </SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="completed">
                                    Completed
                                </SelectItem>
                                <SelectItem value="cancelled">
                                    Cancelled
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            value={filters.payment_status ?? ''}
                            onValueChange={(v) =>
                                applyFilters({
                                    payment_status: v === 'all' ? undefined : v,
                                })
                            }
                        >
                            <SelectTrigger className="h-9 w-40">
                                <SelectValue placeholder="Payment" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All payments
                                </SelectItem>
                                <SelectItem value="unpaid">Unpaid</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <DataTable
                        columns={columns}
                        data={sales}
                        baseUrl={SaleController.index.url()}
                        filters={filters}
                    />
                </div>

                <SaleFormModal
                    open={createOpen}
                    onOpenChange={setCreateOpen}
                    customers={customers}
                    warehouses={warehouses}
                    products={products}
                />

                {editSale && (
                    <SaleFormModal
                        open={!!editSale}
                        onOpenChange={(v) => !v && setEditSale(null)}
                        customers={customers}
                        warehouses={warehouses}
                        products={products}
                        sale={editSale}
                    />
                )}

                {completeSale && (
                    <ActionDialog
                        open={!!completeSale}
                        onOpenChange={(v) => !v && setCompleteSale(null)}
                        actionRoute={CompleteSaleController.url({
                            sale: completeSale.id,
                        })}
                        title="Complete this sale?"
                        description={`Sale ${completeSale.reference_no} will be completed and stock deducted.`}
                        confirmLabel="Complete"
                    />
                )}

                {cancelSale && (
                    <ActionDialog
                        open={!!cancelSale}
                        onOpenChange={(v) => !v && setCancelSale(null)}
                        actionRoute={CancelSaleController.url({
                            sale: cancelSale.id,
                        })}
                        title="Cancel this sale?"
                        description={
                            cancelSale.status === 'completed'
                                ? `Cancelling will restore stock.`
                                : `Sale ${cancelSale.reference_no} will be cancelled.`
                        }
                        confirmLabel="Cancel sale"
                        confirmVariant="destructive"
                    />
                )}

                {deleteSale && (
                    <ConfirmDialog
                        open={!!deleteSale}
                        onOpenChange={(v) => !v && setDeleteSale(null)}
                        deleteRoute={SaleController.destroy.url({
                            sale: deleteSale.id,
                        })}
                        title={`Delete sale ${deleteSale.reference_no}?`}
                        description="This sale and all its items will be permanently deleted."
                    />
                )}
            </div>
        </AppLayout>
    );
}
