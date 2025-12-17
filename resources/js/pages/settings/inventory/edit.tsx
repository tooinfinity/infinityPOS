import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import settingsRoutes from '@/routes/settings';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    inventory: {
        enable_batch_tracking: boolean;
        enable_expiry_tracking: boolean;
        low_stock_threshold: number;
        enable_stock_alerts: boolean;
        auto_deduct_stock: boolean;
    };
}

export default function InventorySettingsEdit({ inventory }: Props) {
    const { __ } = useLanguage();
    const { data, setData, put, processing, errors } = useForm(inventory);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: __('Inventory settings'),
            href: settingsRoutes.inventory.edit().url,
        },
    ];

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(settingsRoutes.inventory.update().url, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('Inventory settings')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={__('Inventory settings')}
                        description={__(
                            'Configure inventory and stock behaviour',
                        )}
                    />
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_batch_tracking}
                                    onChange={(e) =>
                                        setData(
                                            'enable_batch_tracking',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable batch tracking')}</span>
                            </label>
                            {errors.enable_batch_tracking && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_batch_tracking}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_expiry_tracking}
                                    onChange={(e) =>
                                        setData(
                                            'enable_expiry_tracking',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable expiry tracking')}</span>
                            </label>
                            {errors.enable_expiry_tracking && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_expiry_tracking}
                                </p>
                            )}

                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Low stock threshold')}
                                </label>
                                <input
                                    type="number"
                                    className="input input-bordered w-full"
                                    value={data.low_stock_threshold}
                                    onChange={(e) =>
                                        setData(
                                            'low_stock_threshold',
                                            Number(e.target.value),
                                        )
                                    }
                                />
                                {errors.low_stock_threshold && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.low_stock_threshold}
                                    </p>
                                )}
                            </div>

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_stock_alerts}
                                    onChange={(e) =>
                                        setData(
                                            'enable_stock_alerts',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable stock alerts')}</span>
                            </label>
                            {errors.enable_stock_alerts && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_stock_alerts}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.auto_deduct_stock}
                                    onChange={(e) =>
                                        setData(
                                            'auto_deduct_stock',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Auto deduct stock')}</span>
                            </label>
                            {errors.auto_deduct_stock && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.auto_deduct_stock}
                                </p>
                            )}
                        </div>

                        <div className="pt-2">
                            <Button type="submit" disabled={processing}>
                                {__('Save changes')}
                            </Button>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
