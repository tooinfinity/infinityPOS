<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

final readonly class RecalculateStockLevels
{
    /**
     * Recalculate stock levels for a product in a store based on stock movements.
     * This is useful for synchronizing inventory after manual corrections.
     */
    public function handle(Product|int $product, Store|int $store): int
    {
        $productId = $product instanceof Product ? $product->id : $product;
        $storeId = $store instanceof Store ? $store->id : $store;

        return DB::transaction(function () use ($productId, $storeId): int {
            // Get total quantity from all layers
            $totalFromLayers = InventoryLayer::query()
                ->where('product_id', $productId)
                ->where('store_id', $storeId)
                ->sum('remaining_qty');

            return (int) $totalFromLayers;
        });
    }
}
