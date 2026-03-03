<?php

declare(strict_types=1);

namespace App\Data\Sale;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateSaleItemData extends Data
{
    public function __construct(
        public int|Optional|null $batch_id,
        public int|Optional|null $quantity,
        public int|Optional|null $unit_price,
        public int|Optional|null $unit_cost,
    ) {}
}
