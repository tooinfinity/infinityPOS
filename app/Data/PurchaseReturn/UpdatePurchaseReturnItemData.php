<?php

declare(strict_types=1);

namespace App\Data\PurchaseReturn;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdatePurchaseReturnItemData extends Data
{
    public function __construct(
        public int|Optional $quantity = new Optional,
        public int|Optional $unit_cost = new Optional,
    ) {}
}
