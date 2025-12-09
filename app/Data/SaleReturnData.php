<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\SaleReturn;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class SaleReturnData extends Data
{
    public function __construct(
        public int $id,
        public string $reference,
        public int $subtotal,
        public ?int $discount,
        public ?int $tax,
        public int $total,
        public int $refunded,
        public string $status,
        public ?string $reason,
        public ?string $notes,
        public Lazy|SaleData|null $sale,
        public Lazy|ClientData|null $client,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, SaleReturnItemData> */
        public Lazy|DataCollection $items,
        /** @var Lazy|DataCollection<int|string, PaymentData> */
        public Lazy|DataCollection $payments,
        /** @var Lazy|DataCollection<int|string, StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(SaleReturn $return): self
    {
        return new self(
            id: $return->id,
            reference: $return->reference,
            subtotal: $return->subtotal,
            discount: $return->discount,
            tax: $return->tax,
            total: $return->total,
            refunded: $return->refunded,
            status: $return->status,
            reason: $return->reason,
            notes: $return->notes,
            sale: Lazy::whenLoaded('sale', $return, fn (): ?SaleData => $return->sale ? SaleData::from($return->sale) : null
            ),
            client: Lazy::whenLoaded('client', $return, fn (): ?ClientData => $return->client ? ClientData::from($return->client) : null
            ),
            store: Lazy::whenLoaded('store', $return, fn (): StoreData => StoreData::from($return->store)
            ),
            creator: Lazy::whenLoaded('creator', $return, fn (): UserData => UserData::from($return->creator)
            ),
            updater: Lazy::whenLoaded('updater', $return, fn (): ?UserData => $return->updater ? UserData::from($return->updater) : null
            ),
            items: Lazy::whenLoaded('items', $return,
                /**
                 * @return Collection<int|string, SaleReturnItemData>
                 */
                fn (): Collection => SaleReturnItemData::collect($return->items)
            ),
            payments: Lazy::whenLoaded('payments', $return,
                /**
                 * @return Collection<int|string, PaymentData>
                 */
                fn (): Collection => PaymentData::collect($return->payments)
            ),
            stockMovements: Lazy::whenLoaded('stockMovements', $return,
                /**
                 * @return Collection<int|string, StockMovementData>
                 */
                fn (): Collection => StockMovementData::collect($return->stockMovements)
            ),
            created_at: $return->created_at,
            updated_at: $return->updated_at,
        );
    }
}
