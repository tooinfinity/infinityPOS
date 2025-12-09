<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class ProductData extends Data
{
    public function __construct(
        public int $id,
        public ?string $sku,
        public ?string $barcode,
        public string $name,
        public ?string $description,
        public ?string $image,
        public int $cost,
        public int $price,
        public int $alert_quantity,
        public bool $has_batches,
        public bool $is_active,
        public Lazy|CategoryData|null $category,
        public Lazy|BrandData|null $brand,
        public Lazy|UnitData|null $unit,
        public Lazy|TaxData|null $tax,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, SaleItemData> */
        public Lazy|DataCollection $saleItems,
        /** @var Lazy|DataCollection<int|string, PurchaseItemData> */
        public Lazy|DataCollection $purchaseItems,
        /** @var Lazy|DataCollection<int|string, StoreData> */
        public Lazy|DataCollection $stores,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            sku: $product->sku,
            barcode: $product->barcode,
            name: $product->name,
            description: $product->description,
            image: $product->image,
            cost: $product->cost,
            price: $product->price,
            alert_quantity: $product->alert_quantity,
            has_batches: $product->has_batches,
            is_active: $product->is_active,
            category: Lazy::whenLoaded('category', $product, fn (): ?CategoryData => $product->category ? CategoryData::from($product->category) : null
            ),
            brand: Lazy::whenLoaded('brand', $product, fn (): ?BrandData => $product->brand ? BrandData::from($product->brand) : null
            ),
            unit: Lazy::whenLoaded('unit', $product, fn (): ?UnitData => $product->unit ? UnitData::from($product->unit) : null
            ),
            tax: Lazy::whenLoaded('tax', $product, fn (): ?TaxData => $product->tax ? TaxData::from($product->tax) : null
            ),
            creator: Lazy::whenLoaded('creator', $product, fn (): UserData => UserData::from($product->creator)
            ),
            updater: Lazy::whenLoaded('updater', $product, fn (): ?UserData => $product->updater ? UserData::from($product->updater) : null
            ),
            saleItems: Lazy::whenLoaded('saleItems', $product,
                /**
                 * @return Collection<int|string, SaleItemData>
                 */
                fn (): Collection => SaleItemData::collect($product->saleItems)),
            purchaseItems: Lazy::whenLoaded('purchaseItems', $product,
                /**
                 * @return Collection<int|string, PurchaseItemData>
                 */
                fn (): Collection => PurchaseItemData::collect($product->purchaseItems)),
            stores: Lazy::whenLoaded('stores', $product,
                /**
                 * @return Collection<int|string, StoreData>
                 */
                fn (): Collection => StoreData::collect($product->stores)),
            created_at: $product->created_at,
            updated_at: $product->updated_at,
        );
    }
}
