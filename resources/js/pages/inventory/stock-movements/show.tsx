import { Head, Link } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    MoveHorizontal,
    SlidersHorizontal,
} from 'lucide-react';

import PageHeader from '@/components/page-header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/formatters';
import StockMovementController from '@/wayfinder/App/Http/Controllers/Inventory/StockMovementController';
import BatchController from '@/wayfinder/App/Http/Controllers/Products/BatchController';
import ProductController from '@/wayfinder/App/Http/Controllers/Products/ProductController';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    movement: App.Models.StockMovement;
}

const TYPE_ICON: Record<string, React.ReactNode> = {
    in: <ArrowDownCircle className="h-5 w-5 text-emerald-600" />,
    out: <ArrowUpCircle className="h-5 w-5 text-red-600" />,
    transfer: <MoveHorizontal className="h-5 w-5 text-blue-600" />,
    adjustment: <SlidersHorizontal className="h-5 w-5 text-amber-600" />,
};

const TYPE_LABEL: Record<string, string> = {
    in: 'Stock In',
    out: 'Stock Out',
    transfer: 'Transfer',
    adjustment: 'Adjustment',
};

export default function StockMovementShow({ movement }: Props) {
    const change = movement.current_quantity - movement.previous_quantity;

    return (
        <AppLayout>
            <Head title={`Movement #${movement.id}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={StockMovementController.index.url()}
                        title={`Movement #${movement.id}`}
                        badges={
                            <span className="flex items-center gap-1.5">
                                {TYPE_ICON[movement.type]}
                                <span className="text-sm font-semibold capitalize">
                                    {TYPE_LABEL[movement.type] ?? movement.type}
                                </span>
                            </span>
                        }
                        subtitle={`Recorded ${formatDateTime(movement.created_at)}`}
                    />

                    <div className="max-w-sm">
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="text-base">
                                    Movement details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Product
                                    </span>
                                    {movement.product ? (
                                        <Link
                                            href={ProductController.show.url({
                                                product: movement.product.id,
                                            })}
                                            className="font-medium text-primary hover:underline"
                                        >
                                            {movement.product.name}
                                        </Link>
                                    ) : (
                                        <span className="text-muted-foreground italic">
                                            —
                                        </span>
                                    )}
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Warehouse
                                    </span>
                                    {movement.warehouse ? (
                                        <Link
                                            href={WarehouseController.show.url({
                                                warehouse:
                                                    movement.warehouse.id,
                                            })}
                                            className="font-medium text-primary hover:underline"
                                        >
                                            {movement.warehouse.name}
                                        </Link>
                                    ) : (
                                        <span className="text-muted-foreground italic">
                                            —
                                        </span>
                                    )}
                                </div>
                                {movement.batch && (
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Batch
                                        </span>
                                        <Link
                                            href={BatchController.show.url({
                                                batch: movement.batch.id,
                                            })}
                                            className="font-mono text-xs font-medium text-primary hover:underline"
                                        >
                                            {movement.batch.batch_number}
                                        </Link>
                                    </div>
                                )}
                                <Separator />
                                <div className="grid grid-cols-3 gap-2 text-center">
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Before
                                        </p>
                                        <p className="font-mono font-semibold">
                                            {movement.previous_quantity}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Change
                                        </p>
                                        <p
                                            className={`font-mono font-bold ${change >= 0 ? 'text-emerald-600' : 'text-red-600'}`}
                                        >
                                            {change >= 0 ? '+' : ''}
                                            {change}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            After
                                        </p>
                                        <p className="font-mono font-semibold">
                                            {movement.current_quantity}
                                        </p>
                                    </div>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Recorded by
                                    </span>
                                    <span>{movement.user?.name ?? '—'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Date
                                    </span>
                                    <span>
                                        {formatDateTime(movement.created_at)}
                                    </span>
                                </div>
                                {movement.note && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="mb-1 text-muted-foreground">
                                                Note
                                            </p>
                                            <p className="whitespace-pre-wrap">
                                                {movement.note}
                                            </p>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
