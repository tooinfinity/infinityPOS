<?php

declare(strict_types=1);

namespace App\Data\Pos;

use Spatie\LaravelData\Data;

final class PosCartItemData extends Data
{
    public function __construct(
        public string $line_id,
        public int $product_id,
        public string $name,
        public int $unit_price,
        public int $quantity,
        public int $line_subtotal,
    ) {}
}
