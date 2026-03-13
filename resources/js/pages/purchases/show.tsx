import { Head, Link } from '@inertiajs/react';
import {
    Building2,
    CheckCircle2,
    CreditCard,
    RotateCcw,
    Truck,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

import { ActionDialog } from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import {
    PaymentStatusBadge,
    PurchaseStatusBadge,
} from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/formatters';
import TransactionSummaryCard from '@/pages/sales/partials/TransactionSummaryCard';
import PurchasePaymentController from '@/wayfinder/App/Http/Controllers/Payments/PurchasePaymentController';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import CancelPurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/CancelPurchaseController';
import PurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseController';
import PurchaseReturnController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseReturnController';
import ReceivePurchaseController from '@/wayfinder/App/Http/Controllers/Purchases/ReceivePurchaseController';
import SupplierController from '@/wayfinder/App/Http/Controllers/Purchases/SupplierController';
import type { App, Inertia } from '@/wayfinder/types';
import PaymentFormModal from '../sales/partials/payment-form-modal';
import PurchaseItemsTable from './partials/purchase-items-table';
import PurchasePaymentsTable from './partials/purchase-payments-table';

interface Props extends Inertia.SharedData {
    purchase: App.Models.Purchase;
    payment_methods: App.Models.PaymentMethod[];
}

export default function PurchaseShow({ purchase, payment_methods }: Props) {
    const [payOpen, setPayOpen] = useState(false);
    const [receiveOpen, setReceiveOpen] = useState(false);
    const [cancelOpen, setCancelOpen] = useState(false);

    const items = purchase.items ?? [];
    const payments = purchase.payments ?? [];

    return (
        <AppLayout>
            <Head title={`Purchase ${purchase.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={PurchaseController.index.url()}
                        title={purchase.reference_no}
                        badges={
                            <>
                                <PurchaseStatusBadge status={purchase.status} />
                                <PaymentStatusBadge
                                    status={purchase.payment_status}
                                />
                            </>
                        }
                        subtitle={`Created ${formatDateTime(purchase.created_at)}`}
                        actions={
                            <>
                                {(purchase.status === 'pending' ||
                                    purchase.status === 'ordered') && (
                                    <Button
                                        size="sm"
                                        onClick={() => setReceiveOpen(true)}
                                    >
                                        <CheckCircle2 className="mr-1.5 h-3.5 w-3.5" />
                                        Receive
                                    </Button>
                                )}
                                {purchase.status === 'received' &&
                                    purchase.payment_status !== 'paid' && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setPayOpen(true)}
                                        >
                                            <CreditCard className="mr-1.5 h-3.5 w-3.5" />
                                            Add payment
                                        </Button>
                                    )}
                                {purchase.status === 'received' && (
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={PurchaseReturnController.create[
                                                '/purchase-returns/create/{purchase}'
                                            ].url({
                                                purchase: purchase.id,
                                            })}
                                        >
                                            <RotateCcw className="mr-1.5 h-3.5 w-3.5" />
                                            Create return
                                        </Link>
                                    </Button>
                                )}
                                {(purchase.status === 'pending' ||
                                    purchase.status === 'ordered' ||
                                    purchase.status === 'received') && (
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        onClick={() => setCancelOpen(true)}
                                    >
                                        <XCircle className="mr-1.5 h-3.5 w-3.5" />
                                        Cancel
                                    </Button>
                                )}
                            </>
                        }
                    />

                    <div className="grid grid-cols-3 gap-6">
                        <div className="col-span-2 space-y-4">
                            <PurchaseItemsTable items={items} />
                            <PurchasePaymentsTable payments={payments} />
                        </div>

                        <div className="space-y-4">
                            <TransactionSummaryCard
                                totalAmount={purchase.total_amount}
                                paidAmount={purchase.paid_amount}
                                dueAmount={purchase.due_amount}
                            />

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Truck className="h-4 w-4" /> Supplier
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="text-sm">
                                    {purchase.supplier ? (
                                        <div className="space-y-1">
                                            <Link
                                                href={SupplierController.show.url(
                                                    {
                                                        supplier:
                                                            purchase.supplier
                                                                .id,
                                                    },
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {purchase.supplier.name}
                                            </Link>
                                            {purchase.supplier.phone && (
                                                <p className="text-muted-foreground">
                                                    {purchase.supplier.phone}
                                                </p>
                                            )}
                                            {purchase.supplier.email && (
                                                <p className="text-muted-foreground">
                                                    {purchase.supplier.email}
                                                </p>
                                            )}
                                        </div>
                                    ) : (
                                        <span className="text-muted-foreground italic">
                                            —
                                        </span>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Building2 className="h-4 w-4" />{' '}
                                        Warehouse
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="text-sm">
                                    {purchase.warehouse ? (
                                        <>
                                            <Link
                                                href={WarehouseController.show.url(
                                                    {
                                                        warehouse:
                                                            purchase.warehouse
                                                                .id,
                                                    },
                                                )}
                                                className="font-medium text-primary hover:underline"
                                            >
                                                {purchase.warehouse.name}
                                            </Link>
                                            <p className="mt-0.5 font-mono text-xs text-muted-foreground">
                                                {purchase.warehouse.code}
                                            </p>
                                        </>
                                    ) : (
                                        <span className="text-muted-foreground italic">
                                            —
                                        </span>
                                    )}
                                </CardContent>
                            </Card>

                            {purchase.note && (
                                <Card>
                                    <CardHeader className="pb-2">
                                        <CardTitle className="text-base">
                                            Note
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                            {purchase.note}
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
                    storeUrl={PurchasePaymentController.url({
                        purchase: purchase.id,
                    })}
                    dueAmount={purchase.due_amount}
                    paymentMethods={payment_methods}
                />

                <ActionDialog
                    open={receiveOpen}
                    onOpenChange={setReceiveOpen}
                    actionRoute={ReceivePurchaseController.url({
                        purchase: purchase.id,
                    })}
                    title="Receive this purchase?"
                    description="Stock will be added for all items."
                    confirmLabel="Receive purchase"
                />

                <ActionDialog
                    open={cancelOpen}
                    onOpenChange={setCancelOpen}
                    actionRoute={CancelPurchaseController.url({
                        purchase: purchase.id,
                    })}
                    title="Cancel this purchase?"
                    description={
                        purchase.status === 'received'
                            ? 'Cancelling will deduct stock.'
                            : 'The purchase will be cancelled.'
                    }
                    confirmLabel="Cancel purchase"
                    confirmVariant="destructive"
                />
            </div>
        </AppLayout>
    );
}
