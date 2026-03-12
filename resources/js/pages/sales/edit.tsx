import { Head, router } from '@inertiajs/react';

import SaleController from '@/wayfinder/App/Http/Controllers/Sales/SaleController';
import type { App, Inertia } from '@/wayfinder/types';
import SaleFormModal from './partials/sale-form-modal';

interface Props extends Inertia.SharedData {
    sale: App.Models.Sale;
    customers: App.Models.Customer[];
    warehouses: App.Models.Warehouse[];
    products: Array<App.Models.Product & { batches?: App.Models.Batch[] }>;
}

export default function SaleEdit({
    sale,
    customers,
    warehouses,
    products,
}: Props) {
    return (
        <>
            <Head title={`Edit Sale ${sale.reference_no}`} />

            <SaleFormModal
                open={true}
                onOpenChange={(open) => {
                    if (!open) {
                        router.visit(
                            SaleController.show.url({ sale: sale.id }),
                        );
                    }
                }}
                customers={customers}
                warehouses={warehouses}
                products={products}
                sale={sale}
            />
        </>
    );
}
