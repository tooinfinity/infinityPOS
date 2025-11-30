<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\Store;

final readonly class CheckProductAvailabilityAction
{
    /**
     * Execute the action.
     */
    public function handle(Product $product, Store $store, float $requiredQuantity): bool
    {
        $availableStock = $product->stores()
            ->where('store_id', $store->id)
            ->value('quantity') ?? 0;

        return $availableStock >= $requiredQuantity;
    }
}
