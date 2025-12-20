<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final readonly class AdjustProductPrice
{
    public function handle(Product $product, int $price): Product
    {
        $product->update([
            'price' => $price,
        ]);

        return $product;
    }
}
