import { router } from '@inertiajs/react';
import { Loader2, Trash2 } from 'lucide-react';
import { useState } from 'react';

import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';

// ─── Delete confirm ───────────────────────────────────────────────────────────

interface ConfirmDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** Pass a Wayfinder .url() result e.g. SaleController.destroy.url({ sale: id }) */
    deleteRoute: string;
    title?: string;
    description?: string;
    onSuccess?: () => void;
}

export default function ConfirmDialog({
    open,
    onOpenChange,
    deleteRoute,
    title = 'Delete this record?',
    description = 'This action cannot be undone.',
    onSuccess,
}: ConfirmDialogProps) {
    const [processing, setProcessing] = useState(false);

    function handleDelete() {
        setProcessing(true);
        router.delete(deleteRoute, {
            preserveScroll: true,
            onSuccess: () => {
                setProcessing(false);
                onOpenChange(false);
                onSuccess?.();
            },
            onError: () => setProcessing(false),
        });
    }

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {description}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={processing}>
                        Cancel
                    </AlertDialogCancel>
                    <Button
                        variant="destructive"
                        onClick={handleDelete}
                        disabled={processing}
                    >
                        {processing ? (
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        ) : (
                            <Trash2 className="mr-2 h-4 w-4" />
                        )}
                        Delete
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// ─── Action confirm (complete / cancel / order / etc.) ────────────────────────

interface ActionDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** Pass a Wayfinder .url() result e.g. SaleController.complete.url({ sale: id }) */
    actionRoute: string;
    method?: 'patch' | 'post';
    title: string;
    description: string;
    confirmLabel: string;
    confirmVariant?: 'default' | 'destructive' | 'outline';
    onSuccess?: () => void;
}

export function ActionDialog({
    open,
    onOpenChange,
    actionRoute,
    method = 'patch',
    title,
    description,
    confirmLabel,
    confirmVariant = 'default',
    onSuccess,
}: ActionDialogProps) {
    const [processing, setProcessing] = useState(false);

    function handleAction() {
        setProcessing(true);
        router[method](actionRoute, undefined, {
            preserveScroll: true,
            onSuccess: () => {
                setProcessing(false);
                onOpenChange(false);
                onSuccess?.();
            },
            onError: () => setProcessing(false),
        });
    }

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {description}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={processing}>
                        Cancel
                    </AlertDialogCancel>
                    <Button
                        variant={confirmVariant}
                        onClick={handleAction}
                        disabled={processing}
                    >
                        {processing && (
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        )}
                        {confirmLabel}
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
