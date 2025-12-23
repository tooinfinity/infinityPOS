<?php

declare(strict_types=1);

namespace App\Data\Pos;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

final class ApplyCartDiscountData extends Data
{
    public function __construct(
        #[Min(0)]
        public int $discount,
    ) {}
}
