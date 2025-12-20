<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final readonly class SetProductUnit
{
    public function handle(Product $product, ?int $unitId): Product
    {
        $product->update([
            'unit_id' => $unitId,
        ]);

        return $product;
    }
}
