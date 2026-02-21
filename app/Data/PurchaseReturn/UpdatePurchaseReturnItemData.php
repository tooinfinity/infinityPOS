<?php

declare(strict_types=1);

namespace App\Data\PurchaseReturn;

use Spatie\LaravelData\Data;

final class UpdatePurchaseReturnItemData extends Data
{
    public function __construct(
        public ?int $quantity = null,
        public ?int $unit_cost = null,
    ) {}
}
