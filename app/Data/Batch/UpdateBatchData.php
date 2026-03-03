<?php

declare(strict_types=1);

namespace App\Data\Batch;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateBatchData extends Data
{
    public function __construct(
        public string|Optional|null $batch_number,
        public int|Optional $cost_amount,
        public int|Optional $quantity,
        public DateTimeInterface|string|null|Optional $expires_at,
        public ?string $note = null,
    ) {}
}
