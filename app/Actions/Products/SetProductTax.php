<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final readonly class SetProductTax
{
    public function handle(Product $product, ?int $taxId): Product
    {
        $product->update([
            'tax_id' => $taxId,
        ]);

        return $product;
    }
}
