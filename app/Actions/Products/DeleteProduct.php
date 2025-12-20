<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final readonly class DeleteProduct
{
    public function handle(Product $product): void
    {
        $product->update([
            'tax_id' => null,
            'category_id' => null,
            'brand_id' => null,
            'created_by' => null,
        ]);
        $product->delete();
    }
}
