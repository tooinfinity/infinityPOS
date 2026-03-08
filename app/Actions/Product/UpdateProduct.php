<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Data\Product\ProductData;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateProduct
{
    /**
     * @throws Throwable
     */
    public function handle(Product $product, ProductData $data): Product
    {
        return DB::transaction(static function () use ($product, $data): Product {
            $updateData = [
                'name' => $data->name ?? $product->name,
                'sku' => $data->sku ?? $product->sku,
                'barcode' => $product->barcode,
                'unit_id' => $product->unit_id,
                'category_id' => $data->category_id ?? $product->category_id,
                'brand_id' => $data->brand_id ?? $product->brand_id,
                'description' => $data->description ?? $product->description,
                'cost_price' => $data->cost_price ?? $product->cost_price,
                'selling_price' => $data->selling_price ?? $product->selling_price,
                'alert_quantity' => $data->alert_quantity ?? $product->alert_quantity,
                'track_inventory' => $data->track_inventory ?? $product->track_inventory,
                'is_active' => $data->is_active ?? $product->is_active,
            ];

            $product->update($updateData);

            return $product->refresh();
        });

    }
}
