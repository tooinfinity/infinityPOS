<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use Spatie\LaravelData\Data;

final class CreateStockTransferData extends Data
{
    /**
     * @param  array<int, CreateStockTransferItemData>  $items
     */
    public function __construct(
        public string $reference,
        public int $from_store_id,
        public int $to_store_id,
        public ?string $notes,
        public array $items,
        public int $created_by,
    ) {}
}
