<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Supplier;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class SupplierData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public int $balance,
        public bool $is_active,
        public Lazy|BusinessIdentifierData|null $businessIdentifier,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, PurchaseData> */
        public Lazy|DataCollection $purchases,
        /** @var Lazy|DataCollection<int|string, PurchaseReturnData> */
        public Lazy|DataCollection $purchaseReturns,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Supplier $supplier): self
    {
        return new self(
            id: $supplier->id,
            name: $supplier->name,
            phone: $supplier->phone,
            email: $supplier->email,
            address: $supplier->address,
            balance: $supplier->balance,
            is_active: $supplier->is_active,
            businessIdentifier: Lazy::whenLoaded('businessIdentifier', $supplier, fn (): ?BusinessIdentifierData => $supplier->businessIdentifier ? BusinessIdentifierData::from($supplier->businessIdentifier) : null),
            creator: Lazy::whenLoaded('creator', $supplier, fn (): UserData => UserData::from($supplier->creator)
            ),
            updater: Lazy::whenLoaded('updater', $supplier, fn (): ?UserData => $supplier->updater ? UserData::from($supplier->updater) : null),
            purchases: Lazy::whenLoaded('purchases', $supplier,
                /**
                 * @return Collection<int|string, PurchaseData>
                 */
                fn (): Collection => PurchaseData::collect($supplier->purchases)
            ),
            purchaseReturns: Lazy::whenLoaded('purchaseReturns', $supplier,
                /**
                 * @return Collection<int|string, PurchaseReturnData>
                 */
                fn (): Collection => PurchaseReturnData::collect($supplier->purchaseReturns)
            ),
            created_at: $supplier->created_at,
            updated_at: $supplier->updated_at,
        );
    }
}
