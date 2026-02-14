<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\GenerateUniqueBarcodeAction;
use App\Actions\GenerateUniqueSkuAction;
use App\Actions\UploadImageAction;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateProductAction
{
    public function __construct(
        private GenerateUniqueSkuAction $generateSku,
        private GenerateUniqueBarcodeAction $generateBarcode,
        private UploadImageAction $uploadImage,
    ) {}

    /**
     * @param  array{name: string, sku?: string, barcode?: string, unit_id: int, category_id?: int, brand_id?: int, description?: string, image?: UploadedFile|string, cost_price: int, selling_price: int, quantity: int, alert_quantity: int, track_inventory?: bool, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $data['sku'] ??= $this->generateSku->handle();
            $data['barcode'] ??= $this->generateBarcode->handle();

            $data['track_inventory'] ??= true;
            $data['is_active'] ??= true;

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = $this->uploadImage->handle($data['image'], 'products');
            }

            return Product::query()->create($data)->refresh();
        });
    }
}
