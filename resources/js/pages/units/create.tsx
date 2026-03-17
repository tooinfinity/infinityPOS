import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import UnitController from '@/wayfinder/App/Http/Controllers/Products/UnitController';

export default function UnitCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        short_name: '',
        is_active: true as boolean,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(UnitController.store.url(), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="New Unit" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(UnitController.index.url())
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                New Unit
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Add a new unit of measure
                            </p>
                        </div>
                    </div>
                    <form
                        onSubmit={handleSubmit}
                        className="max-w-md space-y-4"
                    >
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label>
                                    Name{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="e.g. Kilogram, Piece…"
                                    required
                                />
                                {errors.name && (
                                    <p className="text-xs text-destructive">
                                        {errors.name}
                                    </p>
                                )}
                            </div>
                            <div className="space-y-1.5">
                                <Label>
                                    Abbreviation{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    value={data.short_name}
                                    onChange={(e) =>
                                        setData(
                                            'short_name',
                                            e.target.value.toLowerCase(),
                                        )
                                    }
                                    placeholder="e.g. kg, pc, m…"
                                    className="font-mono"
                                    required
                                />
                                {errors.short_name && (
                                    <p className="text-xs text-destructive">
                                        {errors.short_name}
                                    </p>
                                )}
                            </div>
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
                                    router.visit(UnitController.index.url())
                                }
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing && (
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                )}
                                Create unit
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
