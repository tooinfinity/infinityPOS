<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\StockTransferItem;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class StockTransferItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public ?string $batch_number,
        public Lazy|StockTransferData $stockTransfer,
        public Lazy|ProductData $product,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(StockTransferItem $item): self
    {
        return new self(
            id: $item->id,
            quantity: $item->quantity,
            batch_number: $item->batch_number,
            stockTransfer: Lazy::whenLoaded('stockTransfer', $item, fn (): StockTransferData => StockTransferData::from($item->stockTransfer)
            ),
            product: Lazy::whenLoaded('product', $item, fn (): ProductData => ProductData::from($item->product)
            ),
            created_at: $item->created_at,
            updated_at: $item->updated_at,
        );
    }
}
