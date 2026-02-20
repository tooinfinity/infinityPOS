<?php

declare(strict_types=1);

namespace App\Data\Sale;

use Spatie\LaravelData\Data;

final class CancelSaleData extends Data
{
    public function __construct(
        public bool $restock_items,
        public ?string $note,
    ) {}
}
