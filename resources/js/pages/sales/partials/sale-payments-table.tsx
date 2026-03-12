import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { CreditCard } from 'lucide-react';

interface Props {
    payments: App.Models.Payment[];
}

export default function SalePaymentsTable({ payments }: Props) {
    return (
        <Card>
            <CardHeader className="pb-3">
                <CardTitle className="flex items-center gap-2 text-base">
                    <CreditCard className="h-4 w-4" /> Payments
                </CardTitle>
            </CardHeader>
            <CardContent className="p-0">
                {payments.length === 0 ? (
                    <div className="flex h-16 items-center justify-center text-sm text-muted-foreground">
                        No payments recorded
                    </div>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Reference</TableHead>
                                <TableHead>Method</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Amount
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {payments.map((p) => (
                                <TableRow key={p.id}>
                                    <TableCell className="font-mono text-xs">
                                        {p.reference_no}
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        {p.payment_method?.name ?? '—'}
                                    </TableCell>
                                    <TableCell className="text-sm text-muted-foreground">
                                        {formatDateTime(p.payment_date)}
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            className={
                                                p.status === 'voided'
                                                    ? 'text-xs text-muted-foreground line-through'
                                                    : 'text-xs font-medium text-emerald-600'
                                            }
                                        >
                                            {p.status}
                                        </span>
                                    </TableCell>
                                    <TableCell className="text-right font-mono text-sm">
                                        {formatAmount(p.amount)}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                )}
            </CardContent>
        </Card>
    );
}
