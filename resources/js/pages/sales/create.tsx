import { Head, router } from '@inertiajs/react';

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
        <>
            <Head title="New Sale" />

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
        </>
    );
}
