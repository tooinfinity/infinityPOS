import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useLanguage } from '@/hooks/use-language';
import { Clock, Loader2, Save, ShoppingCart, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface HoldSaleModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess?: () => void;
}

interface HeldSale {
    id: number;
    reference: string;
    items_count: number;
    total: number;
    held_at: string;
    note?: string;
}

export function HoldSaleModal({ isOpen, onClose }: HoldSaleModalProps) {
    const { __ } = useLanguage();
    const [note, setNote] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);

    const handleHoldSale = async () => {
        setIsProcessing(true);

        try {
            // TODO: Hold sale feature not yet implemented in backend
            // await axios.post('/pos/cart/hold', {
            //     note: note || 'Held sale',
            // });

            alert(
                __(
                    'Hold sale feature is not yet implemented. Please complete the sale or clear the cart.',
                ),
            );

            onClose();
        } catch (error: unknown) {
            console.error('Hold sale failed:', error);
            const errorMessage =
                error instanceof Error && 'response' in error
                    ? (error as { response?: { data?: { message?: string } } })
                          .response?.data?.message
                    : undefined;
            alert(errorMessage || __('Failed to hold sale. Please try again.'));
        } finally {
            setIsProcessing(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl font-semibold">
                        <Clock className="h-5 w-5" />
                        {__('Hold Sale')}
                    </DialogTitle>
                    <DialogDescription>
                        {__(
                            'Save this sale to resume later. Add an optional note to identify it.',
                        )}
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4 py-4">
                    {/* Note Input */}
                    <div className="space-y-2">
                        <Label htmlFor="hold-note">
                            {__('Note')}{' '}
                            <span className="text-muted-foreground">
                                ({__('optional')})
                            </span>
                        </Label>
                        <Input
                            id="hold-note"
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                            placeholder={__('e.g., Customer will return later')}
                            autoFocus
                        />
                    </div>

                    {/* Info Box */}
                    <div className="rounded-lg bg-muted/50 p-4 text-sm text-muted-foreground">
                        <p>
                            {__(
                                'The current cart will be saved and you can resume it later from the held sales list.',
                            )}
                        </p>
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        disabled={isProcessing}
                        className="flex-1"
                    >
                        {__('Cancel')}
                    </Button>
                    <Button
                        onClick={handleHoldSale}
                        disabled={isProcessing}
                        className="flex-1 gap-2"
                    >
                        {isProcessing ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {__('Holding...')}
                            </>
                        ) : (
                            <>
                                <Save className="h-4 w-4" />
                                {__('Hold Sale')}
                            </>
                        )}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

// Held Sales List Modal Component
interface HeldSalesListModalProps {
    isOpen: boolean;
    onClose: () => void;
    onResume?: (saleId: number) => void;
}

export function HeldSalesListModal({
    isOpen,
    onClose,
    onResume,
}: HeldSalesListModalProps) {
    const { __ } = useLanguage();
    const [heldSales, setHeldSales] = useState<HeldSale[]>([]);
    const [isLoading, setIsLoading] = useState(false);

    // Load held sales when modal opens
    useState(() => {
        if (isOpen) {
            loadHeldSales();
        }
    });

    const loadHeldSales = async () => {
        setIsLoading(true);
        try {
            // TODO: Replace with actual API endpoint
            // const response = await axios.get('/pos/held-sales');
            // setHeldSales(response.data.data);

            // Mock data for now
            setHeldSales([]);
        } catch (error) {
            console.error('Failed to load held sales:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    };

    const handleResume = async (saleId: number) => {
        try {
            // TODO: API call to resume held sale
            // await axios.post(`/pos/held-sales/${saleId}/resume`);

            onClose();
            if (onResume) {
                onResume(saleId);
            }
        } catch (error) {
            console.error('Failed to resume sale:', error);
            alert(__('Failed to resume sale. Please try again.'));
        }
    };

    const handleDelete = async (saleId: number) => {
        if (!confirm(__('Are you sure you want to delete this held sale?'))) {
            return;
        }

        try {
            // TODO: API call to delete held sale
            // await axios.delete(`/pos/held-sales/${saleId}`);

            setHeldSales((prev) => prev.filter((s) => s.id !== saleId));
        } catch (error) {
            console.error('Failed to delete held sale:', error);
            alert(__('Failed to delete held sale. Please try again.'));
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl font-semibold">
                        <ShoppingCart className="h-5 w-5" />
                        {__('Held Sales')}
                    </DialogTitle>
                    <DialogDescription>
                        {__('Resume or delete previously held sales')}
                    </DialogDescription>
                </DialogHeader>

                <div className="py-4">
                    {isLoading ? (
                        <div className="flex items-center justify-center py-12">
                            <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                        </div>
                    ) : heldSales.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-12 text-center">
                            <ShoppingCart className="mb-4 h-16 w-16 text-muted-foreground/30" />
                            <p className="text-muted-foreground">
                                {__('No held sales')}
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {__('Held sales will appear here')}
                            </p>
                        </div>
                    ) : (
                        <div className="max-h-[400px] space-y-3 overflow-y-auto">
                            {heldSales.map((sale) => (
                                <div
                                    key={sale.id}
                                    className="group flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                                >
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">
                                                {sale.reference}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {sale.items_count}{' '}
                                                {sale.items_count === 1
                                                    ? __('item')
                                                    : __('items')}
                                            </span>
                                        </div>
                                        {sale.note && (
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {sale.note}
                                            </p>
                                        )}
                                        <div className="mt-2 flex items-center gap-4">
                                            <span className="text-sm font-semibold text-primary">
                                                {formatPrice(sale.total)}
                                            </span>
                                            <span className="text-xs text-muted-foreground">
                                                {formatDate(sale.held_at)}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="flex gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                handleResume(sale.id)
                                            }
                                        >
                                            {__('Resume')}
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            className="h-9 w-9"
                                            onClick={() =>
                                                handleDelete(sale.id)
                                            }
                                        >
                                            <Trash2 className="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="flex justify-end">
                    <Button variant="outline" onClick={onClose}>
                        {__('Close')}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
