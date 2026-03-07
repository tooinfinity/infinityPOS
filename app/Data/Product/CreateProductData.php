<?php

declare(strict_types=1);

namespace App\Data\Product;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

final class CreateProductData extends Data
{
    public function __construct(
        public string $name,
        public ?string $sku,
        public ?string $barcode,
        public int $unit_id,
        public ?int $category_id,
        public ?int $brand_id,
        public ?string $description,
        public int $cost_price,
        public int $selling_price,
        public int $alert_quantity,
        public bool $track_inventory,
        public bool $is_active,
    ) {}
}
