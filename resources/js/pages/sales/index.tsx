import { Link } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Eye, MoreHorizontal, Plus, RotateCcw } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Sales',
        href: '/sales',
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

export default function SalesIndex({ sales }: { sales: any }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sales" />
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold">Sales</h1>
                    <p className="text-muted-foreground">
                        Manage your sales and transactions
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href="/returns/create">
                            <RotateCcw className="mr-2 h-4 w-4" />
                            New Return
                        </Link>
                    </Button>
                    <Button asChild>
                        <Link href="/pos">
                            <Plus className="mr-2 h-4 w-4" />
                            New Sale
                        </Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Sales</CardTitle>
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
                                        Customer
                                    </th>
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Warehouse
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Total
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Paid
                                    </th>
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Status
                                    </th>
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Payment
                                    </th>
                                    <th className="w-[50px] px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {sales.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={9}
                                            className="px-4 py-8 text-center text-muted-foreground"
                                        >
                                            No sales found
                                        </td>
                                    </tr>
                                ) : (
                                    sales.data.map((sale: any) => (
                                        <tr key={sale.id} className="border-b">
                                            <td className="px-4 py-3 font-medium">
                                                {sale.reference_no}
                                            </td>
                                            <td className="px-4 py-3">
                                                {new Date(
                                                    sale.sale_date,
                                                ).toLocaleDateString()}
                                            </td>
                                            <td className="px-4 py-3">
                                                {sale.customer?.name ??
                                                    'Walk-in'}
                                            </td>
                                            <td className="px-4 py-3">
                                                {sale.warehouse?.name}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                $
                                                {(
                                                    sale.total_amount / 100
                                                ).toFixed(2)}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                $
                                                {(
                                                    sale.paid_amount / 100
                                                ).toFixed(2)}
                                            </td>
                                            <td className="px-4 py-3">
                                                <Badge
                                                    className={
                                                        statusColors[
                                                            sale.status
                                                        ] || 'bg-gray-500'
                                                    }
                                                >
                                                    {sale.status}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-3">
                                                <Badge
                                                    className={
                                                        paymentStatusColors[
                                                            sale.payment_status
                                                        ] || 'bg-gray-500'
                                                    }
                                                >
                                                    {sale.payment_status}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-3">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                        >
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/sales/${sale.id}`}
                                                            >
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        {sale.status ===
                                                            'pending' && (
                                                            <DropdownMenuItem>
                                                                <RotateCcw className="mr-2 h-4 w-4" />
                                                                Return
                                                            </DropdownMenuItem>
                                                        )}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
