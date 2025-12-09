<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Sale;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class SaleData extends Data
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
        public Lazy|ClientData|null $client,
        public Lazy|StoreData $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, SaleItemData> */
        public Lazy|DataCollection $items,
        /** @var Lazy|DataCollection<int|string, SaleReturnData> */
        public Lazy|DataCollection $returns,
        public Lazy|InvoiceData|null $invoice,
        /** @var Lazy|DataCollection<int|string, PaymentData> */
        public Lazy|DataCollection $payments,
        /** @var Lazy|DataCollection<int|string, StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Sale $sale): self
    {
        return new self(
            id: $sale->id,
            reference: $sale->reference,
            subtotal: $sale->subtotal,
            discount: $sale->discount,
            tax: $sale->tax,
            total: $sale->total,
            paid: $sale->paid,
            status: $sale->status,
            notes: $sale->notes,
            client: Lazy::whenLoaded('client', $sale, fn (): ?ClientData => $sale->client ? ClientData::from($sale->client) : null
            ),
            store: Lazy::whenLoaded('store', $sale, fn (): StoreData => StoreData::from($sale->store)
            ),
            creator: Lazy::whenLoaded('creator', $sale, fn (): UserData => UserData::from($sale->creator)
            ),
            updater: Lazy::whenLoaded('updater', $sale, fn (): ?UserData => $sale->updater ? UserData::from($sale->updater) : null
            ),
            items: Lazy::whenLoaded('items', $sale,
                /**
                 * @return Collection<int|string, SaleItemData>
                 */
                fn (): Collection => SaleItemData::collect($sale->items)
            ),
            returns: Lazy::whenLoaded('returns', $sale,
                /**
                 * @return Collection<int|string, SaleReturnData>
                 */
                fn (): Collection => SaleReturnData::collect($sale->returns)
            ),
            invoice: Lazy::whenLoaded('invoice', $sale, fn (): ?InvoiceData => $sale->invoice ? InvoiceData::from($sale->invoice) : null),
            payments: Lazy::whenLoaded('payments', $sale,
                /**
                 * @return Collection<int|string, PaymentData>
                 */
                fn (): Collection => PaymentData::collect($sale->payments)
            ),
            stockMovements: Lazy::whenLoaded('stockMovements', $sale,
                /**
                 * @return Collection<int|string, StockMovementData>
                 */
                fn (): Collection => StockMovementData::collect($sale->stockMovements)
            ),
            created_at: $sale->created_at,
            updated_at: $sale->updated_at,
        );
    }
}
