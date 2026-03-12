import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatAmount, formatDateTime } from '@/lib/formatters';
import type { App } from '@/wayfinder/types';

interface Props {
    payments: Array<App.Models.Payment>;
}

export default function PurchasePaymentsTable({ payments }: Props) {
    if (payments.length === 0) {
        return null;
    }

    return (
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Date</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead className="text-right">Amount</TableHead>
                        <TableHead>Reference</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {payments.map((payment) => (
                        <TableRow key={payment.id}>
                            <TableCell className="text-sm text-muted-foreground">
                                {formatDateTime(payment.created_at)}
                            </TableCell>
                            <TableCell>
                                <span className="text-sm">
                                    {payment.payment_method?.name ?? 'Unknown'}
                                </span>
                            </TableCell>
                            <TableCell className="text-right font-mono text-sm font-medium">
                                {formatAmount(payment.amount)}
                            </TableCell>
                            <TableCell>
                                <span className="font-mono text-xs text-muted-foreground">
                                    {payment.reference_no ?? '—'}
                                </span>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}
