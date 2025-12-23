import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';

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

    const availableMoneyboxes = moneyboxes.filter(
        (m) => m.store_id === null || m.store_id === data.store_id,
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

                    <form onSubmit={onSubmit} className="mt-6 space-y-4">
                        <div>
                            <label className="mb-1 block text-sm font-medium">
                                {__('Register name')}
                            </label>
                            <input
                                className="input input-bordered w-full"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium">
                                {__('Store')}
                            </label>
                            <select
                                className="select select-bordered w-full"
                                value={data.store_id}
                                onChange={(e) =>
                                    setData('store_id', Number(e.target.value))
                                }
                            >
                                {stores.map((store) => (
                                    <option key={store.id} value={store.id}>
                                        {store.name}
                                    </option>
                                ))}
                            </select>
                            {errors.store_id && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.store_id}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium">
                                {__('Cash drawer')}
                            </label>
                            <select
                                className="select select-bordered w-full"
                                value={data.moneybox_id ?? ''}
                                onChange={(e) => {
                                    const v = e.target.value;
                                    setData(
                                        'moneybox_id',
                                        v === '' ? null : Number(v),
                                    );
                                }}
                            >
                                <option value="">{__('No cash drawer')}</option>
                                {availableMoneyboxes.map((mb) => (
                                    <option key={mb.id} value={mb.id}>
                                        {mb.name}
                                    </option>
                                ))}
                            </select>
                            {errors.moneybox_id && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.moneybox_id}
                                </p>
                            )}
                        </div>

                        <div className="pt-2">
                            <Button type="submit" disabled={processing}>
                                {__('Save register')}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
