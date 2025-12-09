<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Store;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class StoreData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $city,
        public ?string $address,
        public ?string $phone,
        public bool $is_active,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, ProductData> */
        public Lazy|DataCollection $products,
        /** @var Lazy|DataCollection<int|string, SaleData> */
        public Lazy|DataCollection $sales,
        /** @var Lazy|DataCollection<int|string, PurchaseData> */
        public Lazy|DataCollection $purchases,
        /** @var Lazy|DataCollection<int|string, SaleReturnData> */
        public Lazy|DataCollection $saleReturns,
        /** @var Lazy|DataCollection<int|string, PurchaseReturnData> */
        public Lazy|DataCollection $purchaseReturns,
        /** @var Lazy|DataCollection<int|string, MoneyboxData> */
        public Lazy|DataCollection $moneyboxes,
        /** @var Lazy|DataCollection<int|string, ExpenseData> */
        public Lazy|DataCollection $expenses,
        /** @var Lazy|DataCollection<int|string, StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        /** @var Lazy|DataCollection<int|string, StockTransferData> */
        public Lazy|DataCollection $outgoingTransfers,
        /** @var Lazy|DataCollection<int|string, StockTransferData> */
        public Lazy|DataCollection $incomingTransfers,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Store $store): self
    {
        return new self(
            id: $store->id,
            name: $store->name,
            city: $store->city,
            address: $store->address,
            phone: $store->phone,
            is_active: $store->is_active,
            creator: Lazy::whenLoaded('creator', $store, fn (): UserData => UserData::from($store->creator)
            ),
            updater: Lazy::whenLoaded('updater', $store, fn (): ?UserData => $store->updater ? UserData::from($store->updater) : null),
            products: Lazy::whenLoaded('products', $store,
                /**
                 * @return Collection<int|string, ProductData>
                 */
                fn (): Collection => ProductData::collect($store->products)
            ),
            sales: Lazy::whenLoaded('sales', $store,
                /**
                 * @return Collection<int|string, SaleData>
                 */
                fn (): Collection => SaleData::collect($store->sales)
            ),
            purchases: Lazy::whenLoaded('purchases', $store,
                /**
                 * @return Collection<int|string, PurchaseData>
                 */
                fn (): Collection => PurchaseData::collect($store->purchases)
            ),
            saleReturns: Lazy::whenLoaded('saleReturns', $store,
                /**
                 * @return Collection<int|string, SaleReturnData>
                 */
                fn (): Collection => SaleReturnData::collect($store->saleReturns)
            ),
            purchaseReturns: Lazy::whenLoaded('purchaseReturns', $store,
                /**
                 * @return Collection<int|string, PurchaseReturnData>
                 */
                fn (): Collection => PurchaseReturnData::collect($store->purchaseReturns)
            ),
            moneyboxes: Lazy::whenLoaded('moneyboxes', $store,
                /**
                 * @return Collection<int|string, MoneyboxData>
                 */
                fn (): Collection => MoneyboxData::collect($store->moneyboxes)
            ),
            expenses: Lazy::whenLoaded('expenses', $store,
                /**
                 * @return Collection<int|string, ExpenseData>
                 */
                fn (): Collection => ExpenseData::collect($store->expenses)
            ),
            stockMovements: Lazy::whenLoaded('stockMovements', $store,
                /**
                 * @return Collection<int|string, StockMovementData>
                 */
                fn (): Collection => StockMovementData::collect($store->stockMovements)
            ),
            outgoingTransfers: Lazy::whenLoaded('outgoingTransfers', $store,
                /**
                 * @return Collection<int|string, StockTransferData>
                 */
                fn (): Collection => StockTransferData::collect($store->outgoingTransfers)
            ),
            incomingTransfers: Lazy::whenLoaded('incomingTransfers', $store,
                /**
                 * @return Collection<int|string, StockTransferData>
                 */
                fn (): Collection => StockTransferData::collect($store->incomingTransfers)
            ),
            created_at: $store->created_at,
            updated_at: $store->updated_at,
        );
    }
}
