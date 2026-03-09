<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Models\Payment;
use App\Models\Sale;

final readonly class PosOrderResult
{
    public function __construct(
        public Sale $sale,
        public Payment $payment,
        public int $changeAmount,
    ) {}
}
