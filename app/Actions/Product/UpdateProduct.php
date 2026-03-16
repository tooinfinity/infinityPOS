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
            $product->update([
                'name' => $data->name,
                'sku' => $data->sku ?? $product->sku,
                'barcode' => $data->barcode ?? $product->barcode,
                'unit_id' => $data->unit_id,
                'category_id' => $data->category_id,
                'brand_id' => $data->brand_id,
                'description' => $data->description,
                'cost_price' => $data->cost_price,
                'selling_price' => $data->selling_price,
                'alert_quantity' => $data->alert_quantity,
                'track_inventory' => $data->track_inventory,
                'is_active' => $data->is_active,
            ]);

            return $product->refresh();
        });
    }
}
