<?php

declare(strict_types=1);

namespace App\Data\Pos;

use Spatie\LaravelData\Data;

final class PosCartTotalsData extends Data
{
    public function __construct(
        public int $subtotal,
        public int $discount_total,
        public int $tax_total,
        public int $total,
    ) {}
}
