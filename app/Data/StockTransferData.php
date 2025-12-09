<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\StockTransfer;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class StockTransferData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $status,
        public ?string $notes,
        public Lazy|StoreData $fromStore,
        public Lazy|StoreData $toStore,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, StockTransferItemData> */
        public Lazy|DataCollection $items,
        /** @var Lazy|DataCollection<int|string, StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(StockTransfer $transfer): self
    {
        return new self(
            id: $transfer->id,
            reference: $transfer->reference,
            status: $transfer->status,
            notes: $transfer->notes,
            fromStore: Lazy::whenLoaded('fromStore', $transfer, fn (): StoreData => StoreData::from($transfer->fromStore)
            ),
            toStore: Lazy::whenLoaded('toStore', $transfer, fn (): StoreData => StoreData::from($transfer->toStore)
            ),
            creator: Lazy::whenLoaded('creator', $transfer, fn (): UserData => UserData::from($transfer->creator)
            ),
            updater: Lazy::whenLoaded('updater', $transfer, fn (): ?UserData => $transfer->updater ? UserData::from($transfer->updater) : null
            ),
            items: Lazy::whenLoaded('items', $transfer,
                /**
                 * @return Collection<int|string, StockTransferItemData>
                 */
                fn (): Collection => StockTransferItemData::collect($transfer->items)
            ),
            stockMovements: Lazy::whenLoaded('stockMovements', $transfer,
                /**
                 * @return Collection<int|string, StockMovementData>
                 */
                fn (): Collection => StockMovementData::collect($transfer->stockMovements)
            ),
            created_at: $transfer->created_at,
            updated_at: $transfer->updated_at,
        );
    }
}
