import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatAmount, formatDateTime } from '@/lib/formatters';

interface Payment {
    id: string | number;
    reference_no?: string;
    payment_method?: { name: string };
    payment_date?: string;
    created_at?: string;
    amount: number;
    status?: string;
}

interface PaymentsTableProps {
    payments: Payment[];
    variant?: 'card' | 'border';
}

export default function PaymentsTable({
    payments,
    variant = 'border',
}: PaymentsTableProps) {
    if (payments.length === 0 && variant === 'border') {
        return null;
    }

    const content = (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Reference</TableHead>
                    <TableHead>Method</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Amount</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {payments.length === 0 ? (
                    <TableRow>
                        <TableCell
                            colSpan={5}
                            className="h-16 text-center text-muted-foreground"
                        >
                            No payments recorded
                        </TableCell>
                    </TableRow>
                ) : (
                    payments.map((payment) => (
                        <TableRow key={payment.id}>
                            <TableCell className="font-mono text-xs">
                                {payment.reference_no ?? '—'}
                            </TableCell>
                            <TableCell className="text-sm">
                                {payment.payment_method?.name ?? '—'}
                            </TableCell>
                            <TableCell className="text-sm text-muted-foreground">
                                {formatDateTime(
                                    payment.payment_date ??
                                        payment.created_at ??
                                        '',
                                )}
                            </TableCell>
                            <TableCell>
                                <span
                                    className={
                                        payment.status === 'voided'
                                            ? 'text-xs text-muted-foreground line-through'
                                            : 'text-xs font-medium text-emerald-600'
                                    }
                                >
                                    {payment.status}
                                </span>
                            </TableCell>
                            <TableCell className="text-right font-mono text-sm">
                                {formatAmount(payment.amount)}
                            </TableCell>
                        </TableRow>
                    ))
                )}
            </TableBody>
        </Table>
    );

    if (variant === 'card') {
        return (
            <div className="rounded-md border">
                <div className="rounded-lg border">{content}</div>
            </div>
        );
    }

    return <div className="rounded-md border">{content}</div>;
}
