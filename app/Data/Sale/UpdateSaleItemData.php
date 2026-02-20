<?php

declare(strict_types=1);

namespace App\Data\Sale;

use Spatie\LaravelData\Data;

final class UpdateSaleItemData extends Data
{
    public function __construct(
        public ?int $batch_id,
        public ?int $quantity,
        public ?int $unit_price,
        public ?int $unit_cost,
    ) {}
}
