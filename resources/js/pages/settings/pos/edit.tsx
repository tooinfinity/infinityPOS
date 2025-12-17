import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import settingsRoutes from '@/routes/settings';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    pos: {
        enable_barcode_scanner: boolean;
        enable_receipt_printer: boolean;
        auto_print_receipt: boolean;
        default_payment_method: string;
        enable_customer_display: boolean;
        receipt_header: string;
        receipt_footer: string;
    };
}

export default function PosSettingsEdit({ pos }: Props) {
    const { __ } = useLanguage();
    const { data, setData, put, processing, errors } = useForm(pos);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: __('POS settings'), href: settingsRoutes.pos.edit().url },
    ];

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(settingsRoutes.pos.update().url, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('POS settings')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={__('POS settings')}
                        description={__('Configure point-of-sale options')}
                    />
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_barcode_scanner}
                                    onChange={(e) =>
                                        setData(
                                            'enable_barcode_scanner',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable barcode scanner')}</span>
                            </label>
                            {errors.enable_barcode_scanner && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_barcode_scanner}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_receipt_printer}
                                    onChange={(e) =>
                                        setData(
                                            'enable_receipt_printer',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable receipt printer')}</span>
                            </label>
                            {errors.enable_receipt_printer && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_receipt_printer}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.auto_print_receipt}
                                    onChange={(e) =>
                                        setData(
                                            'auto_print_receipt',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Auto print receipt')}</span>
                            </label>
                            {errors.auto_print_receipt && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.auto_print_receipt}
                                </p>
                            )}

                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Default payment method')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.default_payment_method}
                                    onChange={(e) =>
                                        setData(
                                            'default_payment_method',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.default_payment_method && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.default_payment_method}
                                    </p>
                                )}
                            </div>

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_customer_display}
                                    onChange={(e) =>
                                        setData(
                                            'enable_customer_display',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable customer display')}</span>
                            </label>
                            {errors.enable_customer_display && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_customer_display}
                                </p>
                            )}
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Receipt header')}
                                </label>
                                <textarea
                                    className="textarea textarea-bordered w-full"
                                    value={data.receipt_header}
                                    onChange={(e) =>
                                        setData(
                                            'receipt_header',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.receipt_header && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.receipt_header}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Receipt footer')}
                                </label>
                                <textarea
                                    className="textarea textarea-bordered w-full"
                                    value={data.receipt_footer}
                                    onChange={(e) =>
                                        setData(
                                            'receipt_footer',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.receipt_footer && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.receipt_footer}
                                    </p>
                                )}
                            </div>
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
