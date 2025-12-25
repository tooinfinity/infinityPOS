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
import { useState } from 'react';

interface Store {
    id: number;
    name: string;
}

interface Moneybox {
    id: number;
    name: string;
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
    const [name, setName] = useState(currentRegister?.name || '');
    const [storeId, setStoreId] = useState<string>(
        currentRegister?.store_id?.toString() || '',
    );
    const [moneyboxId, setMoneyboxId] = useState<string>(
        currentRegister?.moneybox_id?.toString() || '',
    );
    const [isProcessing, setIsProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleSave = () => {
        // Validation
        const newErrors: Record<string, string> = {};

        if (!name.trim()) {
            newErrors.name = __('Register name is required');
        }

        if (!storeId) {
            newErrors.store_id = __('Store is required');
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setIsProcessing(true);

        router.post(
            '/pos/register',
            {
                name: name.trim(),
                store_id: parseInt(storeId),
                moneybox_id: moneyboxId ? parseInt(moneyboxId) : null,
            },
            {
                onSuccess: () => {
                    onClose();
                    router.reload();
                },
                onError: (errors) => {
                    setErrors(errors);
                    setIsProcessing(false);
                },
                onFinish: () => {
                    setIsProcessing(false);
                },
            },
        );
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
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
                                setErrors((prev) => ({
                                    ...prev,
                                    name: '',
                                }));
                            }}
                            placeholder={__('e.g., Register 1, Front Desk')}
                            disabled={isProcessing}
                        />
                        {errors.name && (
                            <p className="text-sm text-destructive">
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
                                setErrors((prev) => ({
                                    ...prev,
                                    store_id: '',
                                }));
                            }}
                            disabled={isProcessing}
                        >
                            <SelectTrigger id="store">
                                <SelectValue
                                    placeholder={__('Select a store')}
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
                            <p className="text-sm text-destructive">
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
                            value={moneyboxId}
                            onValueChange={setMoneyboxId}
                            disabled={isProcessing}
                        >
                            <SelectTrigger id="moneybox">
                                <SelectValue
                                    placeholder={__('No cash drawer')}
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">
                                    {__('No cash drawer')}
                                </SelectItem>
                                {moneyboxes.map((moneybox) => (
                                    <SelectItem
                                        key={moneybox.id}
                                        value={moneybox.id.toString()}
                                    >
                                        {moneybox.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
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
                        onClick={handleSave}
                        disabled={isProcessing}
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
