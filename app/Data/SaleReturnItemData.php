<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class SaleReturnItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $price,
        public int $cost,
        public ?int $discount,
        public ?int $tax_amount,
        public int $total,
        public Lazy|SaleReturnData $saleReturn,
        public Lazy|ProductData $product,
        public Lazy|SaleItemData|null $saleItem,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
