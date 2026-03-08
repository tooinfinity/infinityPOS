<?php

declare(strict_types=1);

namespace App\Data\Sale;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

final class SaleItemData extends Data
{
    public function __construct(
        #[IntegerType, Exists('products', 'id')]
        public int $product_id,

        #[Nullable, IntegerType, Exists('batches', 'id')]
        public ?int $batch_id,

        #[IntegerType, Min(1)]
        public int $quantity,

        #[IntegerType, Min(0)]
        public int $unit_price,

        #[IntegerType, Min(0)]
        public int $unit_cost,
    ) {}
}
