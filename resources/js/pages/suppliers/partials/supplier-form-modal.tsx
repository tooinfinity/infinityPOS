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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import SupplierController from '@/wayfinder/App/Http/Controllers/Purchases/SupplierController';
import type { App } from '@/wayfinder/types';

interface FormData {
    name: string;
    email: string;
    phone: string;
    address: string;
    is_active: boolean;
}

interface Props {
    open: boolean;
    onOpenChange: (v: boolean) => void;
    supplier?: App.Models.Supplier;
}

export default function SupplierFormModal({
    open,
    onOpenChange,
    supplier,
}: Props) {
    const isEditing = !!supplier;
    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<FormData>({
            name: supplier?.name ?? '',
            email: supplier?.email ?? '',
            phone: supplier?.phone ?? '',
            address: supplier?.address ?? '',
            is_active: supplier?.is_active ?? true,
        });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (isEditing && supplier) {
            put(SupplierController.update.url({ supplier: supplier.id }), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(SupplierController.store.url(), {
                preserveScroll: true,
                onSuccess: () => {
                    onOpenChange(false);
                    reset();
                },
            });
        }
    }

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                if (!v) {
                    onOpenChange(false);
                    clearErrors();
                }
            }}
        >
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? 'Edit supplier' : 'New supplier'}
                    </DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2 space-y-1.5">
                            <Label>
                                Name <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                required
                            />
                            {errors.name && (
                                <p className="text-xs text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>
                        <div className="space-y-1.5">
                            <Label>Email</Label>
                            <Input
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-1.5">
                            <Label>Phone</Label>
                            <Input
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
                                }
                            />
                        </div>
                        <div className="col-span-2 space-y-1.5">
                            <Label>Address</Label>
                            <Textarea
                                rows={2}
                                className="resize-none"
                                value={data.address}
                                onChange={(e) =>
                                    setData('address', e.target.value)
                                }
                            />
                        </div>
                        <div className="col-span-2 flex items-center gap-3">
                            <Switch
                                id="supplier_active"
                                checked={data.is_active}
                                onCheckedChange={(v) => setData('is_active', v)}
                            />
                            <Label
                                htmlFor="supplier_active"
                                className="cursor-pointer"
                            >
                                Active
                            </Label>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing && (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            )}
                            {isEditing ? 'Save changes' : 'Create supplier'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
