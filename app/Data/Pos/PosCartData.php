<?php

declare(strict_types=1);

namespace App\Data\Pos;

use Spatie\LaravelData\Data;

final class PosCartData extends Data
{
    /**
     * @param  array<int, PosCartItemData>  $items
     */
    public function __construct(
        public array $items,
        public PosCartTotalsData $totals,
    ) {}
}
