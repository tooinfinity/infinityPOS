import { Link, useForm } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, XCircle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Returns',
        href: '/returns',
    },
    {
        title: 'Return Details',
        href: '#',
    },
];

const statusColors: Record<string, string> = {
    completed: 'bg-green-500',
    pending: 'bg-yellow-500',
    cancelled: 'bg-red-500',
};

export default function ReturnsShow({ return: returnItem }: { return: any }) {
    const { post, processing } = useForm({});

    const handleComplete = () => {
        post(`/returns/${returnItem.id}/complete`);
    };

    const handleRevert = () => {
        post(`/returns/${returnItem.id}/revert`);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Return ${returnItem.reference_no}`} />
            <div className="mb-6 flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href="/returns">
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">
                            {returnItem.reference_no}
                        </h1>
                        <p className="text-muted-foreground">
                            {new Date(returnItem.return_date).toLocaleString()}
                        </p>
                    </div>
                </div>
                <div className="flex gap-2">
                    {returnItem.status === 'pending' && (
                        <>
                            <Button
                                onClick={handleComplete}
                                disabled={processing}
                            >
                                <CheckCircle className="mr-2 h-4 w-4" />
                                Complete
                            </Button>
                            <Button
                                variant="destructive"
                                onClick={handleRevert}
                                disabled={processing}
                            >
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
                        <CardTitle>Return Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Status
                            </span>
                            <Badge
                                className={
                                    statusColors[returnItem.status] ||
                                    'bg-gray-500'
                                }
                            >
                                {returnItem.status}
                            </Badge>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Original Sale
                            </span>
                            <span>
                                {returnItem.sale?.reference_no ?? 'N/A'}
                            </span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Customer
                            </span>
                            <span>
                                {returnItem.sale?.customer?.name ?? 'N/A'}
                            </span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Warehouse
                            </span>
                            <span>{returnItem.warehouse?.name}</span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Created By
                            </span>
                            <span>{returnItem.user?.name ?? 'System'}</span>
                        </div>
                        {returnItem.note && (
                            <>
                                <Separator />
                                <div>
                                    <span className="mb-1 block text-muted-foreground">
                                        Note
                                    </span>
                                    <p className="text-sm">{returnItem.note}</p>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Refund Summary</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between text-lg">
                            <span className="font-medium">Refund Amount</span>
                            <span className="font-bold">
                                ${(returnItem.total_amount / 100).toFixed(2)}
                            </span>
                        </div>
                        <Separator />
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Refunded Amount
                            </span>
                            <span>
                                ${(returnItem.paid_amount / 100).toFixed(2)}
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Returned Items</CardTitle>
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
                                </tr>
                            </thead>
                            <tbody>
                                {returnItem.items.map((item: any) => (
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
                                        $
                                        {(
                                            returnItem.total_amount / 100
                                        ).toFixed(2)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
