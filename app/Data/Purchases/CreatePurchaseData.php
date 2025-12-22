<?php

declare(strict_types=1);

namespace App\Data\Purchases;

use Spatie\LaravelData\Data;

final class CreatePurchaseData extends Data
{
    /**
     * @param  array<int, CreatePurchaseItemData>  $items
     */
    public function __construct(
        public string $reference,
        public ?int $supplier_id,
        public int $store_id,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public ?string $notes,
        public array $items,
        public int $created_by,
    ) {}
}
