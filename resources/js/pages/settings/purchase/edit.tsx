import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import settingsRoutes from '@/routes/settings';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    purchase: {
        enable_purchase_returns: boolean;
        require_supplier_for_purchase: boolean;
        enable_purchase_notes: boolean;
    };
}

export default function PurchaseSettingsEdit({ purchase }: Props) {
    const { __ } = useLanguage();
    const { data, setData, put, processing, errors } = useForm(purchase);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: __('Purchase settings'),
            href: settingsRoutes.purchase.edit().url,
        },
    ];

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(settingsRoutes.purchase.update().url, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('Purchase settings')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={__('Purchase settings')}
                        description={__(
                            'Set purchase and supplier requirements',
                        )}
                    />
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_purchase_returns}
                                    onChange={(e) =>
                                        setData(
                                            'enable_purchase_returns',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable purchase returns')}</span>
                            </label>
                            {errors.enable_purchase_returns && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_purchase_returns}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={
                                        !!data.require_supplier_for_purchase
                                    }
                                    onChange={(e) =>
                                        setData(
                                            'require_supplier_for_purchase',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>
                                    {__('Require supplier for purchase')}
                                </span>
                            </label>
                            {errors.require_supplier_for_purchase && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.require_supplier_for_purchase}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_purchase_notes}
                                    onChange={(e) =>
                                        setData(
                                            'enable_purchase_notes',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable purchase notes')}</span>
                            </label>
                            {errors.enable_purchase_notes && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_purchase_notes}
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
