import { Link } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { ArrowLeft, Printer, RotateCcw, XCircle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Sales',
        href: '/sales',
    },
    {
        title: 'Sale Details',
        href: '#',
    },
];

const statusColors: Record<string, string> = {
    completed: 'bg-green-500',
    pending: 'bg-yellow-500',
    cancelled: 'bg-red-500',
};

const paymentStatusColors: Record<string, string> = {
    paid: 'bg-green-500',
    partial: 'bg-yellow-500',
    unpaid: 'bg-red-500',
};

export default function SaleShow({ sale }: { sale: any }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Sale ${sale.reference_no}`} />
            <div className="mb-6 flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href="/sales">
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">
                            {sale.reference_no}
                        </h1>
                        <p className="text-muted-foreground">
                            {new Date(sale.sale_date).toLocaleString()}
                        </p>
                    </div>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline">
                        <Printer className="mr-2 h-4 w-4" />
                        Print
                    </Button>
                    {sale.status === 'pending' && (
                        <>
                            <Button variant="outline">
                                <RotateCcw className="mr-2 h-4 w-4" />
                                Return
                            </Button>
                            <Button variant="destructive">
                                <XCircle className="mr-2 h-4 w-4" />
                                Cancel
                            </Button>
                        </>
                    )}
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Sale Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Status
                            </span>
                            <Badge
                                className={
                                    statusColors[sale.status] || 'bg-gray-500'
                                }
                            >
                                {sale.status}
                            </Badge>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Payment Status
                            </span>
                            <Badge
                                className={
                                    paymentStatusColors[sale.payment_status] ||
                                    'bg-gray-500'
                                }
                            >
                                {sale.payment_status}
                            </Badge>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Customer
                            </span>
                            <span>
                                {sale.customer?.name ?? 'Walk-in Customer'}
                            </span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Warehouse
                            </span>
                            <span>{sale.warehouse?.name}</span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Created By
                            </span>
                            <span>{sale.user?.name ?? 'System'}</span>
                        </div>
                        {sale.note && (
                            <>
                                <Separator />
                                <div>
                                    <span className="mb-1 block text-muted-foreground">
                                        Note
                                    </span>
                                    <p className="text-sm">{sale.note}</p>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Payment Summary</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between text-lg">
                            <span className="font-medium">Total Amount</span>
                            <span className="font-bold">
                                ${(sale.total_amount / 100).toFixed(2)}
                            </span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Paid Amount
                            </span>
                            <span>${(sale.paid_amount / 100).toFixed(2)}</span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Due Amount
                            </span>
                            <span className="font-medium text-red-500">
                                ${(sale.due_amount / 100).toFixed(2)}
                            </span>
                        </div>
                        {sale.change_amount > 0 && (
                            <>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Change
                                    </span>
                                    <span>
                                        ${(sale.change_amount / 100).toFixed(2)}
                                    </span>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Sale Items</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="rounded-md border">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b bg-muted/50">
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Product
                                    </th>
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Batch
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Quantity
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Unit Price
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Subtotal
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Profit
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {sale.items.map((item: any) => (
                                    <tr key={item.id} className="border-b">
                                        <td className="px-4 py-3">
                                            <div className="font-medium">
                                                {item.product.name}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {item.product.sku}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            {item.batch?.batch_number ?? 'N/A'}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {item.quantity}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            $
                                            {(item.unit_price / 100).toFixed(2)}
                                        </td>
                                        <td className="px-4 py-3 text-right font-medium">
                                            ${(item.subtotal / 100).toFixed(2)}
                                        </td>
                                        <td className="px-4 py-3 text-right text-green-600">
                                            ${(item.profit / 100).toFixed(2)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot>
                                <tr className="bg-muted/50">
                                    <td
                                        colSpan={4}
                                        className="px-4 py-3 text-right font-medium"
                                    >
                                        Total
                                    </td>
                                    <td className="px-4 py-3 text-right font-bold">
                                        ${(sale.total_amount / 100).toFixed(2)}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        $
                                        {(
                                            sale.items.reduce(
                                                (sum: number, item: any) =>
                                                    sum + item.profit,
                                                0,
                                            ) / 100
                                        ).toFixed(2)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>

            {sale.payments && sale.payments.length > 0 && (
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle>Payment History</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b bg-muted/50">
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Reference
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Date
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Method
                                        </th>
                                        <th className="px-4 py-3 text-right text-sm font-medium">
                                            Amount
                                        </th>
                                        <th className="px-4 py-3 text-left text-sm font-medium">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {sale.payments.map((payment: any) => (
                                        <tr
                                            key={payment.id}
                                            className="border-b"
                                        >
                                            <td className="px-4 py-3 font-medium">
                                                {payment.reference_no}
                                            </td>
                                            <td className="px-4 py-3">
                                                {new Date(
                                                    payment.payment_date,
                                                ).toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3">
                                                {payment.payment_method}
                                            </td>
                                            <td className="px-4 py-3 text-right font-medium">
                                                $
                                                {(payment.amount / 100).toFixed(
                                                    2,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <Badge
                                                    className={
                                                        payment.status ===
                                                        'active'
                                                            ? 'bg-green-500'
                                                            : 'bg-gray-500'
                                                    }
                                                >
                                                    {payment.status}
                                                </Badge>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            )}
        </AppLayout>
    );
}
