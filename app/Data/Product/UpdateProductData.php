<?php

declare(strict_types=1);

namespace App\Data\Product;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateProductData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|Optional|null $sku,
        public string|Optional|null $barcode,
        public int|Optional $unit_id,
        public int|Optional|null $category_id,
        public int|Optional|null $brand_id,
        public string|Optional|null $description,
        public int|Optional $cost_price,
        public int|Optional $selling_price,
        public int|Optional $alert_quantity,
        public bool|Optional $track_inventory,
        public bool|Optional $is_active,
    ) {}
}
