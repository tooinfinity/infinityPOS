<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use Spatie\LaravelData\Data;

final class BulkStockAdjustmentData extends Data
{
    /**
     * @param  array<int, AdjustStockData>  $adjustments
     */
    public function __construct(
        public array $adjustments,
        public string $reference,
        public ?string $notes,
    ) {}
}
