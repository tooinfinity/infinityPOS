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

interface Props {
    items: Array<App.Models.PurchaseItem>;
}

export default function PurchaseItemsTable({ items }: Props) {
    return (
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Product</TableHead>
                        <TableHead>Batch</TableHead>
                        <TableHead className="text-right">Quantity</TableHead>
                        <TableHead className="text-right">Unit Cost</TableHead>
                        <TableHead className="text-right">Subtotal</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {items.map((item) => (
                        <TableRow key={item.id}>
                            <TableCell>
                                <p className="text-sm font-medium">
                                    {item.product?.name ?? 'Unknown'}
                                </p>
                                <p className="font-mono text-xs text-muted-foreground">
                                    {item.product?.sku ?? '—'}
                                </p>
                            </TableCell>
                            <TableCell>
                                <span className="font-mono text-sm text-muted-foreground">
                                    {item.batch?.batch_number ?? '—'}
                                </span>
                            </TableCell>
                            <TableCell className="text-right">
                                <span className="font-mono text-sm">
                                    {item.quantity}
                                </span>
                                <span className="ml-1 text-xs text-muted-foreground">
                                    {item.product?.unit?.short_name ?? ''}
                                </span>
                            </TableCell>
                            <TableCell className="text-right font-mono text-sm">
                                {formatAmount(item.unit_cost)}
                            </TableCell>
                            <TableCell className="text-right font-mono text-sm font-medium">
                                {formatAmount(item.quantity * item.unit_cost)}
                            </TableCell>
                        </TableRow>
                    ))}
                    {items.length === 0 && (
                        <TableRow>
                            <TableCell
                                colSpan={5}
                                className="h-24 text-center text-muted-foreground"
                            >
                                No items
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
