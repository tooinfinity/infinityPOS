<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use Spatie\LaravelData\Data;

final class PurchaseItemData extends Data
{
    public function __construct(
        public int $product_id,
        public int $quantity,
        public int $unit_cost,
    ) {}
}
