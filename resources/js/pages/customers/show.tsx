import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Mail, MapPin, Phone, ShoppingBag } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import {
    ActiveBadge,
    PaymentStatusBadge,
    SaleStatusBadge,
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
import CustomerController from '@/wayfinder/App/Http/Controllers/Sales/CustomerController';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    customer: App.Models.Customer & {
        sales?: App.Models.Sale[];
        sales_count?: number;
        tax_number?: string;
    };
}

export default function CustomerShow({ customer }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const sales = customer.sales ?? [];

    return (
        <>
            <Head title={customer.name} />
            <div className="space-y-6">
                <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(CustomerController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <div className="flex items-center gap-2.5">
                                <h1 className="text-xl font-semibold tracking-tight">
                                    {customer.name}
                                </h1>
                                <ActiveBadge active={customer.is_active} />
                            </div>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Customer since {formatDate(customer.created_at)}
                            </p>
                        </div>
                    </div>
                    <div className="flex shrink-0 items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                router.visit(
                                    CustomerController.edit.url({
                                        customer: customer.id,
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
                                    <ShoppingBag className="h-4 w-4" /> Recent
                                    sales
                                    <span className="ml-auto text-sm font-normal text-muted-foreground">
                                        {customer.sales_count ?? 0} total
                                    </span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {sales.length === 0 ? (
                                    <div className="flex h-20 items-center justify-center text-sm text-muted-foreground">
                                        No sales yet
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
                                            {sales.map((sale) => (
                                                <TableRow key={sale.id}>
                                                    <TableCell>
                                                        <Link
                                                            href={SaleController.show.url(
                                                                {
                                                                    sale: sale.id,
                                                                },
                                                            )}
                                                            className="font-mono text-xs font-medium text-primary hover:underline"
                                                        >
                                                            {sale.reference_no}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell className="text-sm text-muted-foreground">
                                                        {formatDateTime(
                                                            sale.created_at,
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        <SaleStatusBadge
                                                            status={sale.status}
                                                        />
                                                    </TableCell>
                                                    <TableCell>
                                                        <PaymentStatusBadge
                                                            status={
                                                                sale.payment_status
                                                            }
                                                        />
                                                    </TableCell>
                                                    <TableCell className="text-right font-mono text-sm font-medium">
                                                        {formatMoney(
                                                            sale.total_amount,
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
                                {customer.phone && (
                                    <div className="flex items-center gap-2 text-muted-foreground">
                                        <Phone className="h-3.5 w-3.5 shrink-0" />
                                        <span>{customer.phone}</span>
                                    </div>
                                )}
                                {customer.email && (
                                    <div className="flex items-center gap-2 text-muted-foreground">
                                        <Mail className="h-3.5 w-3.5 shrink-0" />
                                        <span>{customer.email}</span>
                                    </div>
                                )}
                                {(customer.address || customer.city) && (
                                    <div className="flex items-start gap-2 text-muted-foreground">
                                        <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                        <span>
                                            {[customer.address, customer.city]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </span>
                                    </div>
                                )}
                                {customer.tax_number && (
                                    <div className="text-muted-foreground">
                                        <span className="font-medium text-foreground">
                                            Tax:{' '}
                                        </span>
                                        <span className="font-mono">
                                            {customer.tax_number}
                                        </span>
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
                            <CardContent className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Total orders
                                    </span>
                                    <span className="font-semibold tabular-nums">
                                        {customer.sales_count ?? 0}
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
                deleteRoute={CustomerController.destroy.url({
                    customer: customer.id,
                })}
                title={`Delete ${customer.name}?`}
                description="Purchases will not be affected."
                onSuccess={() => router.visit(CustomerController.index.url())}
            />
        </>
    );
}
