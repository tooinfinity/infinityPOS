<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class EnsureValidPosCartInventory implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $items = $this->data['items'] ?? [];

        if (! is_array($items) || count($items) === 0) {
            return;
        }

        $productIds = array_column($items, 'product_id');
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = $item['product_id'] ?? null;
            $batchId = $item['batch_id'] ?? null;

            if ($productId === null) {
                continue;
            }

            $product = $products->get($productId);

            if ($product === null) {
                continue;
            }

            if ($product->track_inventory && $batchId === null) {
                $fail("Cart item #{$index}: Product '{$product->name}' tracks inventory and requires a batch selection.");
            }

            if (! $product->track_inventory && $batchId !== null) {
                $fail("Cart item #{$index}: Product '{$product->name}' does not track inventory and should not have a batch selected.");
            }
        }
    }
}
