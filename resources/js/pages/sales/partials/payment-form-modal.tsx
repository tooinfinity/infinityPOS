import { useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { formatMoney } from '@/lib/formatters';
import type { App } from '@/wayfinder/types';

interface Props {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** Wayfinder .url() result — e.g. SaleController.paymentsStore.url({ sale: id }) */
    storeUrl: string;
    dueAmount: number;
    paymentMethods: App.Models.PaymentMethod[];
}

interface FormData {
    payment_method_id: string;
    amount: string;
    note: string;
    paid_at: string;
}

export default function PaymentFormModal({
    open,
    onOpenChange,
    storeUrl,
    dueAmount,
    paymentMethods,
}: Props) {
    const today = new Date().toISOString().slice(0, 10);

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm<FormData>({
            payment_method_id: '',
            amount: String(dueAmount),
            note: '',
            paid_at: today,
        });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(storeUrl, {
            preserveScroll: true,
            onSuccess: () => {
                onOpenChange(false);
                reset();
            },
        });
    }

    function handleClose() {
        onOpenChange(false);
        clearErrors();
        reset();
    }

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Payment</DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="flex items-center justify-between rounded-lg bg-muted/50 px-4 py-3">
                        <span className="text-sm text-muted-foreground">
                            Outstanding
                        </span>
                        <span className="font-mono font-semibold text-red-600">
                            {formatMoney(dueAmount)}
                        </span>
                    </div>

                    <div className="space-y-1.5">
                        <Label>
                            Payment method{' '}
                            <span className="text-destructive">*</span>
                        </Label>
                        <Select
                            value={data.payment_method_id}
                            onValueChange={(v) =>
                                setData('payment_method_id', v)
                            }
                            required
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select method" />
                            </SelectTrigger>
                            <SelectContent>
                                {paymentMethods.map((m) => (
                                    <SelectItem key={m.id} value={String(m.id)}>
                                        {m.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.payment_method_id && (
                            <p className="text-xs text-destructive">
                                {errors.payment_method_id}
                            </p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <Label>
                            Amount <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            type="number"
                            min={1}
                            className="font-mono"
                            value={data.amount}
                            onChange={(e) => setData('amount', e.target.value)}
                            required
                        />
                        {errors.amount && (
                            <p className="text-xs text-destructive">
                                {errors.amount}
                            </p>
                        )}
                    </div>

                    <div className="space-y-1.5">
                        <Label>Payment date</Label>
                        <Input
                            type="date"
                            value={data.paid_at}
                            onChange={(e) => setData('paid_at', e.target.value)}
                        />
                    </div>

                    <div className="space-y-1.5">
                        <Label>Note</Label>
                        <Textarea
                            rows={2}
                            className="resize-none"
                            placeholder="Optional note…"
                            value={data.note}
                            onChange={(e) => setData('note', e.target.value)}
                        />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing && (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            )}
                            Record payment
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
