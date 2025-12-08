<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\PurchaseReturnItem;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PurchaseReturnItemData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public int $cost,
        public int $total,
        public ?string $batch_number,
        public Lazy|PurchaseReturnData $purchaseReturn,
        public Lazy|ProductData $product,
        public Lazy|PurchaseItemData|null $purchaseItem,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(PurchaseReturnItem $item): self
    {
        return new self(
            id: $item->id,
            quantity: $item->quantity,
            cost: $item->cost,
            total: $item->total,
            batch_number: $item->batch_number,
            purchaseReturn: Lazy::whenLoaded('purchaseReturn', $item, fn (): PurchaseReturnData => PurchaseReturnData::from($item->purchaseReturn)
            ),
            product: Lazy::whenLoaded('product', $item, fn (): ProductData => ProductData::from($item->product)
            ),
            purchaseItem: Lazy::whenLoaded('purchaseItem', $item, fn (): ?PurchaseItemData => $item->purchaseItem ? PurchaseItemData::from($item->purchaseItem) : null
            ),
            created_at: $item->created_at,
            updated_at: $item->updated_at,
        );
    }
}
