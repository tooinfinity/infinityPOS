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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLanguage } from '@/hooks/use-language';
import { router } from '@inertiajs/react';
import { Loader2, Settings } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface Store {
    id: number;
    name: string;
}

interface Moneybox {
    id: number;
    name: string;
    store_id?: number | null;
}

interface RegisterSetupModalProps {
    isOpen: boolean;
    onClose: () => void;
    stores: Store[];
    moneyboxes: Moneybox[];
    currentRegister?: {
        id: number;
        name: string;
        store_id: number;
        moneybox_id: number | null;
    } | null;
}

export function RegisterSetupModal({
    isOpen,
    onClose,
    stores,
    moneyboxes,
    currentRegister,
}: RegisterSetupModalProps) {
    const { __ } = useLanguage();
    const [name, setName] = useState('');
    const [storeId, setStoreId] = useState<string>('');
    const [moneyboxId, setMoneyboxId] = useState<string>('');
    const [isProcessing, setIsProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    // Reset form when modal opens or register changes
    useEffect(() => {
        if (isOpen) {
            setName(currentRegister?.name || '');
            setStoreId(
                currentRegister?.store_id?.toString() ||
                    stores[0]?.id?.toString() ||
                    '',
            );
            setMoneyboxId(currentRegister?.moneybox_id?.toString() || '');
            setErrors({});
            setIsProcessing(false);
        }
    }, [isOpen, currentRegister, stores]);

    // Filter moneyboxes by selected store (or store_id is null = available for all stores)
    const availableMoneyboxes = useMemo(() => {
        if (!storeId) return moneyboxes;
        const selectedStoreId = parseInt(storeId);
        return moneyboxes.filter(
            (mb) => mb.store_id === null || mb.store_id === selectedStoreId,
        );
    }, [moneyboxes, storeId]);

    // Reset moneybox selection if current selection is no longer available
    useEffect(() => {
        if (
            moneyboxId &&
            !availableMoneyboxes.find((mb) => mb.id.toString() === moneyboxId)
        ) {
            setMoneyboxId('');
        }
    }, [availableMoneyboxes, moneyboxId]);

    const handleSave = () => {
        // Validation
        const newErrors: Record<string, string> = {};

        if (!name.trim()) {
            newErrors.name = __('Register name is required');
        }

        if (name.trim().length < 2) {
            newErrors.name = __('Register name must be at least 2 characters');
        }

        if (!storeId) {
            newErrors.store_id = __('Store is required');
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setIsProcessing(true);

        // Use PUT method to match the route
        router.put(
            '/pos/register',
            {
                name: name.trim(),
                store_id: parseInt(storeId),
                moneybox_id: moneyboxId ? parseInt(moneyboxId) : null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    onClose();
                },
                onError: (errors) => {
                    setErrors(errors as Record<string, string>);
                },
                onFinish: () => {
                    setIsProcessing(false);
                },
            },
        );
    };

    const handleClose = () => {
        if (!isProcessing) {
            onClose();
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl font-semibold">
                        <Settings className="h-5 w-5" />
                        {__('Register Setup')}
                    </DialogTitle>
                    <DialogDescription>
                        {__(
                            'Configure your POS register settings. This will be used for all transactions from this device.',
                        )}
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4 py-4">
                    {/* Register Name */}
                    <div className="space-y-2">
                        <Label htmlFor="register-name">
                            {__('Register Name')}{' '}
                            <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="register-name"
                            value={name}
                            onChange={(e) => {
                                setName(e.target.value);
                                if (errors.name) {
                                    setErrors((prev) => ({
                                        ...prev,
                                        name: '',
                                    }));
                                }
                            }}
                            placeholder={__('e.g., Register 1, Front Desk')}
                            disabled={isProcessing}
                            className={errors.name ? 'border-destructive' : ''}
                            autoFocus
                        />
                        {errors.name && (
                            <p className="text-sm font-medium text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    {/* Store Selection */}
                    <div className="space-y-2">
                        <Label htmlFor="store">
                            {__('Store')}{' '}
                            <span className="text-destructive">*</span>
                        </Label>
                        <Select
                            value={storeId}
                            onValueChange={(value) => {
                                setStoreId(value);
                                if (errors.store_id) {
                                    setErrors((prev) => ({
                                        ...prev,
                                        store_id: '',
                                    }));
                                }
                            }}
                            disabled={isProcessing || stores.length === 0}
                        >
                            <SelectTrigger
                                id="store"
                                className={
                                    errors.store_id ? 'border-destructive' : ''
                                }
                            >
                                <SelectValue
                                    placeholder={
                                        stores.length > 0
                                            ? __('Select a store')
                                            : __('No stores available')
                                    }
                                />
                            </SelectTrigger>
                            <SelectContent>
                                {stores.map((store) => (
                                    <SelectItem
                                        key={store.id}
                                        value={store.id.toString()}
                                    >
                                        {store.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.store_id && (
                            <p className="text-sm font-medium text-destructive">
                                {errors.store_id}
                            </p>
                        )}
                    </div>

                    {/* Cash Drawer (Moneybox) */}
                    <div className="space-y-2">
                        <Label htmlFor="moneybox">
                            {__('Cash Drawer')}{' '}
                            <span className="text-xs text-muted-foreground">
                                ({__('optional')})
                            </span>
                        </Label>
                        <Select
                            value={moneyboxId || 'none'}
                            onValueChange={(value) =>
                                setMoneyboxId(value === 'none' ? '' : value)
                            }
                            disabled={isProcessing}
                        >
                            <SelectTrigger id="moneybox">
                                <SelectValue
                                    placeholder={__('No cash drawer')}
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">
                                    {__('No cash drawer')}
                                </SelectItem>
                                {availableMoneyboxes.map((moneybox) => (
                                    <SelectItem
                                        key={moneybox.id}
                                        value={moneybox.id.toString()}
                                    >
                                        {moneybox.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {availableMoneyboxes.length === 0 && storeId && (
                            <p className="text-xs text-amber-600 dark:text-amber-500">
                                {__(
                                    'No cash drawers available for selected store',
                                )}
                            </p>
                        )}
                        <p className="text-xs text-muted-foreground">
                            {__(
                                'Link a cash drawer to track cash transactions',
                            )}
                        </p>
                    </div>

                    {/* Info Box */}
                    {currentRegister && (
                        <div className="rounded-lg border border-blue-500/20 bg-blue-500/10 p-3 text-sm">
                            <p className="text-blue-700 dark:text-blue-400">
                                {__(
                                    'Updating register settings. This will affect all future transactions from this device.',
                                )}
                            </p>
                        </div>
                    )}

                    {!currentRegister && stores.length === 0 && (
                        <div className="rounded-lg border border-amber-500/20 bg-amber-500/10 p-3 text-sm">
                            <p className="text-amber-700 dark:text-amber-400">
                                {__(
                                    'Please create at least one store before setting up a register.',
                                )}
                            </p>
                        </div>
                    )}
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                    <Button
                        variant="outline"
                        onClick={handleClose}
                        disabled={isProcessing}
                        className="flex-1"
                    >
                        {__('Cancel')}
                    </Button>
                    <Button
                        onClick={handleSave}
                        disabled={isProcessing || stores.length === 0}
                        className="flex-1 gap-2"
                    >
                        {isProcessing ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {__('Saving...')}
                            </>
                        ) : (
                            <>
                                <Settings className="h-4 w-4" />
                                {__(
                                    currentRegister
                                        ? 'Update Register'
                                        : 'Save Register',
                                )}
                            </>
                        )}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
