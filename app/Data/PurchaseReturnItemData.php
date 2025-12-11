<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\PurchaseReturnItem;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
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
        #[Lazy] public ?PurchaseReturnData $purchaseReturn,
        #[Lazy] public ?ProductData $product,
        #[Lazy] public ?PurchaseItemData $purchaseItem,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(PurchaseReturnItem $item): self
    {
        return new self(
            id: $item->id,
            quantity: $item->quantity,
            cost: $item->cost,
            total: $item->total,
            batch_number: $item->batch_number,
            purchaseReturn: $item->purchaseReturn ? PurchaseReturnData::from($item->purchaseReturn) : null,
            product: $item->product ? ProductData::from($item->product) : null,
            purchaseItem: $item->purchaseItem ? PurchaseItemData::from($item->purchaseItem) : null,
            created_at: $item->created_at?->toDayDateTimeString(),
            updated_at: $item->updated_at?->toDayDateTimeString(),
        );
    }
}
