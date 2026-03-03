<?php

declare(strict_types=1);

namespace App\Data\SaleReturn;

use Spatie\LaravelData\Data;

final class UpdateSaleReturnItemData extends Data
{
    public function __construct(
        public ?int $quantity = null,
        public ?int $unit_price = null,
    ) {}
}
