import { Head, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import WarehouseController from '@/wayfinder/App/Http/Controllers/Products/WarehouseController';
import type { App, Inertia } from '@/wayfinder/types';

interface Props extends Inertia.SharedData {
    warehouse: App.Models.Warehouse;
}

export default function WarehouseEdit({ warehouse }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: warehouse.name,
        code: warehouse.code,
        email: warehouse.email ?? '',
        phone: warehouse.phone ?? '',
        address: warehouse.address ?? '',
        city: warehouse.city ?? '',
        country: warehouse.country ?? '',
        is_active: warehouse.is_active,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(WarehouseController.update.url({ warehouse: warehouse.id }), {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout>
            <Head title={`Edit — ${warehouse.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="space-y-6">
                    <div className="flex items-center gap-3">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 shrink-0"
                            onClick={() =>
                                router.visit(
                                    WarehouseController.show.url({
                                        warehouse: warehouse.id,
                                    }),
                                )
                            }
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Edit Warehouse
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {warehouse.name}
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="max-w-2xl space-y-6"
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
                                    Code{' '}
                                    <span className="text-destructive">*</span>
                                </Label>
                                <Input
                                    value={data.code}
                                    onChange={(e) =>
                                        setData(
                                            'code',
                                            e.target.value.toUpperCase(),
                                        )
                                    }
                                    className="font-mono"
                                    required
                                />
                                {errors.code && (
                                    <p className="text-xs text-destructive">
                                        {errors.code}
                                    </p>
                                )}
                            </div>
                            <div className="space-y-1.5">
                                <Label>Email</Label>
                                <Input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                />
                            </div>
                            <div className="space-y-1.5">
                                <Label>Phone</Label>
                                <Input
                                    value={data.phone}
                                    onChange={(e) =>
                                        setData('phone', e.target.value)
                                    }
                                />
                            </div>
                            <div className="space-y-1.5">
                                <Label>City</Label>
                                <Input
                                    value={data.city}
                                    onChange={(e) =>
                                        setData('city', e.target.value)
                                    }
                                />
                            </div>
                            <div className="space-y-1.5">
                                <Label>Country</Label>
                                <Input
                                    value={data.country}
                                    onChange={(e) =>
                                        setData('country', e.target.value)
                                    }
                                />
                            </div>
                            <div className="col-span-2 space-y-1.5">
                                <Label>Address</Label>
                                <Input
                                    value={data.address}
                                    onChange={(e) =>
                                        setData('address', e.target.value)
                                    }
                                />
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

                        <div className="flex gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    router.visit(
                                        WarehouseController.show.url({
                                            warehouse: warehouse.id,
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
