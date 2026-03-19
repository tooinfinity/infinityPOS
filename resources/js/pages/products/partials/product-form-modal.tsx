import { useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';

import MediaUploader from '@/components/media-upload';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import ProductController from '@/wayfinder/App/Http/Controllers/Products/ProductController';
import type { App } from '@/wayfinder/types';

interface FormData {
    name: string;
    sku: string;
    barcode: string;
    description: string;
    category_id: string;
    brand_id: string;
    unit_id: string;
    selling_price: number;
    cost_price: number;
    track_inventory: boolean;
    alert_quantity: number;
    is_active: boolean;
}

interface Props {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    categories: App.Models.Category[];
    brands: App.Models.Brand[];
    units: App.Models.Unit[];
    product?: App.Models.Product;
}

export default function ProductFormModal({
    open,
    onOpenChange,
    categories,
    brands,
    units,
    product,
}: Props) {
    const isEditing = !!product;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<FormData>({
            name: product?.name ?? '',
            sku: product?.sku ?? '',
            barcode: product?.barcode ?? '',
            description: product?.description ?? '',
            category_id: product?.category_id?.toString() ?? '',
            brand_id: product?.brand_id?.toString() ?? '',
            unit_id: product?.unit_id?.toString() ?? '',
            selling_price: product?.selling_price ?? 0,
            cost_price: product?.cost_price ?? 0,
            track_inventory: product?.track_inventory ?? true,
            alert_quantity: product?.alert_quantity ?? 10,
            is_active: product?.is_active ?? true,
        });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (isEditing && product) {
            put(ProductController.update.url({ product: product.id }), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(ProductController.store.url(), {
                preserveScroll: true,
                onSuccess: () => {
                    onOpenChange(false);
                    reset();
                },
            });
        }
    }

    function handleClose(isOpen: boolean) {
        if (!isOpen) {
            onOpenChange(false);
            clearErrors();
            if (!isEditing) reset();
        } else {
            onOpenChange(true);
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="max-h-[90vh] w-full max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {isEditing ? `Edit — ${product?.name}` : 'New Product'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="space-y-5">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2 space-y-1.5">
                            <Label>
                                Name <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                placeholder="Product name"
                                required
                            />
                            {errors.name && (
                                <p className="text-xs text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>SKU</Label>
                            <Input
                                value={data.sku}
                                onChange={(e) =>
                                    setData('sku', e.target.value.toUpperCase())
                                }
                                placeholder="e.g. PRD-001 (auto-generated if blank)"
                                className="font-mono"
                            />
                            {errors.sku && (
                                <p className="text-xs text-destructive">
                                    {errors.sku}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Barcode</Label>
                            <Input
                                value={data.barcode}
                                onChange={(e) =>
                                    setData('barcode', e.target.value)
                                }
                                placeholder="EAN-13 (auto-generated if blank)"
                                className="font-mono"
                            />
                            {errors.barcode && (
                                <p className="text-xs text-destructive">
                                    {errors.barcode}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>
                                Unit <span className="text-destructive">*</span>
                            </Label>
                            <Select
                                value={data.unit_id}
                                onValueChange={(v) => setData('unit_id', v)}
                                required
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select unit" />
                                </SelectTrigger>
                                <SelectContent>
                                    {units.map((u) => (
                                        <SelectItem
                                            key={u.id}
                                            value={String(u.id)}
                                        >
                                            {u.name}{' '}
                                            <span className="text-xs text-muted-foreground">
                                                ({u.short_name})
                                            </span>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.unit_id && (
                                <p className="text-xs text-destructive">
                                    {errors.unit_id}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Category</Label>
                            <Select
                                value={data.category_id}
                                onValueChange={(v) => setData('category_id', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">None</SelectItem>
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
                        </div>

                        <div className="space-y-1.5">
                            <Label>Brand</Label>
                            <Select
                                value={data.brand_id}
                                onValueChange={(v) => setData('brand_id', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select brand" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">None</SelectItem>
                                    {brands.map((b) => (
                                        <SelectItem
                                            key={b.id}
                                            value={String(b.id)}
                                        >
                                            {b.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="col-span-2 space-y-1.5">
                            <Label>Description</Label>
                            <Textarea
                                rows={2}
                                className="resize-none"
                                placeholder="Optional product description"
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                            />
                        </div>

                        {isEditing && product && (
                            <div className="col-span-2 space-y-1.5">
                                <Label>Thumbnail</Label>
                                <MediaUploader
                                    modelId={product.id}
                                    modelType="product"
                                    collection="thumbnail"
                                    currentMedia={product.thumbnail}
                                    accept="image/jpeg,image/png,image/webp"
                                    maxSizeMB={5}
                                    disabled={processing}
                                />
                            </div>
                        )}
                    </div>

                    <Separator />

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-1.5">
                            <Label>
                                Selling price{' '}
                                <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                type="number"
                                min={0}
                                className="font-mono"
                                value={data.selling_price}
                                onChange={(e) =>
                                    setData(
                                        'selling_price',
                                        Number(e.target.value),
                                    )
                                }
                                placeholder="0"
                                required
                            />
                            {errors.selling_price && (
                                <p className="text-xs text-destructive">
                                    {errors.selling_price}
                                </p>
                            )}
                        </div>

                        <div className="space-y-1.5">
                            <Label>Cost price</Label>
                            <Input
                                type="number"
                                min={0}
                                className="font-mono"
                                value={data.cost_price}
                                onChange={(e) =>
                                    setData(
                                        'cost_price',
                                        Number(e.target.value),
                                    )
                                }
                                placeholder="0"
                            />
                        </div>
                    </div>

                    <Separator />

                    <div className="space-y-4">
                        <h3 className="text-sm font-semibold">
                            Stock tracking
                        </h3>
                        <div className="flex items-center gap-3">
                            <Switch
                                id="track_inventory"
                                checked={data.track_inventory}
                                onCheckedChange={(v) =>
                                    setData('track_inventory', v)
                                }
                            />
                            <Label
                                htmlFor="track_inventory"
                                className="cursor-pointer"
                            >
                                Track stock for this product
                            </Label>
                        </div>

                        {data.track_inventory && (
                            <div className="space-y-1.5">
                                <Label>Low stock alert at</Label>
                                <Input
                                    type="number"
                                    min={0}
                                    className="w-36 font-mono"
                                    value={data.alert_quantity}
                                    onChange={(e) =>
                                        setData(
                                            'alert_quantity',
                                            Number(e.target.value),
                                        )
                                    }
                                />
                                <p className="text-xs text-muted-foreground">
                                    Alert when total quantity drops below this
                                    threshold
                                </p>
                            </div>
                        )}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => handleClose(false)}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing && (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            )}
                            {isEditing ? 'Save changes' : 'Create product'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
