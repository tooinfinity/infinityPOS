<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
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
        public Lazy|CategoryData|null $category,
        public Lazy|BrandData|null $brand,
        public Lazy|UnitData|null $unit,
        public Lazy|TaxData|null $tax,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
