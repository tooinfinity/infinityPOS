import { Head, router } from '@inertiajs/react';
import { Package, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import { ActiveBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/formatters';
import UnitController from '@/wayfinder/App/Http/Controllers/Products/UnitController';
import type { App, Inertia } from '@/wayfinder/types';

type UnitWithCount = App.Models.Unit & { products_count?: number };

interface Props extends Inertia.SharedData {
    unit: UnitWithCount;
}

export default function UnitShow({ unit }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);

    return (
        <AppLayout>
            <Head title={unit.name} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={UnitController.index.url()}
                        title={unit.name}
                        badges={<ActiveBadge active={unit.is_active} />}
                        subtitle={`Abbreviation: ${unit.short_name}`}
                        actions={
                            <>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.visit(
                                            UnitController.edit.url({
                                                unit: unit.id,
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

                    <div className="max-w-sm">
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Package className="h-4 w-4" /> Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Full name
                                    </span>
                                    <span className="font-medium">
                                        {unit.name}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Abbreviation
                                    </span>
                                    <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs font-medium">
                                        {unit.short_name}
                                    </span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Status
                                    </span>
                                    <ActiveBadge active={unit.is_active} />
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Products using this unit
                                    </span>
                                    <span className="font-semibold tabular-nums">
                                        {unit.products_count ?? 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Created
                                    </span>
                                    <span>{formatDate(unit.created_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={UnitController.destroy.url({ unit: unit.id })}
                    title={`Delete "${unit.name}"?`}
                    description="Products will be reassigned to the default unit (Piece) if available."
                    onSuccess={() => router.visit(UnitController.index.url())}
                />
            </div>
        </AppLayout>
    );
}
