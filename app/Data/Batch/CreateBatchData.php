<?php

declare(strict_types=1);

namespace App\Data\Batch;

use DateTimeInterface;
use Spatie\LaravelData\Data;

final class CreateBatchData extends Data
{
    public function __construct(
        public int $product_id,
        public int $warehouse_id,
        public ?string $batch_number,
        public int $cost_amount,
        public int $quantity,
        public DateTimeInterface|string|null $expires_at,
    ) {}
}
