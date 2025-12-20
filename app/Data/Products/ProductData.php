<?php

declare(strict_types=1);

namespace App\Data\Products;

use App\Data\Brands\BrandData;
use App\Data\Categories\CategoryData;
use App\Data\Taxes\TaxData;
use App\Data\Units\UnitData;
use App\Data\Users\UserData;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
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
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
