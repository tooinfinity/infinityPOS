import { useLanguage } from '@/hooks/use-language';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function PosIndex() {
    const { __ } = useLanguage();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: __('POS'),
            href: '/pos',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('POS')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div className="flex items-center justify-between gap-4">
                        <h1 className="text-xl font-semibold">
                            {__('Point of Sale')}
                        </h1>
                        <a
                            href="/pos/register"
                            className="text-sm underline underline-offset-4"
                        >
                            {__('Change register')}
                        </a>
                    </div>
                    <p className="mt-2 text-sm text-muted-foreground">
                        {__('POS screen is not implemented yet.')}
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
