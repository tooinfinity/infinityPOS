<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\StoreStock;
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

    public static function fromModel(StoreStock $stock): self
    {
        return new self(
            quantity: $stock->quantity,
            store: Lazy::whenLoaded('store', $stock, fn (): StoreData => StoreData::from($stock->store)
            ),
            product: Lazy::whenLoaded('product', $stock, fn (): ProductData => ProductData::from($stock->product)
            ),
            created_at: $stock->created_at,
            updated_at: $stock->updated_at,
        );
    }
}
