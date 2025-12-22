<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use Spatie\LaravelData\Data;

final class CreateStockTransferItemData extends Data
{
    public function __construct(
        public int $product_id,
        public int $quantity,
        public ?string $batch_number,
    ) {}
}
