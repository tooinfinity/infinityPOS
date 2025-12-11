<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class StockTransferItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public ?string $batch_number,
        public Lazy|StockTransferData $stockTransfer,
        public Lazy|ProductData $product,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
