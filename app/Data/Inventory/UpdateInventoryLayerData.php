<?php

declare(strict_types=1);

namespace App\Data\Inventory;

use Spatie\LaravelData\Data;

final class UpdateInventoryLayerData extends Data
{
    public function __construct(
        public ?int $remaining_qty,
    ) {}
}
