import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2, CreditCard, Package, User } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog, { ActionDialog } from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import {
    PaymentStatusBadge,
    ReturnStatusBadge,
} from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatAmount, formatDateTime, formatMoney } from '@/lib/formatters';
import SaleReturnPaymentController from '@/wayfinder/App/Http/Controllers/Payments/SaleReturnPaymentController';
import CompleteSaleReturnController from '@/wayfinder/App/Http/Controllers/Sales/CompleteSaleReturnController';
import CustomerController from '@/wayfinder/App/Http/Controllers/Sales/CustomerController';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import SaleReturnController from '@/wayfinder/App/Http/Controllers/Sales/SaleReturnController';
import type { App, Inertia } from '@/wayfinder/types';
import PaymentFormModal from '../sales/partials/payment-form-modal';

interface Props extends Inertia.SharedData {
    saleReturn: App.Models.SaleReturn & {
        customer?: App.Models.Customer | null;
    };
    payment_methods: App.Models.PaymentMethod[];
}

export default function SaleReturnShow({ saleReturn, payment_methods }: Props) {
    const [payOpen, setPayOpen] = useState(false);
    const [completeOpen, setCompleteOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const items = saleReturn.items ?? [];
    const payments = saleReturn.payments ?? [];

    return (
        <AppLayout>
            <Head title={`Return ${saleReturn.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={SaleReturnController.index.url()}
                        title={saleReturn.reference_no}
                        badges={
                            <>
                                <ReturnStatusBadge status={saleReturn.status} />
                                <PaymentStatusBadge
                                    status={saleReturn.payment_status}
                                />
                            </>
                        }
                        subtitle={
                            <>
                                Created {formatDateTime(saleReturn.created_at)}
                                {saleReturn.sale && (
                                    <>
                                        {' · '}
                                        <Link
                                            href={SaleController.show.url({
                                                sale: saleReturn.sale.id,
                                            })}
                                            className="text-primary hover:underline"
                                        >
                                            {saleReturn.sale.reference_no}
                                        </Link>
                                    </>
                                )}
                            </>
                        }
                        actions={
                            <>
                                {saleReturn.status === 'completed' &&
                                    saleReturn.payment_status !== 'paid' && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setPayOpen(true)}
                                        >
                                            <CreditCard className="mr-1.5 h-3.5 w-3.5" />
                                            Add refund
                                        </Button>
                                    )}
                                {saleReturn.status === 'pending' && (
                                    <>
                                        <Button
                                            size="sm"
                                            onClick={() =>
                                                setCompleteOpen(true)
                                            }
                                        >
                                            <CheckCircle2 className="mr-1.5 h-3.5 w-3.5" />
                                            Complete
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            onClick={() => setDeleteOpen(true)}
                                        >
                                            Delete
                                        </Button>
                                    </>
                                )}
                            </>
                        }
                    />

                    <div className="grid grid-cols-3 gap-6">
                        <div className="col-span-2 space-y-4">
                            {/* Items */}
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Package className="h-4 w-4" />
                                        Returned items
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Product</TableHead>
                                                <TableHead>Batch</TableHead>
                                                <TableHead className="text-right">
                                                    Qty
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Unit price
                                                </TableHead>
                                                <TableHead className="text-right">
                                                    Subtotal
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {items.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell>
                                                        <p className="text-sm font-medium">
                                                            {item.product?.name}
                                                        </p>
                                                        <p className="font-mono text-xs text-muted-foreground">
                                                            {item.product?.sku}
                                                        </p>
                                                    </TableCell>
                                                    <TableCell>
                                                        <span className="font-mono text-sm text-muted-foreground">
                                                            {item.batch
                                                                ?.batch_number ??
                                                                '—'}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="text-right font-mono text-sm">
                                                        {item.quantity}
                                                    </TableCell>
                                                    <TableCell className="text-right font-mono text-sm">
                                                        {formatAmount(
                                                            item.unit_price,
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right font-mono text-sm font-medium">
                                                        {formatAmount(
                                                            item.subtotal,
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>

                            {/* Refunds */}
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <CreditCard className="h-4 w-4" />
                                        Refunds
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    {payments.length === 0 ? (
                                        <div className="flex h-16 items-center justify-center text-sm text-muted-foreground">
                                            No refunds recorded
                                        </div>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Reference
                                                    </TableHead>
                                                    <TableHead>
                                                        Method
                                                    </TableHead>
                                                    <TableHead>Date</TableHead>
                                                    <TableHead className="text-right">
                                                        Amount
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {payments.map((p) => (
                                                    <TableRow key={p.id}>
                                                        <TableCell className="font-mono text-xs">
                                                            {p.reference_no}
                                                        </TableCell>
                                                        <TableCell className="text-sm">
                                                            {p.payment_method
                                                                ?.name ?? '—'}
                                                        </TableCell>
                                                        <TableCell className="text-sm text-muted-foreground">
                                                            {formatDateTime(
                                                                p.payment_date,
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono text-sm">
                                                            {formatAmount(
                                                                p.amount,
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

                        {/* Sidebar */}
                        <div className="space-y-4">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base">
                                        Summary
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2 text-sm">
                                    <div className="flex justify-between text-base font-semibold">
                                        <span>Total</span>
                                        <span className="font-mono">
                                            {formatMoney(
                                                saleReturn.total_amount,
                                            )}
                                        </span>
                                    </div>
                                    <Separator />
                                    <div className="flex justify-between text-muted-foreground">
                                        <span>Refunded</span>
                                        <span className="font-mono text-emerald-600">
                                            {formatAmount(
                                                saleReturn.paid_amount,
                                            )}
                                        </span>
                                    </div>
                                    <div className="flex justify-between font-medium">
                                        <span>Due refund</span>
                                        <span
                                            className={
                                                saleReturn.due_amount > 0
                                                    ? 'font-mono font-semibold text-red-600'
                                                    : 'font-mono text-muted-foreground'
                                            }
                                        >
                                            {formatAmount(
                                                saleReturn.due_amount,
                                            )}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>

                            {saleReturn.customer && (
                                <Card>
                                    <CardHeader className="pb-3">
                                        <CardTitle className="flex items-center gap-2 text-base">
                                            <User className="h-4 w-4" />
                                            Customer
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="text-sm">
                                        <Link
                                            href={CustomerController.show.url({
                                                customer:
                                                    saleReturn.customer.id,
                                            })}
                                            className="font-medium text-primary hover:underline"
                                        >
                                            {saleReturn.customer.name}
                                        </Link>
                                        {saleReturn.customer.phone && (
                                            <p className="mt-0.5 text-muted-foreground">
                                                {saleReturn.customer.phone}
                                            </p>
                                        )}
                                    </CardContent>
                                </Card>
                            )}

                            {saleReturn.note && (
                                <Card>
                                    <CardHeader className="pb-2">
                                        <CardTitle className="text-base">
                                            Note
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                            {saleReturn.note}
                                        </p>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </div>
                </div>

                <PaymentFormModal
                    open={payOpen}
                    onOpenChange={setPayOpen}
                    storeUrl={SaleReturnPaymentController.url({
                        saleReturn: saleReturn.id,
                    })}
                    dueAmount={saleReturn.due_amount}
                    paymentMethods={payment_methods}
                />

                <ActionDialog
                    open={completeOpen}
                    onOpenChange={setCompleteOpen}
                    actionRoute={CompleteSaleReturnController.url({
                        saleReturn: saleReturn.id,
                    })}
                    title="Complete this return?"
                    description="Stock will be restored to the original batch."
                    confirmLabel="Complete return"
                />

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={SaleReturnController.destroy.url({
                        saleReturn: saleReturn.id,
                    })}
                    title={`Delete return ${saleReturn.reference_no}?`}
                    description="This return and all its items will be permanently deleted."
                    onSuccess={() =>
                        router.visit(SaleReturnController.index.url())
                    }
                />
            </div>
        </AppLayout>
    );
}
