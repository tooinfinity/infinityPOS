<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\PurchaseReturn;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class PurchaseReturnData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $total,
        public int $refunded,
        public string $status,
        public ?string $reason,
        public ?string $notes,
        public Lazy|PurchaseData|null $purchase,
        public Lazy|SupplierData|null $supplier,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, PurchaseReturnItemData> */
        public Lazy|DataCollection $items,
        /** @var Lazy|DataCollection<int|string, PaymentData> */
        public Lazy|DataCollection $payments,
        /** @var Lazy|DataCollection<int|string, StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(PurchaseReturn $return): self
    {
        return new self(
            id: $return->id,
            reference: $return->reference,
            total: $return->total,
            refunded: $return->refunded,
            status: $return->status,
            reason: $return->reason,
            notes: $return->notes,
            purchase: Lazy::whenLoaded('purchase', $return, fn (): ?PurchaseData => $return->purchase ? PurchaseData::from($return->purchase) : null
            ),
            supplier: Lazy::whenLoaded('supplier', $return, fn (): ?SupplierData => $return->supplier ? SupplierData::from($return->supplier) : null
            ),
            store: Lazy::whenLoaded('store', $return, fn (): StoreData => StoreData::from($return->store)
            ),
            creator: Lazy::whenLoaded('creator', $return, fn (): UserData => UserData::from($return->creator)
            ),
            updater: Lazy::whenLoaded('updater', $return, fn (): ?UserData => $return->updater ? UserData::from($return->updater) : null
            ),
            items: Lazy::whenLoaded('items', $return,
                /**
                 * @return Collection<int|string, PurchaseReturnItemData>
                 */
                fn (): Collection => PurchaseReturnItemData::collect($return->items)),
            payments: Lazy::whenLoaded('payments', $return,
                /**
                 * @return Collection<int|string, PaymentData>
                 */
                fn (): Collection => PaymentData::collect($return->payments)),
            stockMovements: Lazy::whenLoaded('stockMovements', $return,
                /**
                 * @return Collection<int|string, StockMovementData>
                 */
                fn (): Collection => StockMovementData::collect($return->stockMovements)),
            created_at: $return->created_at,
            updated_at: $return->updated_at,
        );
    }
}
