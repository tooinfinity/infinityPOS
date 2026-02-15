<?php

declare(strict_types=1);

namespace App\Data\StockTransfer;

use Spatie\LaravelData\Data;

final class StockTransferItemData extends Data
{
    public function __construct(
        public int $product_id,
        public ?int $batch_id,
        public int $quantity,
    ) {}
}
