<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Data\Products\CreateProductData;
use App\Models\Product;

final readonly class CreateProduct
{
    public function handle(CreateProductData $data): Product
    {
        return Product::query()->create([
            'sku' => $data->sku,
            'barcode' => $data->barcode,
            'name' => $data->name,
            'description' => $data->description,
            'image' => $data->image,
            'category_id' => $data->category_id,
            'brand_id' => $data->brand_id,
            'unit_id' => $data->unit_id,
            'tax_id' => $data->tax_id,
            'cost' => $data->cost,
            'price' => $data->price,
            'alert_quantity' => $data->alert_quantity,
            'has_batches' => $data->has_batches,
            'is_active' => $data->is_active,
            'created_by' => $data->created_by,
        ]);
    }
}
