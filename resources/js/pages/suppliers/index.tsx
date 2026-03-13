import { Head, router, useForm } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Loader2, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ActionMenu from '@/components/action-menu';
import ConfirmDialog from '@/components/confirm-dialog';
import DataTable from '@/components/data-table/data-table';
import DataTableColumnHeader from '@/components/data-table/data-table-column-header';
import FilterBar from '@/components/filter-bar';
import { ActiveBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/formatters';
import { Paginated } from '@/lib/paginated';
import SupplierController from '@/wayfinder/App/Http/Controllers/Purchases/SupplierController';
import type { App, Inertia } from '@/wayfinder/types';

interface Filters {
    search?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    [key: string]: unknown;
}
type SupplierRow = App.Models.Supplier & { purchases_count?: number };

interface Props extends Inertia.SharedData {
    suppliers: Paginated<SupplierRow>;
    filters: Filters;
}

interface FormData {
    name: string;
    email: string;
    phone: string;
    address: string;
    is_active: boolean;
}

function SupplierFormModal({
    open,
    onOpenChange,
    supplier,
}: {
    open: boolean;
    onOpenChange: (v: boolean) => void;
    supplier?: App.Models.Supplier;
}) {
    const isEditing = !!supplier;
    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<FormData>({
            name: supplier?.name ?? '',
            email: supplier?.email ?? '',
            phone: supplier?.phone ?? '',
            address: supplier?.address ?? '',
            is_active: supplier?.is_active ?? true,
        });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (isEditing && supplier) {
            put(SupplierController.update.url({ supplier: supplier.id }), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(SupplierController.store.url(), {
                preserveScroll: true,
                onSuccess: () => {
                    onOpenChange(false);
                    reset();
                },
            });
        }
    }

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                if (!v) {
                    onOpenChange(false);
                    clearErrors();
                }
            }}
        >
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? 'Edit supplier' : 'New supplier'}
                    </DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2 space-y-1.5">
                            <Label>
                                Name <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                required
                            />
                            {errors.name && (
                                <p className="text-xs text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>
                        <div className="space-y-1.5">
                            <Label>Email</Label>
                            <Input
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-1.5">
                            <Label>Phone</Label>
                            <Input
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
                                }
                            />
                        </div>
                        <div className="col-span-2 space-y-1.5">
                            <Label>Address</Label>
                            <Textarea
                                rows={2}
                                className="resize-none"
                                value={data.address}
                                onChange={(e) =>
                                    setData('address', e.target.value)
                                }
                            />
                        </div>
                        <div className="col-span-2 flex items-center gap-3">
                            <Switch
                                id="supplier_active"
                                checked={data.is_active}
                                onCheckedChange={(v) => setData('is_active', v)}
                            />
                            <Label
                                htmlFor="supplier_active"
                                className="cursor-pointer"
                            >
                                Active
                            </Label>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing && (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            )}
                            {isEditing ? 'Save changes' : 'Create supplier'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function SuppliersIndex({ suppliers, filters = {} }: Props) {
    const [createOpen, setCreateOpen] = useState(false);
    const [editSupplier, setEditSupplier] =
        useState<App.Models.Supplier | null>(null);
    const [deleteSupplier, setDeleteSupplier] =
        useState<App.Models.Supplier | null>(null);
    const [search, setSearch] = useState(filters.search ?? '');

    function applyFilters(patch: Partial<Filters>) {
        router.get(
            SupplierController.index.url(),
            { ...filters, ...patch, page: 1 },
            { preserveState: true, replace: true },
        );
    }

    const columns: ColumnDef<SupplierRow>[] = [
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
                            SupplierController.show.url({
                                supplier: row.original.id,
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
            accessorKey: 'purchases_count',
            size: 100,
            header: ({ column }) => (
                <DataTableColumnHeader column={column} title="Purchases" />
            ),
            cell: ({ row }) => (
                <span className="text-sm tabular-nums">
                    {row.original.purchases_count ?? 0}
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
                                    SupplierController.show.url({
                                        supplier: row.original.id,
                                    }),
                                ),
                        },
                        {
                            label: 'Edit',
                            onClick: () => setEditSupplier(row.original),
                        },
                        {
                            label: 'Delete',
                            onClick: () => setDeleteSupplier(row.original),
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
            <Head title="Suppliers" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-5">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Suppliers
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Manage your supplier list
                            </p>
                        </div>
                        <Button onClick={() => setCreateOpen(true)}>
                            <Plus className="mr-2 h-4 w-4" /> New supplier
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
                        data={suppliers}
                        baseUrl={SupplierController.index.url()}
                        filters={filters}
                    />
                </div>

                <SupplierFormModal
                    open={createOpen}
                    onOpenChange={setCreateOpen}
                />
                {editSupplier && (
                    <SupplierFormModal
                        open={!!editSupplier}
                        onOpenChange={(v) => !v && setEditSupplier(null)}
                        supplier={editSupplier}
                    />
                )}
                {deleteSupplier && (
                    <ConfirmDialog
                        open={!!deleteSupplier}
                        onOpenChange={(v) => !v && setDeleteSupplier(null)}
                        deleteRoute={SupplierController.destroy.url({
                            supplier: deleteSupplier.id,
                        })}
                        title={`Delete ${deleteSupplier.name}?`}
                        description="Purchases will not be affected."
                    />
                )}
            </div>
        </AppLayout>
    );
}
