import { Head, router } from '@inertiajs/react';

import AppLayout from '@/layouts/app-layout';
import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import type { App, Inertia } from '@/wayfinder/types';
import SaleFormModal from './partials/sale-form-modal';

interface Props extends Inertia.SharedData {
    customers: App.Models.Customer[];
    warehouses: App.Models.Warehouse[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
}

export default function SaleCreate({ customers, warehouses, products }: Props) {
    return (
        <AppLayout>
            <Head title="New Sale" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <SaleFormModal
                    open={true}
                    onOpenChange={(open) => {
                        if (!open) {
                            router.visit(SaleController.index.url());
                        }
                    }}
                    customers={customers}
                    warehouses={warehouses}
                    products={products}
                />
            </div>
        </AppLayout>
    );
}
