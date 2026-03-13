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

export interface FieldConfig {
    name: string;
    label: string;
    type: 'text' | 'email' | 'textarea' | 'switch';
    required?: boolean;
    colSpan?: number;
}

interface EntityFormModalProps {
    open: boolean;
    onOpenChange: (v: boolean) => void;
    entity?: Record<string, unknown>;
    fields: FieldConfig[];
    storeUrl: string;
    updateUrl?: string;
    title: string;
    updateId?: string | number;
}

export default function EntityFormModal({
    open,
    onOpenChange,
    entity,
    fields,
    storeUrl,
    updateUrl,
    title,
    updateId,
}: EntityFormModalProps) {
    const isEditing = !!entity && !!updateId;

    const initialData: Record<string, string | boolean> = {};
    fields.forEach((field) => {
        initialData[field.name] =
            (entity?.[field.name] as string | boolean) ??
            (field.type === 'switch' ? false : '');
    });

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm(initialData);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (isEditing && updateUrl) {
            put(updateUrl, {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(storeUrl, {
                preserveScroll: true,
                onSuccess: () => {
                    onOpenChange(false);
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
                        {isEditing ? `Edit ${title}` : `New ${title}`}
                    </DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        {fields.map((field) => (
                            <div
                                key={field.name}
                                className={
                                    field.colSpan === 2
                                        ? 'col-span-2'
                                        : undefined
                                }
                            >
                                {field.type === 'switch' ? (
                                    <div className="col-span-2 flex items-center gap-3">
                                        <Switch
                                            id={field.name}
                                            checked={
                                                data[field.name] as boolean
                                            }
                                            onCheckedChange={(checked) =>
                                                setData(field.name, checked)
                                            }
                                        />
                                        <Label
                                            htmlFor={field.name}
                                            className="cursor-pointer"
                                        >
                                            {field.label}
                                        </Label>
                                    </div>
                                ) : field.type === 'textarea' ? (
                                    <div className="col-span-2 space-y-1.5">
                                        <Label>
                                            {field.label}
                                            {field.required && (
                                                <span className="text-destructive">
                                                    *
                                                </span>
                                            )}
                                        </Label>
                                        <Textarea
                                            rows={2}
                                            className="resize-none"
                                            value={data[field.name] as string}
                                            onChange={(e) =>
                                                setData(
                                                    field.name,
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors[field.name] && (
                                            <p className="text-xs text-destructive">
                                                {errors[field.name]}
                                            </p>
                                        )}
                                    </div>
                                ) : (
                                    <div
                                        className={
                                            field.colSpan === 2
                                                ? 'col-span-2 space-y-1.5'
                                                : 'space-y-1.5'
                                        }
                                    >
                                        <Label>
                                            {field.label}
                                            {field.required && (
                                                <span className="text-destructive">
                                                    *
                                                </span>
                                            )}
                                        </Label>
                                        <Input
                                            type={field.type}
                                            value={data[field.name] as string}
                                            onChange={(e) =>
                                                setData(
                                                    field.name,
                                                    e.target.value,
                                                )
                                            }
                                            required={field.required}
                                        />
                                        {errors[field.name] && (
                                            <p className="text-xs text-destructive">
                                                {errors[field.name]}
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>
                        ))}
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
                            {isEditing ? 'Save changes' : `Create ${title}`}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
