<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Models\Batch;

final readonly class TransferResult
{
    public function __construct(
        public Batch $source,
        public Batch $destination,
    ) {}
}
