<?php

declare(strict_types=1);

namespace App\Data\Sale;

use DateTimeInterface;
use Spatie\LaravelData\Data;

final class UpdateSaleData extends Data
{
    public function __construct(
        public ?int $customer_id,
        public ?DateTimeInterface $sale_date,
        public ?string $note,
    ) {}
}
