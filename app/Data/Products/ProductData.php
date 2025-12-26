<?php

declare(strict_types=1);

namespace App\Data\Products;

use App\Data\Brands\BrandData;
use App\Data\Categories\CategoryData;
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
        public ?int $available_stock = null,
        public Lazy|CategoryData|null $category = null,
        public Lazy|BrandData|null $brand = null,
        public Lazy|UnitData|null $unit = null,
        public Lazy|UserData|null $creator = null,
        public Lazy|UserData|null $updater = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at = null,
    ) {}
}
