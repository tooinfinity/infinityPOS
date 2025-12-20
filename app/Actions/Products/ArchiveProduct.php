<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final readonly class ArchiveProduct
{
    public function handle(Product $product): Product
    {
        $product->update([
            'is_active' => false,
        ]);

        return $product;
    }
}
