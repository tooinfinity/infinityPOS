import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import ExpenseCategoryController from '@/wayfinder/App/Http/Controllers/Expenses/ExpenseCategoryController';

export default function ExpenseCategoryCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        is_active: true as boolean,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(ExpenseCategoryController.store.url(), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="New Expense Category" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(
                                    ExpenseCategoryController.index.url(),
                                )
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                New Expense Category
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Add a new expense classification
                            </p>
                        </div>
                    </div>
                    <form
                        onSubmit={handleSubmit}
                        className="max-w-md space-y-4"
                    >
                        <div className="space-y-1.5">
                            <Label>
                                Name <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                placeholder="e.g. Rent, Utilities, Payroll…"
                                required
                            />
                            {errors.name && (
                                <p className="text-xs text-destructive">
                                    {errors.name}
                                </p>
                            )}
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
                                placeholder="Optional description…"
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <Switch
                                id="is_active"
                                checked={data.is_active}
                                onCheckedChange={(v) => setData('is_active', v)}
                            />
                            <Label
                                htmlFor="is_active"
                                className="cursor-pointer"
                            >
                                Active
                            </Label>
                        </div>
                        <div className="flex gap-3 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(
                                        ExpenseCategoryController.index.url(),
                                    )
                                }
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Create category
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
