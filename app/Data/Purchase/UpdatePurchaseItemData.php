<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdatePurchaseItemData extends Data
{
    public function __construct(
        public int|Optional $quantity,
        public int|Optional $unit_cost,
    ) {}
}
