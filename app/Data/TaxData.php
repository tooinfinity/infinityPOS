<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Tax;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class TaxData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $tax_type,
        public int $rate,
        public bool $is_active,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<ProductData> */
        public Lazy|DataCollection $products,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Tax $tax): self
    {
        return new self(
            id: $tax->id,
            name: $tax->name,
            tax_type: $tax->tax_type,
            rate: $tax->rate,
            is_active: $tax->is_active,
            creator: Lazy::whenLoaded('creator', $tax, fn (): UserData => UserData::from($tax->creator)
            ),
            updater: Lazy::whenLoaded('updater', $tax, fn (): ?UserData => $tax->updater ? UserData::from($tax->updater) : null),
            products: Lazy::whenLoaded('products', $tax, fn (): DataCollection => ProductData::collect($tax->products)
            ),
            created_at: $tax->created_at,
            updated_at: $tax->updated_at,
        );
    }
}
