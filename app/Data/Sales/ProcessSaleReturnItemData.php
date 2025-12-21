<?php

declare(strict_types=1);

namespace App\Data\Sales;

use Spatie\LaravelData\Data;

final class ProcessSaleReturnItemData extends Data
{
    public function __construct(
        public int $product_id,
        public ?int $sale_item_id,
        public int $quantity,
        public int $price,
        public int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public int $total,
    ) {}
}
