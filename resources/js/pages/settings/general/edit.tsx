import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import settingsRoutes from '@/routes/settings';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    general: {
        app_name: string;
        app_timezone: string;
        app_locale: string;
        currency_code: string;
        currency_symbol: string;
        currency_position: 'before' | 'after';
        decimal_separator: string;
        thousand_separator: string;
        decimal_places: number;
    };
}

export default function GeneralSettingsEdit({ general }: Props) {
    const { __ } = useLanguage();
    const { data, setData, put, processing, errors } = useForm(general);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: __('General settings'),
            href: settingsRoutes.general.edit().url,
        },
    ];

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(settingsRoutes.general.update().url, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('General settings')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={__('General settings')}
                        description={__('Update general application settings')}
                    />
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div>
                            <label className="mb-1 block text-sm font-medium">
                                {__('App name')}
                            </label>
                            <input
                                className="input input-bordered w-full"
                                value={data.app_name}
                                onChange={(e) =>
                                    setData('app_name', e.target.value)
                                }
                            />
                            <p className="mt-1 text-xs text-muted-foreground">
                                {__('Shown across the application')}
                            </p>
                            {errors.app_name && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.app_name}
                                </p>
                            )}
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Timezone')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.app_timezone}
                                    onChange={(e) =>
                                        setData('app_timezone', e.target.value)
                                    }
                                />
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {__('e.g. UTC')}
                                </p>
                                {errors.app_timezone && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.app_timezone}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Locale')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.app_locale}
                                    onChange={(e) =>
                                        setData('app_locale', e.target.value)
                                    }
                                />
                                {errors.app_locale && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.app_locale}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Currency code')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.currency_code}
                                    onChange={(e) =>
                                        setData('currency_code', e.target.value)
                                    }
                                />
                                {errors.currency_code && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.currency_code}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Currency symbol')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.currency_symbol}
                                    onChange={(e) =>
                                        setData(
                                            'currency_symbol',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.currency_symbol && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.currency_symbol}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Currency position')}
                                </label>
                                <select
                                    className="select select-bordered w-full"
                                    value={data.currency_position}
                                    onChange={(e) =>
                                        setData(
                                            'currency_position',
                                            e.target.value as
                                                | 'before'
                                                | 'after',
                                        )
                                    }
                                >
                                    <option value="before">
                                        {__('Before')}
                                    </option>
                                    <option value="after">{__('After')}</option>
                                </select>
                                {errors.currency_position && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.currency_position}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Decimal places')}
                                </label>
                                <input
                                    type="number"
                                    className="input input-bordered w-full"
                                    value={data.decimal_places}
                                    onChange={(e) =>
                                        setData(
                                            'decimal_places',
                                            Number(e.target.value),
                                        )
                                    }
                                />
                                {errors.decimal_places && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.decimal_places}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Decimal separator')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.decimal_separator}
                                    onChange={(e) =>
                                        setData(
                                            'decimal_separator',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.decimal_separator && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.decimal_separator}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Thousand separator')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.thousand_separator}
                                    onChange={(e) =>
                                        setData(
                                            'thousand_separator',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.thousand_separator && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.thousand_separator}
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
