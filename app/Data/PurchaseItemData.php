<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PurchaseItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public int $total,
        public ?string $batch_number,
        public ?CarbonInterface $expiry_date,
        public ?int $remaining_quantity,
        public Lazy|ProductData $product,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
