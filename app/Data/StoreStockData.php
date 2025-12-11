<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class StoreStockData extends Data
{
    public function __construct(
        public int $quantity,
        public Lazy|StoreData $store,
        public Lazy|ProductData $product,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
