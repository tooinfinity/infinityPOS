import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2, Printer, ShoppingCart } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime, formatMoney } from '@/lib/formatters';
import PosController from '@/wayfinder/App/Http/Controllers/Pos/PosController';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    sale: App.Models.Sale;
    settings?: {
        company_name?: string;
        company_address?: string;
        company_phone?: string;
    };
}

export default function PosReceipt({ sale, settings }: Props) {
    const items = sale.items ?? [];

    return (
        <AppLayout>
            <Head title={`Receipt — ${sale.reference_no}`} />
            <div className="flex h-full flex-1 flex-col items-center justify-start gap-6 p-6">
                {/* Success banner */}
                <div className="flex w-full max-w-sm items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <CheckCircle2 className="h-5 w-5 shrink-0 text-emerald-600" />
                    <div>
                        <p className="text-sm font-semibold text-emerald-800">
                            Sale completed
                        </p>
                        <p className="text-xs text-emerald-700">
                            {sale.reference_no}
                        </p>
                    </div>
                </div>

                {/* Receipt card */}
                <Card className="w-full max-w-sm shadow-md print:shadow-none">
                    <CardHeader className="items-center gap-1 pb-4 text-center">
                        {settings?.company_name && (
                            <p className="text-base font-bold">
                                {settings.company_name}
                            </p>
                        )}
                        {settings?.company_address && (
                            <p className="text-xs text-muted-foreground">
                                {settings.company_address}
                            </p>
                        )}
                        {settings?.company_phone && (
                            <p className="text-xs text-muted-foreground">
                                {settings.company_phone}
                            </p>
                        )}
                        <Separator className="my-2" />
                        <p className="font-mono text-sm font-semibold">
                            {sale.reference_no}
                        </p>
                        <p className="text-xs text-muted-foreground">
                            {formatDateTime(sale.created_at)}
                        </p>
                        {sale.customer && (
                            <p className="text-xs text-muted-foreground">
                                Customer:{' '}
                                <span className="font-medium">
                                    {sale.customer.name}
                                </span>
                            </p>
                        )}
                    </CardHeader>

                    <CardContent className="space-y-0 px-4">
                        <Separator className="mb-3" />
                        <div className="space-y-2">
                            {items.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex items-start justify-between gap-2"
                                >
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-medium">
                                            {item.product?.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {item.quantity} ×{' '}
                                            {formatMoney(item.unit_price)}
                                        </p>
                                    </div>
                                    <span className="shrink-0 font-mono text-sm font-semibold">
                                        {formatMoney(
                                            item.quantity * item.unit_price,
                                        )}
                                    </span>
                                </div>
                            ))}
                        </div>
                        <Separator className="my-3" />
                        <div className="flex items-center justify-between text-base font-bold">
                            <span>Total</span>
                            <span className="font-mono">
                                {formatMoney(sale.total_amount)}
                            </span>
                        </div>
                        {sale.payments && (
                            <p className="mt-1 text-right text-xs text-muted-foreground">
                                Paid by{' '}
                                {sale.payments[0]?.payment_method?.name ?? 'cash'}
                            </p>
                        )}
                        <Separator className="my-3" />
                        <p className="text-center text-xs text-muted-foreground">
                            Thank you for your purchase!
                        </p>
                    </CardContent>

                    <CardFooter className="flex flex-col gap-2 px-4 pb-4">
                        <Button
                            variant="outline"
                            className="w-full"
                            onClick={() => window.print()}
                        >
                            <Printer className="mr-2 h-4 w-4" /> Print receipt
                        </Button>
                    </CardFooter>
                </Card>

                {/* Navigation */}
                <div className="flex gap-3 print:hidden">
                    <Button
                        onClick={() => router.visit(PosController.index.url())}
                    >
                        <ShoppingCart className="mr-2 h-4 w-4" /> New sale
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={SaleController.show.url({ sale: sale.id })}>
                            View sale record
                        </Link>
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
