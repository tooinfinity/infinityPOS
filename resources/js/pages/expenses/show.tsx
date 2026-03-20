import { Head, router } from '@inertiajs/react';
import { Pencil, Receipt, Trash2 } from 'lucide-react';
import { useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatDateTime, formatMoney } from '@/lib/formatters';
import ExpenseController from '@/wayfinder/App/Http/Controllers/Expenses/ExpenseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    expense: App.Models.Expense;
}

export default function ExpenseShow({ expense }: Props) {
    const [deleteOpen, setDeleteOpen] = useState(false);

    return (
        <AppLayout>
            <Head title={`Expense ${expense.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <PageHeader
                        backUrl={ExpenseController.index.url()}
                        title={expense.reference_no}
                        subtitle={`Recorded ${formatDateTime(expense.created_at)}`}
                        actions={
                            <>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.visit(
                                            ExpenseController.edit.url({
                                                expense: expense.id,
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
                                    <Receipt className="h-4 w-4" /> Expense
                                    details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex justify-between text-base">
                                    <span className="text-muted-foreground">
                                        Amount
                                    </span>
                                    <span className="font-mono text-lg font-bold">
                                        {formatMoney(expense.amount)}
                                    </span>
                                </div>
                                <Separator />
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Category
                                    </span>
                                    <span className="font-medium">
                                        {expense.expense_category?.name ?? '—'}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Date
                                    </span>
                                    <span>
                                        {formatDate(expense.expense_date)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Recorded by
                                    </span>
                                    <span>{expense.user?.name ?? '—'}</span>
                                </div>
                                {expense.description && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="mb-1 text-muted-foreground">
                                                Description
                                            </p>
                                            <p className="whitespace-pre-wrap">
                                                {expense.description}
                                            </p>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <ConfirmDialog
                    open={deleteOpen}
                    onOpenChange={setDeleteOpen}
                    deleteRoute={ExpenseController.destroy.url({
                        expense: expense.id,
                    })}
                    title={`Delete expense ${expense.reference_no}?`}
                    description="This expense will be permanently deleted."
                    onSuccess={() =>
                        router.visit(ExpenseController.index.url())
                    }
                />
            </div>
        </AppLayout>
    );
}
