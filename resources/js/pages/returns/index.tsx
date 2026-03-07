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
import { Eye, MoreHorizontal, RotateCcw } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Returns',
        href: '/returns',
    },
];

const statusColors: Record<string, string> = {
    completed: 'bg-green-500',
    pending: 'bg-yellow-500',
    cancelled: 'bg-red-500',
};

export default function ReturnsIndex({ returns }: { returns: any }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Returns" />
            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold">Returns</h1>
                    <p className="text-muted-foreground">
                        Manage product returns and refunds
                    </p>
                </div>
                <Button asChild>
                    <Link href="/returns/create">
                        <RotateCcw className="mr-2 h-4 w-4" />
                        New Return
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Returns</CardTitle>
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
                                        Sale
                                    </th>
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Customer
                                    </th>
                                    <th className="px-4 py-3 text-right text-sm font-medium">
                                        Amount
                                    </th>
                                    <th className="px-4 py-3 text-left text-sm font-medium">
                                        Status
                                    </th>
                                    <th className="w-[50px] px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {returns.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-4 py-8 text-center text-muted-foreground"
                                        >
                                            No returns found
                                        </td>
                                    </tr>
                                ) : (
                                    returns.data.map((returnItem: any) => (
                                        <tr
                                            key={returnItem.id}
                                            className="border-b"
                                        >
                                            <td className="px-4 py-3 font-medium">
                                                {returnItem.reference_no}
                                            </td>
                                            <td className="px-4 py-3">
                                                {new Date(
                                                    returnItem.return_date,
                                                ).toLocaleDateString()}
                                            </td>
                                            <td className="px-4 py-3">
                                                {returnItem.sale
                                                    ?.reference_no ?? 'N/A'}
                                            </td>
                                            <td className="px-4 py-3">
                                                {returnItem.sale?.customer
                                                    ?.name ?? 'N/A'}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                $
                                                {(
                                                    returnItem.total_amount /
                                                    100
                                                ).toFixed(2)}
                                            </td>
                                            <td className="px-4 py-3">
                                                <Badge
                                                    className={
                                                        statusColors[
                                                            returnItem.status
                                                        ] || 'bg-gray-500'
                                                    }
                                                >
                                                    {returnItem.status}
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
                                                                href={`/returns/${returnItem.id}`}
                                                            >
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                View
                                                            </Link>
                                                        </DropdownMenuItem>
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
