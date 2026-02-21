<?php

declare(strict_types=1);

namespace App\Data\PurchaseReturn;

use Spatie\LaravelData\Data;

final class CancelPurchaseReturnData extends Data
{
    public function __construct(
        public ?string $note = null,
    ) {}
}
