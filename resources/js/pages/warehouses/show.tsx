import { Head, router } from '@inertiajs/react';
import { Mail, MapPin, Pencil, Phone, Trash2, Warehouse } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import { ActiveBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/formatters';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';

type WarehouseWithCounts = App.Models.Warehouse & {
    batches_count?: number;
    purchases_count?: number;
    sales_count?: number;
    stock_movements_count?: number;
    transfers_from_count?: number;
    transfers_to_count?: number;
};

interface Props extends Inertia.SharedData {
    warehouse: WarehouseWithCounts;
}

export default function WarehouseShow({ warehouse }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);

    return (
        <AppLayout>
            <Head title={warehouse.name} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={WarehouseController.index.url()}
                        title={warehouse.name}
                        badges={
                            <>
                                <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs font-medium">
                                    {warehouse.code}
                                </span>
                                <ActiveBadge active={warehouse.is_active} />
                            </>
                        }
                        subtitle={`Created ${formatDate(warehouse.created_at)}`}
                        actions={
                            <>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.visit(
                                            WarehouseController.edit.url({
                                                warehouse: warehouse.id,
                                            }),
                                        )
                                    }
                                >
                                    <Pencil className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Edit
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    onClick={() => setDeleteOpen(true)}
                                >
                                    <Trash2 className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Delete
                                </Button>
                            </>
                        }
                    />

                    <div className="grid grid-cols-3 gap-6">
                        <div className="col-span-2 grid grid-cols-2 gap-4">
                            {[
                                {
                                    label: 'Batches',
                                    value: warehouse.batches_count ?? 0,
                                },
                                {
                                    label: 'Purchases',
                                    value: warehouse.purchases_count ?? 0,
                                },
                                {
                                    label: 'Sales',
                                    value: warehouse.sales_count ?? 0,
                                },
                                {
                                    label: 'Stock movements',
                                    value: warehouse.stock_movements_count ?? 0,
                                },
                                {
                                    label: 'Transfers (from)',
                                    value: warehouse.transfers_from_count ?? 0,
                                },
                                {
                                    label: 'Transfers (to)',
                                    value: warehouse.transfers_to_count ?? 0,
                                },
                            ].map(({ label, value }) => (
                                <Card key={label}>
                                    <CardContent className="pt-6">
                                        <p className="text-sm text-muted-foreground">
                                            {label}
                                        </p>
                                        <p className="mt-1 text-2xl font-semibold tabular-nums">
                                            {value}
                                        </p>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        <div className="space-y-4">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Warehouse className="h-4 w-4" />{' '}
                                        Location
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3 text-sm">
                                    {warehouse.phone && (
                                        <div className="flex items-center gap-2 text-muted-foreground">
                                            <Phone className="h-3.5 w-3.5 shrink-0" />
                                            <span>{warehouse.phone}</span>
                                        </div>
                                    )}
                                    {warehouse.email && (
                                        <div className="flex items-center gap-2 text-muted-foreground">
                                            <Mail className="h-3.5 w-3.5 shrink-0" />
                                            <span>{warehouse.email}</span>
                                        </div>
                                    )}
                                    {(warehouse.address || warehouse.city) && (
                                        <div className="flex items-start gap-2 text-muted-foreground">
                                            <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                            <span>
                                                {[
                                                    warehouse.address,
                                                    warehouse.city,
                                                    warehouse.country,
                                                ]
                                                    .filter(Boolean)
                                                    .join(', ')}
                                            </span>
                                        </div>
                                    )}
                                    {!warehouse.phone &&
                                        !warehouse.email &&
                                        !warehouse.address &&
                                        !warehouse.city && (
                                            <p className="text-muted-foreground italic">
                                                No contact info
                                            </p>
                                        )}
                                    <Separator />
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Status
                                        </span>
                                        <ActiveBadge
                                            active={warehouse.is_active}
                                        />
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Created
                                        </span>
                                        <span>
                                            {formatDate(warehouse.created_at)}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={WarehouseController.destroy.url({
                        warehouse: warehouse.id,
                    })}
                    title={`Delete "${warehouse.name}"?`}
                    description="This warehouse can only be deleted if it has no associated stock, purchases or sales."
                    onSuccess={() =>
                        router.visit(WarehouseController.index.url())
                    }
                />
            </div>
        </AppLayout>
    );
}
