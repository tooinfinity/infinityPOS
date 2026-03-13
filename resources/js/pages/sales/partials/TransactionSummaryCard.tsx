import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatAmount, formatMoney } from '@/lib/formatters';

interface Props {
    totalAmount: number;
    paidAmount: number;
    dueAmount: number;
}

export default function TransactionSummaryCard({
    totalAmount,
    paidAmount,
    dueAmount,
}: Props) {
    return (
        <Card>
            <CardHeader className="pb-3">
                <CardTitle className="text-base">Summary</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
                <div className="flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span className="font-mono">
                        {formatMoney(totalAmount)}
                    </span>
                </div>
                <Separator />
                <div className="flex justify-between text-muted-foreground">
                    <span>Paid</span>
                    <span className="font-mono text-emerald-600">
                        {formatAmount(paidAmount)}
                    </span>
                </div>
                <div className="flex justify-between font-medium">
                    <span>Due</span>
                    <span
                        className={
                            dueAmount > 0
                                ? 'font-mono font-semibold text-red-600'
                                : 'font-mono text-muted-foreground'
                        }
                    >
                        {formatAmount(dueAmount)}
                    </span>
                </div>
            </CardContent>
        </Card>
    );
}
