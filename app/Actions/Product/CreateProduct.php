<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\GenerateUniqueBarcode;
use App\Actions\GenerateUniqueSku;
use App\Actions\UploadImage;
use App\Data\Product\CreateProductData;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateProduct
{
    public function __construct(
        private GenerateUniqueSku $generateSku,
        private GenerateUniqueBarcode $generateBarcode,
        private UploadImage $uploadImage,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateProductData $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $sku = $data->sku ?? $this->generateSku->handle();
            $barcode = $data->barcode ?? $this->generateBarcode->handle();
            $trackInventory = $data->track_inventory ?? true;
            $isActive = $data->is_active ?? true;

            $image = $data->image;
            if ($image instanceof UploadedFile) {
                $image = $this->uploadImage->handle($image, 'products');
            }

            return Product::query()->forceCreate([
                'name' => $data->name,
                'sku' => $sku,
                'barcode' => $barcode,
                'unit_id' => $data->unit_id,
                'category_id' => $data->category_id,
                'brand_id' => $data->brand_id,
                'description' => $data->description,
                'image' => $image,
                'cost_price' => $data->cost_price,
                'selling_price' => $data->selling_price,
                'alert_quantity' => $data->alert_quantity,
                'track_inventory' => $trackInventory,
                'is_active' => $isActive,
            ])->refresh();
        });
    }
}
