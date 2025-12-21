<?php

declare(strict_types=1);

namespace App\Data\Sales;

use Spatie\LaravelData\Data;

final class ProcessSaleReturnData extends Data
{
    /**
     * @param  array<int, ProcessSaleReturnItemData>  $items
     */
    public function __construct(
        public string $reference,
        public int $sale_id,
        public ?int $client_id,
        public int $store_id,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public ?string $reason,
        public ?string $notes,
        public array $items,
        public int $created_by,
    ) {}
}
