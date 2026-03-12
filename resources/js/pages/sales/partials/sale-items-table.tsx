import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatAmount } from '@/lib/formatters';
import type { App } from '@/wayfinder/types';
import { Package } from 'lucide-react';

interface Props {
    items: App.Models.SaleItem[];
}

export default function SaleItemsTable({ items }: Props) {
    return (
        <Card>
            <CardHeader className="pb-3">
                <CardTitle className="flex items-center gap-2 text-base">
                    <Package className="h-4 w-4" /> Items
                    <span className="ml-auto text-sm font-normal text-muted-foreground">
                        {items.length} line
                        {items.length !== 1 ? 's' : ''}
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent className="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Product</TableHead>
                            <TableHead>Batch</TableHead>
                            <TableHead className="text-right">Qty</TableHead>
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
                                        {item.batch?.batch_number}
                                    </span>
                                </TableCell>
                                <TableCell className="text-right font-mono text-sm">
                                    {item.quantity}{' '}
                                    <span className="text-xs text-muted-foreground">
                                        {item.product?.unit?.short_name}
                                    </span>
                                </TableCell>
                                <TableCell className="text-right font-mono text-sm">
                                    {formatAmount(item.unit_price)}
                                </TableCell>
                                <TableCell className="text-right font-mono text-sm font-medium">
                                    {formatAmount(item.subtotal)}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    );
}
