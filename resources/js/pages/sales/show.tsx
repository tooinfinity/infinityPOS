import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    CreditCard,
    RotateCcw,
    User,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

import { ActionDialog } from '@/components/confirm-dialog';
import { PaymentStatusBadge, SaleStatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/lib/formatters';
import SalePaymentController from '@/wayfinder/App/Http/Controllers/Payments/SalePaymentController';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import CancelSaleController from '@/wayfinder/App/Http/Controllers/Sales/CancelSaleController';
import CompleteSaleController from '@/wayfinder/App/Http/Controllers/Sales/CompleteSaleController';
import CustomerController from '@/wayfinder/App/Http/Controllers/Sales/CustomerController';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import SaleReturnController from '@/wayfinder/App/Http/Controllers/Sales/SaleReturnController';
import type { App, Inertia } from '@/wayfinder/types';
import PaymentFormModal from './partials/payment-form-modal';
import SaleFormModal from './partials/sale-form-modal';
import SaleItemsTable from './partials/sale-items-table';
import SalePaymentsTable from './partials/sale-payments-table';
import SaleSummaryCard from './partials/sale-summary-card';

interface Props extends Inertia.SharedData {
    sale: App.Models.Sale;
    customers: App.Models.Customer[];
    warehouses: App.Models.Warehouse[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
    payment_methods: App.Models.PaymentMethod[];
}

export default function SaleShow({
    sale,
    customers,
    warehouses,
    products,
    payment_methods,
}: Props) {
    const [editOpen, setEditOpen] = useState(false);
    const [payOpen, setPayOpen] = useState(false);
    const [completeOpen, setCompleteOpen] = useState(false);
    const [cancelOpen, setCancelOpen] = useState(false);

    const items = sale.items ?? [];
    const payments = sale.payments ?? [];

    return (
        <>
            <Head title={`Sale ${sale.reference_no}`} />

            <div className="space-y-6">
                <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(SaleController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <div className="flex items-center gap-2.5">
                                <h1 className="font-mono text-xl font-semibold tracking-tight">
                                    {sale.reference_no}
                                </h1>
                                <SaleStatusBadge status={sale.status} />
                                <PaymentStatusBadge
                                    status={sale.payment_status}
                                />
                            </div>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Created {formatDateTime(sale.created_at)}
                            </p>
                        </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-2">
                        {sale.status === 'pending' && (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => setEditOpen(true)}
                            >
                                Edit
                            </Button>
                        )}
                        {sale.status === 'completed' &&
                            sale.payment_status !== 'paid' && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setPayOpen(true)}
                                >
                                    <CreditCard className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Add payment
                                </Button>
                            )}
                        {sale.status === 'pending' && (
                            <Button
                                size="sm"
                                onClick={() => setCompleteOpen(true)}
                            >
                                <CheckCircle2 className="mr-1.5 h-3.5 w-3.5" />{' '}
                                Complete
                            </Button>
                        )}
                        {sale.status === 'completed' && (
                            <Button variant="outline" size="sm" asChild>
                                <Link
                                    href={SaleReturnController.create[
                                        '/sale-returns/create/{sale}'
                                    ].url({
                                        sale: sale.id,
                                    })}
                                >
                                    <RotateCcw className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Create return
                                </Link>
                            </Button>
                        )}
                        {(sale.status === 'pending' ||
                            sale.status === 'completed') && (
                            <Button
                                variant="destructive"
                                size="sm"
                                onClick={() => setCancelOpen(true)}
                            >
                                <XCircle className="mr-1.5 h-3.5 w-3.5" />{' '}
                                Cancel
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-3 gap-6">
                    <div className="col-span-2 space-y-4">
                        <SaleItemsTable items={items} />
                        <SalePaymentsTable payments={payments} />
                    </div>

                    <div className="space-y-4">
                        <SaleSummaryCard
                            totalAmount={sale.total_amount}
                            paidAmount={sale.paid_amount}
                            dueAmount={
                                typeof sale.due_amount === 'number'
                                    ? sale.due_amount
                                    : 0
                            }
                        />

                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <User className="h-4 w-4" /> Customer
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm">
                                {sale.customer ? (
                                    <div className="space-y-1">
                                        <Link
                                            href={CustomerController.show.url({
                                                customer: sale.customer.id,
                                            })}
                                            className="font-medium text-primary hover:underline"
                                        >
                                            {sale.customer.name}
                                        </Link>
                                        {sale.customer.phone && (
                                            <p className="text-muted-foreground">
                                                {sale.customer.phone}
                                            </p>
                                        )}
                                        {sale.customer.email && (
                                            <p className="text-muted-foreground">
                                                {sale.customer.email}
                                            </p>
                                        )}
                                    </div>
                                ) : (
                                    <span className="text-muted-foreground italic">
                                        Walk-in customer
                                    </span>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Building2 className="h-4 w-4" /> Warehouse
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm">
                                {sale.warehouse ? (
                                    <>
                                        <Link
                                            href={WarehouseController.show.url({
                                                warehouse: sale.warehouse.id,
                                            })}
                                            className="font-medium text-primary hover:underline"
                                        >
                                            {sale.warehouse.name}
                                        </Link>
                                        <p className="mt-0.5 font-mono text-xs text-muted-foreground">
                                            {sale.warehouse.code}
                                        </p>
                                    </>
                                ) : (
                                    <span className="text-muted-foreground italic">
                                        —
                                    </span>
                                )}
                            </CardContent>
                        </Card>

                        {sale.note && (
                            <Card>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-base">
                                        Note
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                        {sale.note}
                                    </p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>

            <SaleFormModal
                open={editOpen}
                onOpenChange={setEditOpen}
                customers={customers}
                warehouses={warehouses}
                products={products}
                sale={sale}
            />

            <PaymentFormModal
                open={payOpen}
                onOpenChange={setPayOpen}
                storeUrl={SalePaymentController.url({ sale: sale.id })}
                dueAmount={
                    typeof sale.due_amount === 'number' ? sale.due_amount : 0
                }
                paymentMethods={payment_methods}
            />

            <ActionDialog
                open={completeOpen}
                onOpenChange={setCompleteOpen}
                actionRoute={CompleteSaleController.url({ sale: sale.id })}
                title="Complete this sale?"
                description="Stock will be deducted for all items."
                confirmLabel="Complete sale"
            />

            <ActionDialog
                open={cancelOpen}
                onOpenChange={setCancelOpen}
                actionRoute={CancelSaleController.url({ sale: sale.id })}
                title="Cancel this sale?"
                description={
                    sale.status === 'completed'
                        ? 'Cancelling will restore stock.'
                        : 'The sale will be cancelled.'
                }
                confirmLabel="Cancel sale"
                confirmVariant="destructive"
            />
        </>
    );
}
