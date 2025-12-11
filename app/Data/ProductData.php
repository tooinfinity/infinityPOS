<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Product;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
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
        #[Lazy] public ?CategoryData $category,
        #[Lazy] public ?BrandData $brand,
        #[Lazy] public ?UnitData $unit,
        #[Lazy] public ?TaxData $tax,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
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
            category: $product->category ? CategoryData::from($product->category) : null,
            brand: $product->brand ? BrandData::from($product->brand) : null,
            unit: $product->unit ? UnitData::from($product->unit) : null,
            tax: $product->tax ? TaxData::from($product->tax) : null,
            creator: $product->creator ? UserData::from($product->creator) : null,
            updater: $product->updater ? UserData::from($product->updater) : null,
            created_at: $product->created_at?->toDayDateTimeString(),
            updated_at: $product->updated_at?->toDayDateTimeString(),
        );
    }
}
