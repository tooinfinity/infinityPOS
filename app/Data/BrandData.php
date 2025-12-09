<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Brand;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class BrandData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $is_active,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /**
         * @var Lazy|DataCollection<int|string, ProductData>
         */
        public Lazy|DataCollection $products,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Brand $brand): self
    {
        return new self(
            id: $brand->id,
            name: $brand->name,
            is_active: $brand->is_active,
            creator: Lazy::whenLoaded('creator', $brand,
                fn (): UserData => UserData::from($brand->creator)
            ),
            updater: Lazy::whenLoaded('updater', $brand,
                fn (): ?UserData => $brand->updater ? UserData::from($brand->updater) : null),
            products: Lazy::whenLoaded('products', $brand,
                /**
                 * @return Collection<int|string, ProductData>
                 */
                fn (): Collection => ProductData::collect($brand->products)
            ),
            created_at: $brand->created_at,
            updated_at: $brand->updated_at,
        );
    }
}
