<?php

declare(strict_types=1);

namespace App\Data\Sale;

use Spatie\LaravelData\Data;

final class CompleteSaleData extends Data
{
    public function __construct(
        public ?string $note,
    ) {}
}
