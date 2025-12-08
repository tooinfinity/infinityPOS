<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\StockMovement;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class StockMovementData extends Data
{
    public function __construct(
        public int $id,
        public int $quantity,
        public string $type,
        public ?string $reference,
        public ?string $batch_number,
        public ?string $notes,
        public Lazy|ProductData $product,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(StockMovement $movement): self
    {
        return new self(
            id: $movement->id,
            quantity: $movement->quantity,
            type: $movement->type,
            reference: $movement->reference,
            batch_number: $movement->batch_number,
            notes: $movement->notes,
            product: Lazy::whenLoaded('product', $movement, fn (): ProductData => ProductData::from($movement->product)
            ),
            store: Lazy::whenLoaded('store', $movement, fn (): StoreData => StoreData::from($movement->store)
            ),
            creator: Lazy::whenLoaded('creator', $movement, fn (): UserData => UserData::from($movement->creator)
            ),
            updater: Lazy::whenLoaded('updater', $movement, fn (): ?UserData => $movement->updater ? UserData::from($movement->updater) : null
            ),
            created_at: $movement->created_at,
            updated_at: $movement->updated_at,
        );
    }
}
