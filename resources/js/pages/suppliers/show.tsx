import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Mail, MapPin, Phone, ShoppingCart } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
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
        <>
            <Head title={supplier.name} />
            <div className="space-y-6">
                <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(SupplierController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <div className="flex items-center gap-2.5">
                                <h1 className="text-xl font-semibold tracking-tight">
                                    {supplier.name}
                                </h1>
                                <ActiveBadge active={supplier.is_active} />
                            </div>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Supplier since {formatDate(supplier.created_at)}
                            </p>
                        </div>
                    </div>
                    <div className="flex shrink-0 items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(
                                    SupplierController.edit.url({
                                        supplier: supplier.id,
                                    }),
                                )
                            }
                        >
                            Edit
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={() => setDeleteOpen(true)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-3 gap-6">
                    <div className="col-span-2">
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <ShoppingCart className="h-4 w-4" /> Recent
                                    purchases
                                    <span className="ml-auto text-sm font-normal text-muted-foreground">
                                        {supplier.purchases_count ?? 0} total
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
                                                <TableHead>Reference</TableHead>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>Payment</TableHead>
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
                onSuccess={() => router.visit(SupplierController.index.url())}
            />
        </>
    );
}
