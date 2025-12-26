<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Data\Products\UpdateProductData;
use App\Models\Product;

final readonly class UpdateProduct
{
    public function handle(Product $product, UpdateProductData $data): void
    {
        $updateData = array_filter([
            'sku' => $data->sku,
            'barcode' => $data->barcode,
            'name' => $data->name,
            'description' => $data->description,
            'image' => $data->image,
            'category_id' => $data->category_id,
            'brand_id' => $data->brand_id,
            'unit_id' => $data->unit_id,
            'cost' => $data->cost,
            'price' => $data->price,
            'alert_quantity' => $data->alert_quantity,
            'has_batches' => $data->has_batches,
            'is_active' => $data->is_active,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $product->update($updateData);
    }
}
