<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Purchase;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class PurchaseData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public int $paid,
        public string $status,
        public ?string $notes,
        public Lazy|SupplierData|null $supplier,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<PurchaseItemData> */
        public Lazy|DataCollection $items,
        /** @var Lazy|DataCollection<PurchaseReturnData> */
        public Lazy|DataCollection $returns,
        /** @var Lazy|DataCollection<PaymentData> */
        public Lazy|DataCollection $payments,
        /** @var Lazy|DataCollection<StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Purchase $purchase): self
    {
        return new self(
            id: $purchase->id,
            reference: $purchase->reference,
            subtotal: $purchase->subtotal,
            discount: $purchase->discount,
            tax: $purchase->tax,
            total: $purchase->total,
            paid: $purchase->paid,
            status: $purchase->status,
            notes: $purchase->notes,
            supplier: Lazy::whenLoaded('supplier', $purchase, fn (): ?SupplierData => $purchase->supplier ? SupplierData::from($purchase->supplier) : null
            ),
            store: Lazy::whenLoaded('store', $purchase, fn (): StoreData => StoreData::from($purchase->store)
            ),
            creator: Lazy::whenLoaded('creator', $purchase, fn (): UserData => UserData::from($purchase->creator)
            ),
            updater: Lazy::whenLoaded('updater', $purchase, fn (): ?UserData => $purchase->updater ? UserData::from($purchase->updater) : null
            ),
            items: Lazy::whenLoaded('items', $purchase, fn (): DataCollection => PurchaseItemData::collect($purchase->items)
            ),
            returns: Lazy::whenLoaded('returns', $purchase, fn (): DataCollection => PurchaseReturnData::collect($purchase->returns)
            ),
            payments: Lazy::whenLoaded('payments', $purchase, fn (): DataCollection => PaymentData::collect($purchase->payments)
            ),
            stockMovements: Lazy::whenLoaded('stockMovements', $purchase, fn (): DataCollection => StockMovementData::collect($purchase->stockMovements)
            ),
            created_at: $purchase->created_at,
            updated_at: $purchase->updated_at,
        );
    }
}
