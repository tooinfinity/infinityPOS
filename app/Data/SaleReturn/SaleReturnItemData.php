<?php

declare(strict_types=1);

namespace App\Data\SaleReturn;

use Spatie\LaravelData\Data;

final class SaleReturnItemData extends Data
{
    public function __construct(
        public int $product_id,
        public int $batch_id,
        public int $quantity,
        public int $unit_price,
    ) {}
}
