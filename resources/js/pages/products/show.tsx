import { Head, Link, router } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowLeft,
    BarChart3,
    Layers,
    Package,
    Tag,
} from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatDateTime, formatMoney } from '@/lib/formatters';
import BatchController from '@/wayfinder/App/Http/Controllers/Products/BatchController';
import CategoryController from '@/wayfinder/App/Http/Controllers/Products/CategoryController';
import ProductController from '@/wayfinder/App/Http/Controllers/Products/ProductController';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';
import ProductFormModal from './partials/product-form-modal';

interface StockSummary {
    warehouse_id: number;
    warehouse_name: string;
    warehouse_code: string;
    total_quantity: number;
}

interface Props extends Inertia.SharedData {
    product: App.Models.Product & { stock_quantity?: number };
    categories: App.Models.Category[];
    brands: App.Models.Brand[];
    units: App.Models.Unit[];
    stockByWarehouse: StockSummary[];
    recentMovements: (App.Models.StockMovement & {
        quantity_before: number;
        quantity_after: number;
    })[];
}

export default function ProductShow({
    product,
    categories,
    brands,
    units,
    stockByWarehouse,
    recentMovements,
}: Props) {
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const batches = product.batches ?? [];
    const isLow =
        product.track_inventory &&
        product.alert_quantity != null &&
        (product.stock_quantity ?? 0) <= product.alert_quantity;

    return (
        <AppLayout>
            <Head title={product.name} />

            <div className="flex flex-1 flex-col gap-6 overflow-y-auto p-4 md:p-6">
                {/* Header */}
                <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(ProductController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <div className="flex items-center gap-2.5">
                                <h1 className="text-xl font-semibold tracking-tight">
                                    {product.name}
                                </h1>
                                {isLow && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">
                                        <AlertTriangle className="h-3 w-3" />
                                        Low stock
                                    </span>
                                )}
                            </div>
                            <p className="mt-0.5 font-mono text-sm text-muted-foreground">
                                {product.sku}
                            </p>
                        </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setEditOpen(true)}
                        >
                            Edit
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={() => setDeleteOpen(true)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {/* Stock by warehouse */}
                        {product.track_inventory && (
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <BarChart3 className="h-4 w-4" />
                                        Stock by warehouse
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    {stockByWarehouse.length === 0 ? (
                                        <div className="flex h-16 items-center justify-center text-sm text-muted-foreground">
                                            No stock in any warehouse
                                        </div>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Warehouse
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Quantity
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {stockByWarehouse.map((s) => (
                                                    <TableRow
                                                        key={s.warehouse_id}
                                                    >
                                                        <TableCell>
                                                            <Link
                                                                href={WarehouseController.show.url(
                                                                    {
                                                                        warehouse:
                                                                            s.warehouse_id,
                                                                    },
                                                                )}
                                                                className="text-sm font-medium text-primary hover:underline"
                                                            >
                                                                {
                                                                    s.warehouse_name
                                                                }
                                                            </Link>
                                                            <span className="ml-2 font-mono text-xs text-muted-foreground">
                                                                {
                                                                    s.warehouse_code
                                                                }
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <span className="font-mono text-sm font-medium">
                                                                {
                                                                    s.total_quantity
                                                                }
                                                            </span>
                                                            <span className="ml-1 text-xs text-muted-foreground">
                                                                {
                                                                    product.unit
                                                                        ?.short_name
                                                                }
                                                            </span>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Batches */}
                        {product.track_inventory && (
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Layers className="h-4 w-4" />
                                        Batches
                                    </CardTitle>
                                    <span className="text-sm font-normal text-muted-foreground">
                                        {batches.length} batch
                                        {batches.length !== 1 ? 'es' : ''}
                                    </span>
                                </CardHeader>
                                <CardContent className="p-0">
                                    {batches.length === 0 ? (
                                        <div className="flex h-16 items-center justify-center text-sm text-muted-foreground">
                                            No batches yet
                                        </div>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Batch #
                                                    </TableHead>
                                                    <TableHead>
                                                        Warehouse
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Quantity
                                                    </TableHead>
                                                    <TableHead>
                                                        Expiry
                                                    </TableHead>
                                                    <TableHead>
                                                        Received
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {batches.map((batch) => {
                                                    const isExpired =
                                                        batch.expires_at &&
                                                        new Date(
                                                            batch.expires_at,
                                                        ) < new Date();
                                                    const isExpiringSoon =
                                                        batch.expires_at &&
                                                        !isExpired &&
                                                        new Date(
                                                            batch.expires_at,
                                                        ) <
                                                            new Date(
                                                                Date.now() +
                                                                    30 *
                                                                        24 *
                                                                        60 *
                                                                        60 *
                                                                        1000,
                                                            );

                                                    return (
                                                        <TableRow
                                                            key={batch.id}
                                                        >
                                                            <TableCell>
                                                                <Link
                                                                    href={BatchController.show.url(
                                                                        {
                                                                            batch: batch.id,
                                                                        },
                                                                    )}
                                                                    className="font-mono text-xs font-medium text-primary hover:underline"
                                                                >
                                                                    {
                                                                        batch.batch_number
                                                                    }
                                                                </Link>
                                                            </TableCell>
                                                            <TableCell>
                                                                <span className="text-sm text-muted-foreground">
                                                                    {batch
                                                                        .warehouse
                                                                        ?.name ??
                                                                        '—'}
                                                                </span>
                                                            </TableCell>
                                                            <TableCell className="text-right font-mono text-sm">
                                                                {batch.quantity}
                                                            </TableCell>
                                                            <TableCell>
                                                                {batch.expires_at ? (
                                                                    <span
                                                                        className={
                                                                            isExpired
                                                                                ? 'text-xs font-medium text-red-600'
                                                                                : isExpiringSoon
                                                                                  ? 'text-xs font-medium text-amber-600'
                                                                                  : 'text-xs text-muted-foreground'
                                                                        }
                                                                    >
                                                                        {isExpired &&
                                                                            '⚠ '}
                                                                        {formatDate(
                                                                            batch.expires_at,
                                                                        )}
                                                                    </span>
                                                                ) : (
                                                                    <span className="text-xs text-muted-foreground">
                                                                        —
                                                                    </span>
                                                                )}
                                                            </TableCell>
                                                            <TableCell className="text-sm text-muted-foreground">
                                                                {formatDate(
                                                                    batch.created_at,
                                                                )}
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                            </TableBody>
                                        </Table>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Recent stock movements */}
                        {product.track_inventory &&
                            recentMovements.length > 0 && (
                                <Card>
                                    <CardHeader className="pb-3">
                                        <CardTitle className="text-base">
                                            Recent movements
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="p-0">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Type</TableHead>
                                                    <TableHead>
                                                        Warehouse
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Before
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        Change
                                                    </TableHead>
                                                    <TableHead className="text-right">
                                                        After
                                                    </TableHead>
                                                    <TableHead>Date</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {recentMovements.map((m) => {
                                                    const diff =
                                                        m.quantity_after -
                                                        m.quantity_before;
                                                    return (
                                                        <TableRow key={m.id}>
                                                            <TableCell>
                                                                <span className="text-xs font-medium text-muted-foreground capitalize">
                                                                    {m.type.replace(
                                                                        /_/g,
                                                                        ' ',
                                                                    )}
                                                                </span>
                                                            </TableCell>
                                                            <TableCell className="text-sm text-muted-foreground">
                                                                {m.warehouse
                                                                    ?.name ??
                                                                    '—'}
                                                            </TableCell>
                                                            <TableCell className="text-right font-mono text-sm text-muted-foreground">
                                                                {
                                                                    m.quantity_before
                                                                }
                                                            </TableCell>
                                                            <TableCell className="text-right font-mono text-sm">
                                                                <span
                                                                    className={
                                                                        diff >=
                                                                        0
                                                                            ? 'text-emerald-600'
                                                                            : 'text-red-600'
                                                                    }
                                                                >
                                                                    {diff >= 0
                                                                        ? '+'
                                                                        : ''}
                                                                    {diff}
                                                                </span>
                                                            </TableCell>
                                                            <TableCell className="text-right font-mono text-sm font-medium">
                                                                {
                                                                    m.quantity_after
                                                                }
                                                            </TableCell>
                                                            <TableCell className="text-sm text-muted-foreground">
                                                                {formatDateTime(
                                                                    m.created_at,
                                                                )}
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                            </TableBody>
                                        </Table>
                                    </CardContent>
                                </Card>
                            )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Pricing */}
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Tag className="h-4 w-4" />
                                    Pricing
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Selling price
                                    </span>
                                    <span className="font-mono font-semibold">
                                        {formatMoney(product.selling_price)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Cost price
                                    </span>
                                    <span className="font-mono">
                                        {product.cost_price != null
                                            ? formatMoney(product.cost_price)
                                            : '—'}
                                    </span>
                                </div>
                                {product.cost_price != null && (
                                    <>
                                        <Separator />
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Margin
                                            </span>
                                            <span className="font-mono font-medium text-emerald-600">
                                                {formatMoney(
                                                    product.selling_price -
                                                        product.cost_price,
                                                )}
                                            </span>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {/* Details */}
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Package className="h-4 w-4" />
                                    Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Category
                                    </span>
                                    {product.category ? (
                                        <Link
                                            href={CategoryController.show.url({
                                                category: product.category.id,
                                            })}
                                            className="font-medium text-primary hover:underline"
                                        >
                                            {product.category.name}
                                        </Link>
                                    ) : (
                                        <span className="text-muted-foreground italic">
                                            None
                                        </span>
                                    )}
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Brand
                                    </span>
                                    <span className="font-medium">
                                        {product.brand?.name ?? '—'}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Unit
                                    </span>
                                    <span className="font-medium">
                                        {product.unit?.name ?? '—'}
                                    </span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Stock tracking
                                    </span>
                                    <span
                                        className={
                                            product.track_inventory
                                                ? 'font-medium text-emerald-600'
                                                : 'text-muted-foreground'
                                        }
                                    >
                                        {product.track_inventory
                                            ? 'Enabled'
                                            : 'Disabled'}
                                    </span>
                                </div>
                                {product.track_inventory && (
                                    <>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Total stock
                                            </span>
                                            <span
                                                className={`font-mono font-semibold ${isLow ? 'text-amber-600' : ''}`}
                                            >
                                                {product.stock_quantity ?? 0}
                                                <span className="ml-1 text-xs font-normal text-muted-foreground">
                                                    {product.unit?.short_name}
                                                </span>
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Alert at
                                            </span>
                                            <span className="font-mono">
                                                {product.alert_quantity ?? 0}
                                            </span>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {product.description && (
                            <Card>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-base">
                                        Description
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                        {product.description}
                                    </p>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>

            <ProductFormModal
                open={editOpen}
                onOpenChange={setEditOpen}
                categories={categories}
                brands={brands}
                units={units}
                product={product}
            />

            <ConfirmDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                deleteRoute={ProductController.destroy.url({
                    product: product.id,
                })}
                title={`Delete "${product.name}"?`}
                description="This product and all associated batches will be permanently deleted."
                onSuccess={() => router.visit(ProductController.index.url())}
            />
        </AppLayout>
    );
}
