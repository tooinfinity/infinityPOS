<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use Spatie\LaravelData\Data;

final class CreateInventoryLayerData extends Data
{
    public function __construct(
        public int $product_id,
        public int $store_id,
        public ?string $batch_number,
        public ?string $expiry_date,
        public int $unit_cost,
        public int $received_qty,
        public int $remaining_qty,
        public string $received_at,
    ) {}
}
