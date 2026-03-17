import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import ExpenseController from '@/wayfinder/App/Http/Controllers/Expenses/ExpenseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    expense: App.Models.Expense;
    categories: App.Models.ExpenseCategory[];
}

export default function ExpenseEdit({ expense, categories }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        expense_category_id: String(expense.expense_category_id),
        amount: expense.amount,
        expense_date: expense.expense_date
            ? String(expense.expense_date).slice(0, 10)
            : '',
        description: expense.description ?? '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(ExpenseController.update.url({ expense: expense.id }), {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout>
            <Head title={`Edit Expense — ${expense.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(
                                    ExpenseController.show.url({
                                        expense: expense.id,
                                    }),
                                )
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Edit Expense
                            </h1>
                            <p className="font-mono text-sm text-muted-foreground">
                                {expense.reference_no}
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="max-w-lg space-y-4"
                    >
                        <div className="space-y-1.5">
                            <Label>
                                Category{' '}
                                <span className="text-destructive">*</span>
                            </Label>
                            <Select
                                value={data.expense_category_id}
                                onValueChange={(v) =>
                                    setData('expense_category_id', v)
                                }
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((c) => (
                                        <SelectItem
                                            key={c.id}
                                            value={String(c.id)}
                                        >
                                            {c.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.expense_category_id && (
                                <p className="text-xs text-destructive">
                                    {errors.expense_category_id}
                                </p>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>
                                    Amount{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    type="number"
                                    min={1}
                                    className="font-mono"
                                    value={data.amount}
                                    onChange={(e) =>
                                        setData(
                                            'amount',
                                            Number(e.target.value),
                                        )
                                    }
                                    required
                                />
                            </div>
                            <div className="space-y-1.5">
                                <Label>
                                    Date{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    type="date"
                                    value={data.expense_date}
                                    onChange={(e) =>
                                        setData('expense_date', e.target.value)
                                    }
                                    required
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label>Description</Label>
                            <Textarea
                                rows={3}
                                className="resize-none"
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                            />
                        </div>

                        <div className="flex gap-3 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(
                                        ExpenseController.show.url({
                                            expense: expense.id,
                                        }),
                                    )
                                }
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Save changes
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
