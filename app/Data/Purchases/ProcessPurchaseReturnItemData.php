<?php

declare(strict_types=1);

namespace App\Data\Purchases;

use Spatie\LaravelData\Data;

final class ProcessPurchaseReturnItemData extends Data
{
    public function __construct(
        public int $product_id,
        public ?int $purchase_item_id,
        public int $quantity,
        public int $cost,
        public int $total,
        public ?string $batch_number,
    ) {}
}
