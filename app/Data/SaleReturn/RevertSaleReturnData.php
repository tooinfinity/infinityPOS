<?php

declare(strict_types=1);

namespace App\Data\SaleReturn;

use Spatie\LaravelData\Data;

final class RevertSaleReturnData extends Data
{
    public function __construct(
        public ?string $note = null,
    ) {}
}
