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
    categories: App.Models.ExpenseCategory[];
}

export default function ExpenseCreate({ categories }: Props) {
    const today = new Date().toISOString().slice(0, 10);

    const { data, setData, post, processing, errors } = useForm({
        expense_category_id: '',
        amount: 0,
        expense_date: today,
        description: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(ExpenseController.store.url(), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="New Expense" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(ExpenseController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                New Expense
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Record a business expense
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
                                    <SelectValue placeholder="Select category" />
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
                                {errors.amount && (
                                    <p className="text-xs text-destructive">
                                        {errors.amount}
                                    </p>
                                )}
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
                                {errors.expense_date && (
                                    <p className="text-xs text-destructive">
                                        {errors.expense_date}
                                    </p>
                                )}
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
                                placeholder="Optional notes about this expense…"
                            />
                        </div>

                        <div className="flex gap-3 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(ExpenseController.index.url())
                                }
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Record expense
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
