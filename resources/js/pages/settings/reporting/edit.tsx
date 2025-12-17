import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import settingsRoutes from '@/routes/settings';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

interface Props {
    reporting: {
        default_date_range: string;
        enable_profit_tracking: boolean;
        enable_export_reports: boolean;
    };
}

export default function ReportingSettingsEdit({ reporting }: Props) {
    const { __ } = useLanguage();
    const { data, setData, put, processing, errors } = useForm(reporting);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: __('Reporting settings'),
            href: settingsRoutes.reporting.edit().url,
        },
    ];

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(settingsRoutes.reporting.update().url, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('Reporting settings')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={__('Reporting settings')}
                        description={__(
                            'Configure reporting defaults and exports',
                        )}
                    />
                    <form onSubmit={onSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-medium">
                                    {__('Default date range')}
                                </label>
                                <input
                                    className="input input-bordered w-full"
                                    value={data.default_date_range}
                                    onChange={(e) =>
                                        setData(
                                            'default_date_range',
                                            e.target.value,
                                        )
                                    }
                                />
                                {errors.default_date_range && (
                                    <p className="mt-1 text-sm text-destructive">
                                        {errors.default_date_range}
                                    </p>
                                )}
                            </div>

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_profit_tracking}
                                    onChange={(e) =>
                                        setData(
                                            'enable_profit_tracking',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable profit tracking')}</span>
                            </label>
                            {errors.enable_profit_tracking && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_profit_tracking}
                                </p>
                            )}

                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={!!data.enable_export_reports}
                                    onChange={(e) =>
                                        setData(
                                            'enable_export_reports',
                                            e.target.checked,
                                        )
                                    }
                                />
                                <span>{__('Enable export reports')}</span>
                            </label>
                            {errors.enable_export_reports && (
                                <p className="mt-1 text-sm text-destructive">
                                    {errors.enable_export_reports}
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
