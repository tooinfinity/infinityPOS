<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ProductUnitEnum;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class ProductData extends Data
{
    public function __construct(
        #[Nullable]
        #[MapInputName('category_id')]
        public ?int $categoryId = null,

        #[Required, Max(255)]
        public string $name = '',

        #[Required, Max(100)]
        public string $sku = '',

        #[Max(100)]
        #[MapInputName('barcode')]
        public ?string $barcode = null,

        #[MapInputName('description')]
        public ?string $description = null,

        #[Required]
        public ProductUnitEnum $unit = ProductUnitEnum::PIECE,

        #[Required, Min(0)]
        #[MapInputName('selling_price')]
        public int $sellingPrice = 0,

        #[Required, Min(0)]
        #[MapInputName('alert_quantity')]
        public int $alertQuantity = 0,

        #[Max(255)]
        #[MapInputName('image')]
        public ?string $image = null,

        #[BooleanType]
        #[MapInputName('is_active')]
        public bool $isActive = true,
    ) {}
}
