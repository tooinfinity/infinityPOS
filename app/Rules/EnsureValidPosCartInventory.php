<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class EnsureValidPosCartInventory implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = $value;

        if (count($items) === 0) {
            return;
        }

        $productIds = array_column($items, 'product_id');

        $products = Product::query()
            ->findMany($productIds)
            ->keyBy('id');

        foreach ($items as $index => $item) {
            /** @var int|null $productId */
            $productId = $item['product_id'] ?? null;
            /** @var int|null $batchId */
            $batchId = $item['batch_id'] ?? null;

            if ($productId === null) {
                continue;
            }

            $product = $products->get($productId);

            if (! $product instanceof Product) {
                continue;
            }

            if ($product->track_inventory && $batchId === null) {
                $fail("Cart item #{$index}: Product '{$product->name}' requires a batch.");
            }

            if (! $product->track_inventory && $batchId !== null) {
                $fail("Cart item #{$index}: Product '{$product->name}' should not have a batch.");
            }
        }
    }
}
