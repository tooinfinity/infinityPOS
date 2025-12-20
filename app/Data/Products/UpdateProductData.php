<?php

declare(strict_types=1);

namespace App\Data\Products;

use Spatie\LaravelData\Data;

final class UpdateProductData extends Data
{
    public function __construct(
        public ?string $sku,
        public ?string $barcode,
        public ?string $name,
        public ?string $description,
        public ?string $image,
        public ?int $category_id,
        public ?int $brand_id,
        public ?int $unit_id,
        public ?int $tax_id,
        public ?int $cost,
        public ?int $price,
        public ?int $alert_quantity,
        public ?bool $has_batches,
        public ?bool $is_active,
        public int $updated_by,
    ) {}
}
