import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import settingsRoutes from '@/routes/settings';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    sales: {
        enable_discounts: boolean;
        max_discount_percentage: number;
        require_customer_for_sale: boolean;
        enable_sale_notes: boolean;
        enable_tax_calculation: boolean;
        tax_inclusive: boolean;
    };
}

export default function SalesSettingsEdit({ sales }: Props) {
    const { __ } = useLanguage();
    const { data, setData, put, processing, errors } = useForm(sales);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: __('Sales settings'), href: settingsRoutes.sales.edit().url },
    ];

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(settingsRoutes.sales.update().url, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('Sales settings')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={__('Sales settings')}
                        description={__('Control sales behaviour and taxation')}
                    />
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_discounts}
                                    onChange={(e) =>
                                        setData(
                                            'enable_discounts',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable discounts')}</span>
                            </label>
                            {errors.enable_discounts && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_discounts}
                                </p>
                            )}

                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Max discount percentage')}
                                </label>
                                <input
                                    type="number"
                                    className="input input-bordered w-full"
                                    value={data.max_discount_percentage}
                                    onChange={(e) =>
                                        setData(
                                            'max_discount_percentage',
                                            Number(e.target.value),
                                        )
                                    }
                                />
                                {errors.max_discount_percentage && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.max_discount_percentage}
                                    </p>
                                )}
                            </div>

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.require_customer_for_sale}
                                    onChange={(e) =>
                                        setData(
                                            'require_customer_for_sale',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Require customer for sale')}</span>
                            </label>
                            {errors.require_customer_for_sale && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.require_customer_for_sale}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_sale_notes}
                                    onChange={(e) =>
                                        setData(
                                            'enable_sale_notes',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable sale notes')}</span>
                            </label>
                            {errors.enable_sale_notes && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_sale_notes}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_tax_calculation}
                                    onChange={(e) =>
                                        setData(
                                            'enable_tax_calculation',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable tax calculation')}</span>
                            </label>
                            {errors.enable_tax_calculation && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_tax_calculation}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.tax_inclusive}
                                    onChange={(e) =>
                                        setData(
                                            'tax_inclusive',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Tax inclusive')}</span>
                            </label>
                            {errors.tax_inclusive && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.tax_inclusive}
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
