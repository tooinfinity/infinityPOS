<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Store;
use Carbon\CarbonInterface;
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
        /** @var Lazy|DataCollection<ProductData> */
        public Lazy|DataCollection $products,
        /** @var Lazy|DataCollection<SaleData> */
        public Lazy|DataCollection $sales,
        /** @var Lazy|DataCollection<PurchaseData> */
        public Lazy|DataCollection $purchases,
        /** @var Lazy|DataCollection<SaleReturnData> */
        public Lazy|DataCollection $saleReturns,
        /** @var Lazy|DataCollection<PurchaseReturnData> */
        public Lazy|DataCollection $purchaseReturns,
        /** @var Lazy|DataCollection<MoneyboxData> */
        public Lazy|DataCollection $moneyboxes,
        /** @var Lazy|DataCollection<ExpenseData> */
        public Lazy|DataCollection $expenses,
        /** @var Lazy|DataCollection<StockMovementData> */
        public Lazy|DataCollection $stockMovements,
        /** @var Lazy|DataCollection<StockTransferData> */
        public Lazy|DataCollection $outgoingTransfers,
        /** @var Lazy|DataCollection<StockTransferData> */
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
            products: Lazy::whenLoaded('products', $store, fn (): DataCollection => ProductData::collect($store->products)
            ),
            sales: Lazy::whenLoaded('sales', $store, fn (): DataCollection => SaleData::collect($store->sales)
            ),
            purchases: Lazy::whenLoaded('purchases', $store, fn (): DataCollection => PurchaseData::collect($store->purchases)
            ),
            saleReturns: Lazy::whenLoaded('saleReturns', $store, fn (): DataCollection => SaleReturnData::collect($store->saleReturns)
            ),
            purchaseReturns: Lazy::whenLoaded('purchaseReturns', $store, fn (): DataCollection => PurchaseReturnData::collect($store->purchaseReturns)
            ),
            moneyboxes: Lazy::whenLoaded('moneyboxes', $store, fn (): DataCollection => MoneyboxData::collect($store->moneyboxes)
            ),
            expenses: Lazy::whenLoaded('expenses', $store, fn (): DataCollection => ExpenseData::collect($store->expenses)
            ),
            stockMovements: Lazy::whenLoaded('stockMovements', $store, fn (): DataCollection => StockMovementData::collect($store->stockMovements)
            ),
            outgoingTransfers: Lazy::whenLoaded('outgoingTransfers', $store, fn (): DataCollection => StockTransferData::collect($store->outgoingTransfers)
            ),
            incomingTransfers: Lazy::whenLoaded('incomingTransfers', $store, fn (): DataCollection => StockTransferData::collect($store->incomingTransfers)
            ),
            created_at: $store->created_at,
            updated_at: $store->updated_at,
        );
    }
}
