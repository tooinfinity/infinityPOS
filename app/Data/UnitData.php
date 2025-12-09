<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Unit;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class UnitData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $short_name,
        public bool $is_active,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, ProductData> */
        public Lazy|DataCollection $products,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Unit $unit): self
    {
        return new self(
            id: $unit->id,
            name: $unit->name,
            short_name: $unit->short_name,
            is_active: $unit->is_active,
            creator: Lazy::whenLoaded('creator', $unit, fn (): UserData => UserData::from($unit->creator)
            ),
            updater: Lazy::whenLoaded('updater', $unit, fn (): ?UserData => $unit->updater ? UserData::from($unit->updater) : null),
            products: Lazy::whenLoaded('products', $unit,
                /**
                 * @return Collection<int|string, ProductData>
                 */
                fn (): Collection => ProductData::collect($unit->products)
            ),
            created_at: $unit->created_at,
            updated_at: $unit->updated_at,
        );
    }
}
