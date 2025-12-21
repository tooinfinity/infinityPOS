<?php

declare(strict_types=1);

namespace App\Data\Sales;

use Spatie\LaravelData\Data;

final class UpdateSaleData extends Data
{
    public function __construct(
        public ?string $reference,
        public ?int $client_id,
        public ?int $store_id,
        public ?int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public ?int $total,
        public ?string $notes,
        public int $updated_by,
    ) {}
}
