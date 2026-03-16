import { Head, Link, router } from '@inertiajs/react';
import { Mail, MapPin, Phone, ShoppingCart } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import {
    ActiveBadge,
    PaymentStatusBadge,
    PurchaseStatusBadge,
} from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatDateTime, formatMoney } from '@/lib/formatters';
import PurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseController';
import SupplierController from '@/wayfinder/App/Http/Controllers/Purchases/SupplierController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    supplier: App.Models.Supplier & {
        purchases?: App.Models.Purchase[];
        purchases_count?: number;
    };
}

export default function SupplierShow({ supplier }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const purchases = supplier.purchases ?? [];

    return (
        <AppLayout>
            <Head title={supplier.name} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={SupplierController.index.url()}
                        title={supplier.name}
                        badges={<ActiveBadge active={supplier.is_active} />}
                        subtitle={`Supplier since ${formatDate(supplier.created_at)}`}
                        actions={
                            <>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    onClick={() => setDeleteOpen(true)}
                                >
                                    Delete
                                </Button>
                            </>
                        }
                    />

                    <div className="grid grid-cols-3 gap-6">
                        <div className="col-span-2">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <ShoppingCart className="h-4 w-4" />{' '}
                                        Recent purchases
                                        <span className="ml-auto text-sm font-normal text-muted-foreground">
                                            {supplier.purchases_count ?? 0}{' '}
                                            total
                                        </span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    {purchases.length === 0 ? (
                                        <div className="flex h-20 items-center justify-center text-sm text-muted-foreground">
                                            No purchases yet
                                        </div>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Reference
                                                    </TableHead>
                                                    <TableHead>Date</TableHead>
                                                    <TableHead>
                                                        Status
                                                    </TableHead>
                                                    <TableHead>
                                                        Payment
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Total
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {purchases.map((purchase) => (
                                                    <TableRow key={purchase.id}>
                                                        <TableCell>
                                                            <Link
                                                                href={PurchaseController.show.url(
                                                                    {
                                                                        purchase:
                                                                            purchase.id,
                                                                    },
                                                                )}
                                                                className="font-mono text-xs font-medium text-primary hover:underline"
                                                            >
                                                                {
                                                                    purchase.reference_no
                                                                }
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell className="text-sm text-muted-foreground">
                                                            {formatDateTime(
                                                                purchase.created_at,
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            <PurchaseStatusBadge
                                                                status={
                                                                    purchase.status
                                                                }
                                                            />
                                                        </TableCell>
                                                        <TableCell>
                                                            <PaymentStatusBadge
                                                                status={
                                                                    purchase.payment_status
                                                                }
                                                            />
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono text-sm font-medium">
                                                            {formatMoney(
                                                                purchase.total_amount,
                                                            )}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        <div className="space-y-4">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base">
                                        Contact
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3 text-sm">
                                    {supplier.phone && (
                                        <div className="flex items-center gap-2 text-muted-foreground">
                                            <Phone className="h-3.5 w-3.5 shrink-0" />
                                            <span>{supplier.phone}</span>
                                        </div>
                                    )}
                                    {supplier.email && (
                                        <div className="flex items-center gap-2 text-muted-foreground">
                                            <Mail className="h-3.5 w-3.5 shrink-0" />
                                            <span>{supplier.email}</span>
                                        </div>
                                    )}
                                    {supplier.address && (
                                        <div className="flex items-start gap-2 text-muted-foreground">
                                            <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                            <span>{supplier.address}</span>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base">
                                        Stats
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Total orders
                                        </span>
                                        <span className="font-semibold tabular-nums">
                                            {supplier.purchases_count ?? 0}
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
                    deleteRoute={SupplierController.destroy.url({
                        supplier: supplier.id,
                    })}
                    title={`Delete ${supplier.name}?`}
                    description="Purchases will not be affected."
                    onSuccess={() =>
                        router.visit(SupplierController.index.url())
                    }
                />
            </div>
        </AppLayout>
    );
}
