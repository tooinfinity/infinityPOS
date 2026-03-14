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
import CustomerController from '@/wayfinder/App/Http/Controllers/Sales/CustomerController';
import type { App } from '@/wayfinder/types';

interface FormData {
    name: string;
    email: string;
    phone: string;
    address: string;
    city: string;
    tax_number: string;
    is_active: boolean;
}

interface Props {
    open: boolean;
    onOpenChange: (v: boolean) => void;
    customer?: App.Models.Customer;
}

type CustomerRow = App.Models.Customer & {
    sales_count?: number;
    tax_number?: string;
};

export default function CustomerFormModal({
    open,
    onOpenChange,
    customer,
}: Props) {
    const isEditing = !!customer;
    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<FormData>({
            name: customer?.name ?? '',
            email: customer?.email ?? '',
            phone: customer?.phone ?? '',
            address: customer?.address ?? '',
            city: customer?.city ?? '',
            tax_number: (customer as CustomerRow)?.tax_number ?? '',
            is_active: customer?.is_active ?? true,
        });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (isEditing && customer) {
            put(CustomerController.update.url({ customer: customer.id }), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(CustomerController.store.url(), {
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
                        {isEditing ? 'Edit customer' : 'New customer'}
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
                        <div className="space-y-1.5">
                            <Label>City</Label>
                            <Input
                                value={data.city}
                                onChange={(e) =>
                                    setData('city', e.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-1.5">
                            <Label>Tax number</Label>
                            <Input
                                value={data.tax_number}
                                onChange={(e) =>
                                    setData('tax_number', e.target.value)
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
                                id="is_active"
                                checked={data.is_active}
                                onCheckedChange={(v) => setData('is_active', v)}
                            />
                            <Label
                                htmlFor="is_active"
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
                            {isEditing ? 'Save changes' : 'Create customer'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
