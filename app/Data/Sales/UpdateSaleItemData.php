<?php

declare(strict_types=1);

namespace App\Data\Sales;

use Spatie\LaravelData\Data;

final class UpdateSaleItemData extends Data
{
    public function __construct(
        public ?int $quantity,
        public ?int $price,
        public ?int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public ?int $total,
        public ?string $batch_number,
        public ?string $expiry_date,
    ) {}
}
