import { Head, Link, router } from '@inertiajs/react';
import { Package, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import { ActiveBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatMoney } from '@/lib/formatters';
import CategoryController from '@/wayfinder/App/Http/Controllers/Products/CategoryController';
import ProductController from '@/wayfinder/App/Http/Controllers/Products/ProductController';
import type { App, Inertia } from '@/wayfinder/types';

type CategoryWithProducts = App.Models.Category & {
    products?: App.Models.Product[];
    products_count?: number;
};

interface Props extends Inertia.SharedData {
    category: CategoryWithProducts;
}

export default function CategoryShow({ category }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const products = category.products ?? [];

    return (
        <AppLayout>
            <Head title={category.name} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={CategoryController.index.url()}
                        title={category.name}
                        badges={<ActiveBadge active={category.is_active} />}
                        subtitle={
                            category.description ??
                            `Created ${formatDate(category.created_at)}`
                        }
                        actions={
                            <>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.visit(
                                            CategoryController.edit.url({
                                                category: category.id,
                                            }),
                                        )
                                    }
                                >
                                    <Pencil className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Edit
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    onClick={() => setDeleteOpen(true)}
                                >
                                    <Trash2 className="mr-1.5 h-3.5 w-3.5" />{' '}
                                    Delete
                                </Button>
                            </>
                        }
                    />

                    <div className="grid grid-cols-3 gap-6">
                        <div className="col-span-2">
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Package className="h-4 w-4" /> Products
                                        <span className="ml-auto text-sm font-normal text-muted-foreground">
                                            {category.products_count ?? 0} total
                                        </span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    {products.length === 0 ? (
                                        <div className="flex h-20 items-center justify-center text-sm text-muted-foreground">
                                            No products in this category
                                        </div>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>
                                                        Product
                                                    </TableHead>
                                                    <TableHead>SKU</TableHead>
                                                    <TableHead className="text-right">
                                                        Price
                                                    </TableHead>
                                                    <TableHead>
                                                        Status
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {products.map((p) => (
                                                    <TableRow key={p.id}>
                                                        <TableCell>
                                                            <Link
                                                                href={ProductController.show.url(
                                                                    {
                                                                        product:
                                                                            p.id,
                                                                    },
                                                                )}
                                                                className="text-sm font-medium text-primary hover:underline"
                                                            >
                                                                {p.name}
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell>
                                                            <span className="font-mono text-xs text-muted-foreground">
                                                                {p.sku}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-right font-mono text-sm">
                                                            {formatMoney(
                                                                p.selling_price,
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            <ActiveBadge
                                                                active={
                                                                    p.is_active
                                                                }
                                                            />
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="text-base">
                                    Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Status
                                    </span>
                                    <ActiveBadge active={category.is_active} />
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Products
                                    </span>
                                    <span className="font-semibold tabular-nums">
                                        {category.products_count ?? 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Created
                                    </span>
                                    <span>
                                        {formatDate(category.created_at)}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={CategoryController.destroy.url({
                        category: category.id,
                    })}
                    title={`Delete "${category.name}"?`}
                    description="Products in this category will have their category removed."
                    onSuccess={() =>
                        router.visit(CategoryController.index.url())
                    }
                />
            </div>
        </AppLayout>
    );
}
