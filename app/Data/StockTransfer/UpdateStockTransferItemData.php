<?php

declare(strict_types=1);

namespace App\Data\StockTransfer;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateStockTransferItemData extends Data
{
    public function __construct(
        public int|Optional|null $batch_id,
        public int|Optional $quantity,
    ) {}
}
