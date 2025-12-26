import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
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
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { useMemo } from 'react';

type StoreOption = {
    id: number;
    name: string;
};

type MoneyboxOption = {
    id: number;
    name: string;
    store_id: number | null;
};

type RegisterPayload = {
    name: string;
    store_id: number;
    moneybox_id: number | null;
    is_configured: boolean;
} | null;

interface Props {
    register: RegisterPayload;
    stores: StoreOption[];
    moneyboxes: MoneyboxOption[];
}

export default function PosRegisterSetup({
    register,
    stores,
    moneyboxes,
}: Props) {
    const { __ } = useLanguage();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: __('POS'),
            href: '/pos',
        },
        {
            title: __('Register setup'),
            href: '/pos/register',
        },
    ];

    const { data, setData, put, processing, errors } = useForm({
        name: register?.name ?? '',
        store_id: register?.store_id ?? stores[0]?.id ?? 0,
        moneybox_id: register?.moneybox_id ?? null,
    });

    const availableMoneyboxes = useMemo(
        () =>
            moneyboxes.filter(
                (m) => m.store_id === null || m.store_id === data.store_id,
            ),
        [moneyboxes, data.store_id],
    );

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/pos/register', { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('POS Register setup')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <HeadingSmall
                        title={__('Register setup')}
                        description={__(
                            'This device must be assigned to a store and a register name before using POS.',
                        )}
                    />

                    <div className="mt-6 flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() =>
                                router.delete('/pos/register/cart', {
                                    preserveScroll: true,
                                })
                            }
                        >
                            {__('Clear current cart')}
                        </Button>
                    </div>

                    <form onSubmit={onSubmit} className="mt-6 space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="register-name">
                                {__('Register name')}{' '}
                                <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                id="register-name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                placeholder={__('e.g., Register 1, Front Desk')}
                                disabled={processing}
                                className={
                                    errors.name ? 'border-destructive' : ''
                                }
                                autoFocus
                            />
                            {errors.name && (
                                <p className="text-sm font-medium text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="store">
                                {__('Store')}{' '}
                                <span className="text-destructive">*</span>
                            </Label>
                            <Select
                                value={data.store_id.toString()}
                                onValueChange={(value) =>
                                    setData('store_id', Number(value))
                                }
                                disabled={processing || stores.length === 0}
                            >
                                <SelectTrigger
                                    id="store"
                                    className={
                                        errors.store_id
                                            ? 'border-destructive'
                                            : ''
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

                        <div className="space-y-2">
                            <Label htmlFor="moneybox">
                                {__('Cash drawer')}{' '}
                                <span className="text-xs text-muted-foreground">
                                    ({__('optional')})
                                </span>
                            </Label>
                            <Select
                                value={data.moneybox_id?.toString() ?? 'none'}
                                onValueChange={(value) =>
                                    setData(
                                        'moneybox_id',
                                        value === 'none' ? null : Number(value),
                                    )
                                }
                                disabled={processing}
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
                                    {availableMoneyboxes.map((mb) => (
                                        <SelectItem
                                            key={mb.id}
                                            value={mb.id.toString()}
                                        >
                                            {mb.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {availableMoneyboxes.length === 0 &&
                                data.store_id && (
                                    <p className="text-xs text-amber-600 dark:text-amber-500">
                                        {__(
                                            'No cash drawers available for selected store',
                                        )}
                                    </p>
                                )}
                            {errors.moneybox_id && (
                                <p className="text-sm font-medium text-destructive">
                                    {errors.moneybox_id}
                                </p>
                            )}
                            <p className="text-xs text-muted-foreground">
                                {__(
                                    'Link a cash drawer to track cash transactions',
                                )}
                            </p>
                        </div>

                        <div className="flex gap-3 pt-2">
                            <Button
                                type="submit"
                                disabled={processing || stores.length === 0}
                                className="gap-2"
                            >
                                {processing ? (
                                    <>
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                        {__('Saving...')}
                                    </>
                                ) : (
                                    __('Save register')
                                )}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => router.visit('/pos')}
                                disabled={processing}
                            >
                                {__('Back to POS')}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
