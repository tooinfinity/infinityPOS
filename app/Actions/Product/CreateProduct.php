<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\GenerateUniqueBarcode;
use App\Actions\GenerateUniqueSku;
use App\Data\Product\ProductData;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateProduct
{
    public function __construct(
        private GenerateUniqueSku $generateSku,
        private GenerateUniqueBarcode $generateBarcode,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(ProductData $data): Product
    {
        $sku = $data->sku ?? $this->generateSku->handle();
        $barcode = $data->barcode ?? $this->generateBarcode->handle();

        return DB::transaction(static fn (): Product => Product::query()->forceCreate([
            'name' => $data->name,
            'sku' => $sku,
            'barcode' => $barcode,
            'unit_id' => $data->unit_id,
            'category_id' => $data->category_id,
            'brand_id' => $data->brand_id,
            'description' => $data->description,
            'cost_price' => $data->cost_price,
            'selling_price' => $data->selling_price,
            'alert_quantity' => $data->alert_quantity,
            'track_inventory' => $data->track_inventory,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
