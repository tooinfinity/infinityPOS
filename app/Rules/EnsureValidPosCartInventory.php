<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Translation\PotentiallyTranslatedString;

final class EnsureValidPosCartInventory implements DataAwareRule, ValidationRule
{
    /** @var array<int|string, mixed> */
    private array $data = [];

    /**
     * @param  array<int|string, mixed>  $data
     */
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
        /** @var array<int, array<string, mixed>> $items */
        $items = $this->data['items'] ?? [];

        if (count($items) === 0) {
            return;
        }

        /** @var array<int, int> $productIds */
        $productIds = array_column($items, 'product_id');
        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $index => $item) {
            /** @var int|null $productId */
            $productId = $item['product_id'] ?? null;
            /** @var int|null $batchId */
            $batchId = $item['batch_id'] ?? null;

            if ($productId === null) {
                continue;
            }

            /** @var Product|null $product */
            $product = $products->get((int) $productId);

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
